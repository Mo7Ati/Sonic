<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkIncomingLocaleHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');
        if (!empty($locale) && in_array($locale, config('app.supported_locales'))) {
            app()->setLocale($locale);
        }
        return $next($request);
    }
}
