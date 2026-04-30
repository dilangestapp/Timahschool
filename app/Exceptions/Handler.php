<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (TokenMismatchException $e, $request) {
            return $this->redirectExpiredSession($request);
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 419) {
            return $this->redirectExpiredSession($request);
        }

        return parent::render($request, $e);
    }

    private function redirectExpiredSession($request)
    {
        if ($request->expectsJson() || $request->is('__timah/internal/*')) {
            return response()->json([
                'status' => 'session_expired',
                'message' => 'Session expirée. Rechargez la page puis recommencez.',
            ], 419);
        }

        if ($request->is('backoffice-access') || $request->is('backoffice-access/*')) {
            return redirect()->route('admin.login')
                ->with('error', 'La session avait expiré. Reconnectez-vous simplement.');
        }

        if ($request->is('teacher') || $request->is('teacher/*')) {
            return redirect()->route('login')
                ->with('error', 'La session avait expiré. Reconnectez-vous simplement.');
        }

        if ($request->is('student') || $request->is('student/*')) {
            return redirect()->route('login')
                ->with('error', 'La session avait expiré. Reconnectez-vous simplement.');
        }

        return redirect()->route('login')
            ->with('error', 'La session avait expiré. Rechargez la page puis reconnectez-vous.');
    }
}
