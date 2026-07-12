<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);
        $scriptSources = ["'self'", "'unsafe-inline'"];
        $styleSources = ["'self'", "'unsafe-inline'"];
        $fontSources = ["'self'", 'data:'];
        $connectSources = ["'self'", 'ws:', 'wss:'];

        if (app()->isLocal()) {
            $viteOrigins = ['http:'];
            array_push($scriptSources, "'unsafe-eval'", ...$viteOrigins);
            array_push($styleSources, ...$viteOrigins);
            array_push($fontSources, ...$viteOrigins);
            array_push($connectSources, ...$viteOrigins);
            array_push($connectSources, 'ws:');
        }

        $directives = [
            "default-src 'self'",
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
            "img-src 'self' data: blob: https://image.tmdb.org",
            'font-src '.implode(' ', $fontSources),
            "media-src 'self' blob:",
            'frame-src https://www.youtube-nocookie.com',
            'connect-src '.implode(' ', $connectSources),
            "worker-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $directives));
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        return $response;
    }
}
