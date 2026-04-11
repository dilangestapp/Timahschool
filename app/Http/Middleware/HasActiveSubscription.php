<?php

namespace App\Http\Middleware;

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

        $subscription = $user->activeSubscription;

        if (!$subscription || !$subscription->isActive()) {
            return redirect()->route('student.subscription.expired');
        }

        return $next($request);
    }
}
