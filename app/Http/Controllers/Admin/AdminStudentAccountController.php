<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStudentAccountController extends Controller
{
    use FiltersTableColumns;

    public function updateClass(Request $request, int $userId)
    {
        if (!$this->hasTableSafe('student_profiles')) {
            return back()->with('error', 'La table student_profiles est introuvable.');
        }

        $request->validate([
            'school_class_id' => ['nullable', 'integer'],
        ]);

        $user = User::query()->with(['roles', 'role', 'studentProfile'])->findOrFail($userId);

        $profile = StudentProfile::query()->firstOrNew(['user_id' => $user->id]);
        $profile->fill($this->onlyExistingColumns('student_profiles', [
            'user_id' => $user->id,
            'school_class_id' => $request->filled('school_class_id') ? (int) $request->school_class_id : null,
        ]));
        $profile->save();

        return back()->with('success', 'Classe de l’élève mise à jour.');
    }

    public function updateSubscription(Request $request, int $userId)
    {
        if (!$this->hasTableSafe('subscriptions')) {
            return back()->with('error', 'La table subscriptions est introuvable.');
        }

        $request->validate([
            'subscription_plan_id' => ['nullable', 'integer'],
            'status' => ['required', 'string', 'max:50'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::query()->with(['subscriptions'])->findOrFail($userId);
        $plan = null;

        if ($request->filled('subscription_plan_id') && $this->hasTableSafe('subscription_plans')) {
            $plan = SubscriptionPlan::query()->find((int) $request->subscription_plan_id);
        }

        DB::transaction(function () use ($request, $user, $plan) {
            $subscription = $user->subscriptions()->latest()->first();

            if (!$subscription) {
                $subscription = new Subscription();
                $subscription->user_id = $user->id;
            }

            $startsAt = $request->filled('starts_at') ? $request->starts_at : ($subscription->starts_at ?: now());
            $endsAt = $request->filled('ends_at') ? $request->ends_at : $subscription->ends_at;

            if ($plan && !$request->filled('ends_at')) {
                $base = now();
                $durationValue = max(1, (int) ($plan->duration_value ?? 1));
                $durationUnit = $plan->duration_unit ?? 'month';

                $endsAt = match ($durationUnit) {
                    'day' => $base->copy()->addDays($durationValue),
                    'week' => $base->copy()->addWeeks($durationValue),
                    'year' => $base->copy()->addYears($durationValue),
                    default => $base->copy()->addMonths($durationValue),
                };
            }

            $data = [
                'user_id' => $user->id,
                'subscription_plan_id' => $plan?->id ?: $subscription->subscription_plan_id,
                'plan_name' => $plan?->name ?: ($subscription->plan_name ?: 'Abonnement manuel'),
                'status' => $request->status,
                'is_trial' => $request->boolean('is_trial'),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'cancelled_at' => in_array($request->status, [Subscription::STATUS_CANCELLED, Subscription::STATUS_FAILED], true) ? now() : null,
                'cancellation_reason' => $request->cancellation_reason,
            ];

            $subscription->fill($this->onlyExistingColumns('subscriptions', $data));
            $subscription->save();
        });

        return back()->with('success', 'Abonnement de l’élève mis à jour.');
    }
}
