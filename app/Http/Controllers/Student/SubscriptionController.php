<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\NotchPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(protected NotchPayService $notchPay) {}

    public function index()
    {
        $user                = auth()->user();
        $currentSubscription = $user->activeSubscription;
        $plans               = SubscriptionPlan::query()->orderBy('id')->get();

        return view('student.subscription.index', compact('currentSubscription', 'plans'));
    }

    public function checkout(SubscriptionPlan $plan)
    {
        $user = auth()->user();

        $activeSubscription = $user->activeSubscription;
        if ($activeSubscription && !$activeSubscription->is_trial) {
            return redirect()->route('student.subscription.index')
                ->with('info', 'Vous avez déjà un abonnement actif.');
        }

        return view('student.subscription.checkout', compact('plan', 'user'));
    }

    public function processPayment(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'phone'   => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
            'channel' => ['required', 'in:mtn_momo,orange_money'],
        ], [
            'phone.required'   => 'Le numéro de téléphone est obligatoire.',
            'phone.regex'      => 'Le numéro de téléphone est invalide.',
            'channel.required' => 'Veuillez choisir MTN MoMo ou Orange Money.',
            'channel.in'       => 'Le moyen de paiement choisi est invalide.',
        ]);

        $user               = auth()->user();
        $merchantReference  = $this->notchPay->generateReference($user->id, $plan->id);

        $channelMap = [
            'mtn_momo'     => 'cm.mobile',
            'orange_money' => 'cm.mobile',
        ];

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'user_id'              => $user->id,
                'subscription_plan_id' => $plan->id,
                'notchpay_reference'   => $merchantReference,
                'amount'               => $plan->price,
                'currency'             => $plan->currency ?? 'XAF',
                'status'               => defined(Payment::class . '::STATUS_PENDING') ? Payment::STATUS_PENDING : 'pending',
                'phone_number'         => $request->phone,
                'ip_address'           => $request->ip(),
                'user_agent'           => $request->userAgent(),
            ]);

            $subscription = Subscription::create([
                'user_id'              => $user->id,
                'subscription_plan_id' => $plan->id,
                'plan_name'            => $plan->name,
                'status'               => defined(Subscription::class . '::STATUS_PENDING') ? Subscription::STATUS_PENDING : 'pending',
                'is_trial'             => false,
                'payment_id'           => $payment->id,
            ]);

            $response = $this->notchPay->initiatePayment([
                'amount'      => (int) $plan->price,
                'currency'    => $plan->currency ?? 'XAF',
                'email'       => $user->email,
                'phone'       => $request->phone,
                'reference'   => $merchantReference,
                'description' => "Abonnement {$plan->name} - TIMAH SCHOOL",
                'name'        => $user->name ?? $user->full_name ?? $user->username ?? 'Client',
                'channel'     => $channelMap[$request->channel] ?? 'cm.mobile',
                'country'     => 'CM',
            ]);

            $payment->update([
                'notchpay_response' => $response['data'] ?? $response,
            ]);

            if (!($response['success'] ?? false)) {
                $errorMessage = $response['message'] ?? 'Erreur lors de l\'initialisation du paiement.';
                $statusCode   = $response['status_code'] ?? null;

                $payment->update([
                    'status'         => defined(Payment::class . '::STATUS_FAILED') ? Payment::STATUS_FAILED : 'failed',
                    'failure_reason' => $statusCode ? "[HTTP {$statusCode}] {$errorMessage}" : $errorMessage,
                ]);

                $subscription->update([
                    'status' => defined(Subscription::class . '::STATUS_FAILED') ? Subscription::STATUS_FAILED : 'failed',
                ]);

                DB::commit();

                return back()
                    ->withInput()
                    ->with('error', $statusCode ? "{$errorMessage} (HTTP {$statusCode})" : $errorMessage);
            }

            $paymentUrl = $response['authorization_url']
                ?? data_get($response, 'data.authorization_url')
                ?? data_get($response, 'data.transaction.authorization_url')
                ?? data_get($response, 'data.transaction.payment_url')
                ?? data_get($response, 'data.payment_url');

            if (!$paymentUrl) {
                $payment->update([
                    'status'         => defined(Payment::class . '::STATUS_FAILED') ? Payment::STATUS_FAILED : 'failed',
                    'failure_reason' => 'authorization_url introuvable dans la réponse NotchPay.',
                ]);

                $subscription->update([
                    'status' => defined(Subscription::class . '::STATUS_FAILED') ? Subscription::STATUS_FAILED : 'failed',
                ]);

                Log::error('NotchPay payment url missing', [
                    'reference' => $merchantReference,
                    'response'  => $response,
                ]);

                DB::commit();

                return back()
                    ->withInput()
                    ->with('error', 'URL de paiement introuvable dans la réponse NotchPay.');
            }

            DB::commit();

            return redirect()->away($paymentUrl);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erreur processPayment', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $providerReference = $request->get('reference');
        $merchantReference = $request->get('trxref') ?? $request->get('notchpay_trxref');

        if (!$providerReference && !$merchantReference) {
            return redirect()->to($this->subscriptionRedirectUrl())
                ->with('warning', 'Référence de paiement introuvable.');
        }

        if (!$providerReference) {
            Log::error('NotchPay callback missing provider reference', [
                'query' => $request->query(),
            ]);

            return redirect()->to($this->subscriptionRedirectUrl())
                ->with('error', 'Référence NotchPay introuvable dans le callback.');
        }

        $verification = $this->notchPay->verifyPayment($providerReference);

        if (!($verification['success'] ?? false)) {
            $message = $verification['message'] ?? 'Impossible de vérifier votre paiement.';

            return redirect()->to($this->subscriptionRedirectUrl())
                ->with('error', $message);
        }

        $payment = null;

        if ($merchantReference) {
            $payment = Payment::where('notchpay_reference', $merchantReference)->first();
        }

        if (!$payment) {
            $payment = Payment::where('notchpay_reference', $providerReference)->first();
        }

        if (!$payment) {
            Log::error('NotchPay callback payment not found locally', [
                'provider_reference' => $providerReference,
                'merchant_reference' => $merchantReference,
            ]);

            return redirect()->to($this->subscriptionRedirectUrl())
                ->with('error', 'Paiement introuvable dans notre système.');
        }

        $verificationData = $verification['data'] ?? [];
        $status = data_get($verificationData, 'transaction.status')
            ?? data_get($verificationData, 'status');

        if ($status === 'complete') {
            $this->activateSubscription($payment, $verificationData);

            return redirect()->to($this->postPaymentRedirectUrl((int) $payment->user_id, true))
                ->with('success', 'Paiement réussi. Votre abonnement est maintenant actif.');
        }

        if (in_array($status, ['failed', 'canceled', 'cancelled', 'rejected', 'abandoned', 'expired'], true)) {
            $payment->update([
                'status'            => defined(Payment::class . '::STATUS_FAILED') ? Payment::STATUS_FAILED : 'failed',
                'failure_reason'    => data_get($verificationData, 'message', 'Paiement échoué'),
                'notchpay_response' => $verificationData,
            ]);

            $subscription = Subscription::where('payment_id', $payment->id)->first();
            if ($subscription) {
                $subscription->update([
                    'status' => defined(Subscription::class . '::STATUS_FAILED') ? Subscription::STATUS_FAILED : 'failed',
                ]);
            }

            return redirect()->to($this->postPaymentRedirectUrl((int) $payment->user_id))
                ->with('error', 'Le paiement a échoué. Veuillez réessayer.');
        }

        $payment->update([
            'notchpay_response' => $verificationData,
        ]);

        return redirect()->to($this->postPaymentRedirectUrl((int) $payment->user_id))
            ->with('info', 'Votre paiement est en cours de traitement. Vous recevrez une confirmation sous peu.');
    }

    public function expired()
    {
        return view('student.subscription.expired');
    }

    public function required()
    {
        return view('student.subscription.required');
    }

    public function pending()
    {
        return view('student.subscription.pending');
    }

    protected function activateSubscription(Payment $payment, array $notchpayData): void
    {
        DB::transaction(function () use ($payment, $notchpayData) {
            if (method_exists($payment, 'markAsCompleted')) {
                $payment->markAsCompleted($notchpayData);
            } else {
                $payment->update([
                    'status'            => defined(Payment::class . '::STATUS_COMPLETED') ? Payment::STATUS_COMPLETED : 'completed',
                    'notchpay_response' => $notchpayData,
                ]);
            }

            $plan = $payment->plan;
            $startsAt = now();

            $endsAt = match ($plan->duration_unit) {
                'day'   => $startsAt->copy()->addDays($plan->duration_value),
                'week'  => $startsAt->copy()->addWeeks($plan->duration_value),
                'month' => $startsAt->copy()->addMonths($plan->duration_value),
                'year'  => $startsAt->copy()->addYears($plan->duration_value),
                default => $startsAt->copy()->addMonth(),
            };

            Subscription::where('payment_id', $payment->id)->update([
                'status'    => defined(Subscription::class . '::STATUS_ACTIVE') ? Subscription::STATUS_ACTIVE : 'active',
                'starts_at' => $startsAt,
                'ends_at'   => $endsAt,
                'is_trial'  => false,
            ]);
        });
    }

    protected function subscriptionRedirectUrl(): string
    {
        if (auth()->check()) {
            return route('student.subscription.index');
        }

        return route('login');
    }

    protected function postPaymentRedirectUrl(int $paymentUserId, bool $success = false): string
    {
        if (auth()->check() && (int) auth()->id() === $paymentUserId) {
            return $success ? route('student.dashboard') : route('student.subscription.index');
        }

        return route('login');
    }
}
