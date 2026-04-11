<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\NotchPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotchPayWebhookController extends Controller
{
    public function __construct(protected NotchPayService $notchPay) {}

    /**
     * Recevoir et traiter les webhooks NotchPay
     */
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('X-Notch-Signature', '');

        // 1. Valider la signature du webhook
        if (!$this->notchPay->validateWebhookSignature($payload, $signature)) {
            Log::warning('NotchPay webhook: signature invalide', [
                'signature' => $signature,
                'ip'        => $request->ip(),
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data = $request->json()->all();
        Log::info('NotchPay webhook reçu', $data);

        $event     = $data['event'] ?? null;
        $reference = $data['data']['reference'] ?? null;

        if (!$reference) {
            return response()->json(['message' => 'No reference'], 400);
        }

        $payment = Payment::where('notchpay_reference', $reference)->first();

        if (!$payment) {
            Log::error("NotchPay webhook: paiement introuvable pour référence {$reference}");
            return response()->json(['message' => 'Payment not found'], 404);
        }

        match ($event) {
            'payment.complete' => $this->handlePaymentComplete($payment, $data),
            'payment.failed'   => $this->handlePaymentFailed($payment, $data),
            'payment.cancelled'=> $this->handlePaymentCancelled($payment, $data),
            default => Log::info("NotchPay webhook: événement ignoré ({$event})"),
        };

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Paiement complété
     */
    protected function handlePaymentComplete(Payment $payment, array $data): void
    {
        if ($payment->isCompleted()) {
            return; // Idempotent - déjà traité
        }

        DB::transaction(function () use ($payment, $data) {
            $payment->markAsCompleted($data['data'] ?? []);
            $payment->update([
                'notchpay_transaction_id' => $data['data']['id'] ?? null,
                'payment_method'          => $data['data']['channel'] ?? null,
            ]);

            $plan     = $payment->plan;
            $startsAt = now();
            $endsAt   = match ($plan->duration_unit) {
                'day'   => $startsAt->copy()->addDays($plan->duration_value),
                'week'  => $startsAt->copy()->addWeeks($plan->duration_value),
                'month' => $startsAt->copy()->addMonths($plan->duration_value),
                'year'  => $startsAt->copy()->addYears($plan->duration_value),
                default => $startsAt->copy()->addMonth(),
            };

            // Annuler anciens abonnements actifs
            Subscription::where('user_id', $payment->user_id)
                ->whereIn('status', [
                    Subscription::STATUS_ACTIVE,
                    Subscription::STATUS_TRIAL,
                    Subscription::STATUS_PENDING,
                ])
                ->update(['status' => Subscription::STATUS_CANCELLED, 'cancelled_at' => now()]);

            // Activer l'abonnement
            Subscription::where('payment_id', $payment->id)->update([
                'status'    => Subscription::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'ends_at'   => $endsAt,
                'is_trial'  => false,
            ]);
        });

        Log::info("Abonnement activé pour user_id={$payment->user_id}, référence={$payment->notchpay_reference}");
    }

    /**
     * Paiement échoué
     */
    protected function handlePaymentFailed(Payment $payment, array $data): void
    {
        $payment->update([
            'status'            => Payment::STATUS_FAILED,
            'failure_reason'    => $data['data']['message'] ?? 'Paiement échoué',
            'notchpay_response' => array_merge($payment->notchpay_response ?? [], $data['data'] ?? []),
        ]);

        Subscription::where('payment_id', $payment->id)
            ->where('status', Subscription::STATUS_PENDING)
            ->update(['status' => Subscription::STATUS_FAILED]);

        Log::warning("Paiement échoué: {$payment->notchpay_reference}");
    }

    /**
     * Paiement annulé
     */
    protected function handlePaymentCancelled(Payment $payment, array $data): void
    {
        $payment->update([
            'status'            => Payment::STATUS_CANCELLED,
            'notchpay_response' => array_merge($payment->notchpay_response ?? [], $data['data'] ?? []),
        ]);

        Subscription::where('payment_id', $payment->id)
            ->where('status', Subscription::STATUS_PENDING)
            ->update(['status' => Subscription::STATUS_CANCELLED, 'cancelled_at' => now()]);

        Log::info("Paiement annulé: {$payment->notchpay_reference}");
    }
}
