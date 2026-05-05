<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectAdminBankShortcut
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->is('backoffice-access/*')) {
            return $response;
        }

        if (!$response->headers->contains('Content-Type', 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || str_contains($content, 'data-timah-bank-shortcut')) {
            return $response;
        }

        $url = url('/backoffice-access/pedagogical-bank');
        $html = '<a data-timah-bank-shortcut href="' . e($url) . '" style="position:fixed;right:22px;bottom:22px;z-index:99999;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;text-decoration:none;border-radius:999px;padding:13px 18px;font-weight:900;box-shadow:0 16px 34px rgba(37,99,235,.32);border:1px solid rgba(255,255,255,.25)">📚 Banque</a>';

        if (str_contains($content, '</body>')) {
            $content = str_replace('</body>', $html . '</body>', $content);
        } else {
            $content .= $html;
        }

        $response->setContent($content);

        return $response;
    }
}
