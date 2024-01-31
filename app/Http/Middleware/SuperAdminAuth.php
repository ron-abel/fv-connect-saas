<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class SuperAdminAuth
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

        $user_id = Auth::id();

        $sub_domain_name = config('app.superadmin');

        if($user_id){
          $user_details = Auth::user();

          view()->share('user_details', $user_details);
          view()->share('sub_domain', $sub_domain_name);

          return $next($request);
        } else{
          return redirect()->route('super.login');
        }
    }
}
