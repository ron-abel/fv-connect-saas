<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LanguageLog;

class ClientMultiLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $lang = request()->get("lang");
        if ($lang) {
            session()->put('lang', $lang);
            app()->setLocale($lang);
        } elseif (!$lang && !is_null(session('lang'))) {
            app()->setLocale(session('lang'));
        } else {
            $client_ip = $request->getClientIp();
            $language = LanguageLog::where('client_ip', $client_ip)->orderBy('updated_at', 'DESC')->first();
            if ($language != null) {
                $latest_language = $language->language;
                session()->put('lang', $latest_language);
                app()->setLocale($latest_language);
            }
        }
        return $next($request);
    }
}
