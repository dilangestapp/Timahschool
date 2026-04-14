<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasActiveSubscription
{
    /**
     * Bloquer l'accès si l'utilisateur n'a pas d'abonnement actif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasActiveSubscription()) {
            return $next($request);
        }

        $latestSubscription = $user->subscriptions()->latest('id')->first();

        if (!$latestSubscription) {
            return redirect()->route('student.subscription.required');
        }

        if ($latestSubscription->status === Subscription::STATUS_PENDING) {
            return redirect()->route('student.subscription.pending');
        }

        return redirect()->route('student.subscription.expired');
    }
}
