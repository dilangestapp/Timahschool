<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'webhook/notchpay',

        // Déconnexion robuste : évite l'erreur 419 lorsque l'utilisateur
        // passe d'un type de compte à un autre après expiration du token CSRF.
        'logout',
        '*/logout',
    ];
}
