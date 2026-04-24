<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserLoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserActivityRecorder
{
    public static function record(?User $user, Request $request, string $event = 'login', ?string $guard = null): void
    {
        if (!$user) {
            return;
        }

        try {
            if (!Schema::hasTable('user_login_activities')) {
                return;
            }

            $sessionId = null;
            try {
                $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            } catch (\Throwable $e) {
                $sessionId = null;
            }

            UserLoginActivity::query()->create([
                'user_id' => $user->id,
                'event' => $event,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'guard' => $guard,
                'session_id' => $sessionId,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            return;
        }
    }
}
