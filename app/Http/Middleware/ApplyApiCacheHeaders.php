<?php

namespace App\Http\Middleware;

use App\Support\PortalCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyApiCacheHeaders
{
    public function handle(Request $request, Closure $next, string $visibility = 'private', int|string|null $maxAge = null): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->isMethodCacheable() && $response->isSuccessful() && ! $response->headers->has('Cache-Control')) {
            $response->headers->set(
                'Cache-Control',
                PortalCache::cacheControlHeader($visibility, is_numeric($maxAge) ? (int) $maxAge : null)
            );
            $response->headers->set('Vary', 'Accept, Authorization', false);
        }

        return $response;
    }
}
