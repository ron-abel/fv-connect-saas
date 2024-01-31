<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $domain_name = explode('.', URL::current());
        $subdomain = substr($domain_name[0], strrpos($domain_name[0], '/') + 1);
        $tenant_details = DB::table('tenants')->where('tenant_name', $subdomain)->first();

        if ($tenant_details) {
            $user_id = Auth::id();
            
            $request->merge(array("tenant_details" => $tenant_details));

            $config_details = DB::table('config')->where('tenant_id', $tenant_details->id)->first();

            $request->merge(array("settings_details" => $config_details));

            view()->share('subdomain', $tenant_details->tenant_name);

            $route_name = Route::currentRouteName();
            if (!Auth::check() && $route_name != 'admin.login') {
                return redirect()->route('admin.login', ['subdomain' =>  $tenant_details->tenant_name]);
            }
            if ($user_id || $route_name == 'login') {

                $user_details = Auth::user();
                // check tenant  and user's tenant
                if(isset($user_details['tenant_id'], $tenant_details->id)){
                    if($user_details['tenant_id'] == $tenant_details->id){
                        view()->share('user_details', $user_details);
                        view()->share('config_details', $config_details);
                        return $next($request);
                    }
                }

                // logout
                Auth::logout();

                return redirect()->route('admin.login', ['subdomain' =>  $tenant_details->tenant_name]);
            } else {
                //Check if URL Contains 'admin' 
                $adminCheck = substr(URL::current(), strrpos(URL::current(), '/') + 1);
                if ($adminCheck == 'admin') {
                    return redirect()->route('admin.login', ['subdomain' =>  $tenant_details->tenant_name]);
                }

                //Check if subdomain is tenant
                if ($subdomain == $tenant_details->tenant_name && strpos(URL::current(), 'admin') == false) {
                    return redirect()->route('client', ['subdomain' =>  $tenant_details->tenant_name]);
                }

                return $next($request);
                // return redirect()->route('admin.login',  ['subdomain' =>  $tenant_details->tenant_name]);
            }
        }else{
            return redirect()->route('super.welcome');
        }

        return redirect()->route('invalid');
    }
}
