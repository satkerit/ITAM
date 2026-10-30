<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Tambahkan header keamanan (termasuk CSP) pada setiap respons.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        Vite::useCspNonce($nonce);

        $response = $next($request);

        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Content-Security-Policy' => $this->contentSecurityPolicy($nonce),
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    /**
     * Bangun kebijakan CSP. 'unsafe-eval' diperlukan Alpine.js yang mengevaluasi ekspresi x-data.
     */
    private function contentSecurityPolicy(string $nonce): string
    {
        $scriptSources = ["'self'", "'nonce-{$nonce}'", "'unsafe-eval'", 'blob:'];
        $styleSources = ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'];
        $connectSources = ["'self'", 'blob:', 'data:'];

        if (app()->environment('local')) {
            $devServer = ['http://localhost:5173', 'http://127.0.0.1:5173'];
            $scriptSources = [...$scriptSources, ...$devServer];
            $styleSources = [...$styleSources, ...$devServer];
            $connectSources = [...$connectSources, ...$devServer, 'ws://localhost:5173', 'ws://127.0.0.1:5173'];
        }

        $directives = [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
            'frame-ancestors' => ["'self'"],
            'form-action' => ["'self'"],
            'script-src' => $scriptSources,
            'style-src' => $styleSources,
            'font-src' => ["'self'", 'data:', 'https://fonts.gstatic.com'],
            'img-src' => ["'self'", 'data:', 'blob:'],
            'media-src' => ["'self'", 'blob:'],
            'connect-src' => $connectSources,
            'worker-src' => ["'self'", 'blob:'],
        ];

        $policies = [];

        foreach ($directives as $directive => $sources) {
            $policies[] = $directive.' '.implode(' ', $sources);
        }

        return implode('; ', $policies);
    }
}
