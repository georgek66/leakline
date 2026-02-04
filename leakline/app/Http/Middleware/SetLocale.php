<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'el'];

        // 1) Check if the user already has locale stored in their session
        $locale = session('locale'); // session('key') → read

        // 2) If not chosen yet, auto-detect from browser
        if (! $locale) {
            $locale = $request->getPreferredLanguage($supported) ?? config('app.locale');

            // save it so next requests are consistent
            session(['locale' => $locale]); // session(['key' => value]) → write
        }

        // 3) Apply locale to Laravel
        app()->setLocale($locale);

        return $next($request);
    }

}
