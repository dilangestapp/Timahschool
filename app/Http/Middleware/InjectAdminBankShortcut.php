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

        if (!$request->is('backoffice-access') && !$request->is('backoffice-access/*')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if ($contentType !== '' && !str_contains(strtolower($contentType), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || str_contains($content, 'data-timah-bank-shortcut')) {
            return $response;
        }

        $url = url('/backoffice-access/pedagogical-bank');
        $html = '<a data-timah-bank-shortcut href="' . e($url) . '" title="Ouvrir la Banque pédagogique" style="position:fixed;right:18px;bottom:18px;z-index:2147483647;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff!important;text-decoration:none!important;border-radius:999px;padding:14px 20px;font-weight:900;font-size:16px;line-height:1;box-shadow:0 18px 42px rgba(37,99,235,.42);border:2px solid rgba(255,255,255,.75);font-family:Arial,sans-serif">📚 Banque</a>';
        $html .= '<a data-timah-bank-shortcut href="' . e($url) . '" title="Ouvrir la Banque pédagogique" style="position:fixed;right:18px;top:92px;z-index:2147483647;background:#0f172a;color:#fff!important;text-decoration:none!important;border-radius:14px;padding:10px 14px;font-weight:900;font-size:14px;box-shadow:0 14px 30px rgba(15,23,42,.25);border:1px solid rgba(255,255,255,.35);font-family:Arial,sans-serif">📚 Banque pédagogique</a>';

        if (str_contains($content, '</body>')) {
            $content = str_replace('</body>', $html . '</body>', $content);
        } else {
            $content .= $html;
        }

        $response->setContent($content);

        return $response;
    }
}
