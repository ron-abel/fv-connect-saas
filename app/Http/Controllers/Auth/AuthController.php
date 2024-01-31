<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Log;
use App\Models\AdminLog;
use App\Mail\ResetPasswordMail;
use Auth;
use Hash;
use URL;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Mail;
use App\Services\SendGridServices;
use Illuminate\Support\Facades\Log as Logging;
use App\Models\SubscriptionCustomer;

class AuthController extends Controller
{
    private $sendGridServices;
    public $sub_domain_name;
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->sub_domain_name = session()->get('subdomain');
        $this->cur_tenant_id = session()->get('tenant_id');
        $this->sendGridServices = new SendGridServices($this->cur_tenant_id);
    }

    public function register()
    {
        return $this->_loadContent('auth.register');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        User::create([
            'tenant_id' => 1,
            'full_name' => 'FV Connect',
            'email' => 'filevinecvadmin@fvconnect.com',
            'password' => Hash::make('Abc123'),
            'user_role_id' => User::TENANT_MANAGER,
        ]);

        return redirect('home');
    }

    /**
     * [GET] login for Admin
     */
    public function login(Request $request)
    {
        $subdomain = $this->sub_domain_name;
        $tenant_id = $this->cur_tenant_id;
        $tenant_details = Tenant::where('tenant_name', $subdomain)->first();
        $user_details = User::where('admin_token', $request->get('token', ''))->first();
        if (isset($user_details) and !empty($user_details)) {
            $msg = $this->LogAndSendAdminLoginNote($user_details, $this->getRealUserIp(), $request->header('User-Agent'), $tenant_details);
            session()->put('successMessage', $msg);
        }
        $user_id = Auth::id();
        $user_details = Auth::user();
        if (isset($user_id, $user_details, $user_details->user_role_id)) {
            if ($user_details->user_role_id == User::SUPERADMIN) {
                return redirect()->route('tenants');
            } else {
                return redirect()->route('dashboard', [
                    'subdomain' => $subdomain,
                ]);
            }
        } else {

            if ($subdomain == config('app.superadmin')) {
                return $this->_loadContent('superadmin.pages.login', ['tenant_id' => $tenant_id]);
            } else {
                return $this->_loadContent('admin.pages.login', ['tenant_id' => $tenant_id]);
            }
        }
    }

    /**
     * [POST] login for Tenant Admin
     */
    public function authenticate(Request $request)
    {
        $subdomain = $this->sub_domain_name;

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $remember_me = $request->has('remember') ? true : false;
        $tenant_details = Tenant::where('tenant_name', $subdomain)->first();

        // $credentials = ['email' => $request->input('email'), 'password' => $request->input('password'), 'tenant_id' => $tenant_details->id];
        $credentials = ['email' => $request->input('email'), 'password' => $request->input('password')];

        if (Auth::attempt($credentials, $remember_me)) {
            $user_details = Auth::user();
            if (isset($user_details->user_role_id)) {
                if ($user_details->user_role_id == User::SUPERADMIN) {
                    Auth::logout();
                    return redirect()->route('tenants');
                } elseif ($user_details->user_role_id == User::TENANT_SUPPORTER) {
                    // get user of tenant
                    $tenant_user = User::where('tenant_id', $tenant_details->id)
                        ->where('user_role_id', User::TENANT_OWNER)
                        ->first();
                    if ($tenant_user) {
                        Auth::loginUsingId($tenant_user->id, TRUE);
                        $this->updateSubscription($tenant_details->id);
                        return redirect()->route('dashboard', ['subdomain' => $subdomain]);
                    } else {
                        Auth::logout();
                    }
                } elseif (in_array($user_details->user_role_id, [User::TENANT_OWNER, User::TENANT_MANAGER, User::TENANT_VIEWER])) {
                    if ($user_details->tenant_id == $tenant_details->id) {
                        $msg = $this->LogAndSendAdminLoginNote($user_details, $this->getRealUserIp(), $request->header('User-Agent'), $tenant_details);
                        $this->updateSubscription($tenant_details->id);
                        if ($msg) {
                            Auth::logout();
                            $redirect = redirect()->route('admin.login', ['subdomain' => $subdomain])->with('info', $msg);
                        } else {
                            $redirect = redirect()->route('dashboard', ['subdomain' => $subdomain]);
                        }
                        return $redirect;
                    } else {
                        Auth::logout();
                    }
                }
            }
        }

        return redirect()->route('admin.login', ['subdomain' => $subdomain])->with('error', 'Invalid login info!');
    }



    /**
     * Update Subscription status based on trial and end date
     */
    public function updateSubscription($tenant_id)
    {
        try {
            $subscribedCustomer = SubscriptionCustomer::where('tenant_id', $tenant_id)->first();
            if (isset($subscribedCustomer->id)) {
                $subscriptions = DB::table('subscriptions')->where('subscription_customer_id', $subscribedCustomer->id)->whereNotIn('stripe_status', ['canceled', 'ended'])->get();
                foreach ($subscriptions as $subscription) {
                    $trial_ends_at = $subscription->trial_ends_at;
                    $ends_at = Carbon::parse($subscription->ends_at)->format('Y-m-d');
                    $ends_at = Carbon::createFromFormat('Y-m-d', $ends_at)->endOfDay()->toDateTimeString();

                    if (!empty($trial_ends_at) && (time() > strtotime($trial_ends_at)) && (time() <= strtotime($ends_at))) {
                        DB::table('subscriptions')->where('id', $subscription->id)->update([
                            'updated_at' => Carbon::now(),
                            'stripe_status' => 'active'
                        ]);
                    } else if (!empty($ends_at) && (time() > strtotime($ends_at))) {
                        DB::table('subscriptions')->where('id', $subscription->id)->update([
                            'updated_at' => Carbon::now(),
                            'stripe_status' => 'ended'
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * [POST] login for Super Admin
     */
    public function authenticate_superadmin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $remember_me = $request->has('remember') ? true : false;
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $remember_me)) {
            $user_details = Auth::user();

            if (isset($user_details) && $user_details->user_role_id == User::SUPERADMIN) {

                $msg = $this->LogAndSendAdminLoginNote($user_details, $this->getRealUserIp(), $request->header('User-Agent'));
                $redirect = redirect()->route('tenants');
                if (!empty($msg)) {
                    $redirect = redirect()->route('super.login')->with('info', $msg);
                }
                return $redirect;
            } elseif (isset($user_details) && $user_details->user_role_id == User::TENANT_SUPPORTER) {
                return redirect()->route('tenants');
            } else {
                Auth::logout();
            }
        }

        return redirect()->route('super.login')->with('error', 'Invalid login info!');
    }


    public function logout()
    {
        try {
            $subdomain = $this->sub_domain_name;

            session()->forget('tenant_id');
            session()->forget('subdomain');

            if ($subdomain == config('app.superadmin')) {
                Auth::logout();
                return redirect()->route('super.login');
            }
            Auth::logout();
            return redirect()->route('admin.login', ['subdomain' => $subdomain]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * [GET] Send reset token email
     */
    private function sendResetEmail($email, $token)
    {
        try {

            $user = DB::table('users')->where('email', $email)->select('full_name', 'email')->first();

            //Generate, the password reset link. The token generated is embedded in the link
            $link = url('/password/reset/' . $token);
            //Send the link with token to user email
            Mail::to($email)->send(new ResetPasswordMail($user, $link));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * [GET] Show Forgot Password Form
     */
    public function showForgotPasswordForm()
    {
        try {
            if (Auth::check()) {
                return redirect()->route('dashboard', ['subdomain' => $this->sub_domain_name]);
            }
            return $this->_loadContent('admin.pages.forgot-password');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Validate Request From Forgot password form and send token
     */
    public function validatePasswordRequest(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
        ]);

        try {

            $user = DB::table('users')
                ->where('email', '=', $request->email)
                ->first();

            if ($user == [] || !isset($user->full_name, $user->email)) {
                return redirect()->back()->withErrors(['email' => trans('User does not exist')]);
            }

            //Create Password Reset Token
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => \Str::random(60),
                'created_at' => Carbon::now()
            ]);


            $tokenData = DB::table('password_resets')
                ->where('email', $request->email)->first();

            $tenant = DB::table('tenants')->where('id', $user->tenant_id)->first();

            // get tenant from user.
            $tenant_name = $tenant->tenant_name ?? "";
            if (isset($tenant->tenant_law_firm_name)) {
                $tenant_name = $tenant->tenant_law_firm_name;
            }

            //Generate, the password reset link. The token generated is embedded in the link
            $link = url('/password/reset/' . $tokenData->token);

            $sg_data = [
                'user_name' => $user->full_name,
                'tenant_name' => $tenant_name,
                'verify_link' => $link,
            ];

            if ($this->sendGridServices->sendResetPassword($request->email, $sg_data)) {
                $infoMessage =  'A reset link has been sent to your email address.';
                return $this->_loadContent('admin.pages.info', compact('infoMessage'));
            } else {
                return redirect()->back()->with('error', "Sorry, We couldn't send email. Please check your credentials");
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * [GET] Show Reset Form from email token
     */
    public function showResetForm($subdomain, $token)
    {
        try {
            $user = DB::table('password_resets')->where('token', '=', $token)->first();
            if (!$user) return $this->_loadContent('admin.pages.link_expired');
            $email = $user->email;
            return $this->_loadContent('admin.pages.reset', compact('token', 'email'));
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Submit Reset form to reset password
     */
    public function resetPassword(Request $request)
    {
        //Validate input
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6',
            'confirm_password' => 'same:password',
            'token' => 'required'
        ]);


        $password = $request->password;

        // Validate the token
        $tokenData = DB::table('password_resets')
            ->where('token', $request->token)->first();
        // Redirect the user back to the password reset request form if the token is invalid
        if (!$tokenData) return redirect()->back()->with('info', 'Sorry the link is invalid or has been expired. Please try again to reset Password!');

        $user = User::where('email', $tokenData->email)->first();

        // Redirect the user back if the email is invalid
        if (!$user) return redirect()->back()->withErrors(['email' => 'Email not found']);

        try {
            $user->password = \Hash::make($password);
            $user->update(); //or $user->save();

            //login the user immediately they change password successfully
            // Auth::login($user);

            //Delete the token
            DB::table('password_resets')->where('email', $user->email)
                ->delete();


            return redirect()->route('admin.login', ['subdomain' => session()->get('subdomain')])->with('success', 'Password Reset Completed. Please try to login');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function getRealUserIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;

        // switch(true){
        //     case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
        //     case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
        //     case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
        //     default : return $_SERVER['REMOTE_ADDR'];
        // }
    }

    /**
     * Log and send admin login note
     */
    public function LogAndSendAdminLoginNote($user_details, $ip, $device, $tenant_details = null)
    {
        try {
            $is_log_created = 0;
            $admin_logs = AdminLog::where('ip', $ip)->where('device', $device);
            $admin_log = $admin_logs->where('user_id', $user_details->id)->where('tenant_id', $tenant_details ? $tenant_details->id : 0)->orderBy('updated_at', 'DESC');
            $admin_log = $admin_log->first();
            if (!$admin_log || ($admin_log && $admin_log->is_confirmed == 0)) {
                $tenant_name = $tenant_details ? $tenant_details->tenant_name : '';
                $isNew = AdminLog::where('user_id', $user_details->id)->where('tenant_id', $tenant_details ? $tenant_details->id : 0)->count() == 0;

                if (!$admin_log) {
                    //Create admin log
                    $admin_log = AdminLog::create([
                        'ip' => $ip,
                        'device' => $device,
                        'tenant_id' => $tenant_details ? $tenant_details->id : 0,
                        'user_id' => $user_details->id,
                        'is_confirmed' => $isNew ? 1 : 0,
                    ]);
                    $is_log_created = 1;
                } else if ($admin_log->is_confirmed == 0) {
                    // Check if token is in the request and match from user record then set is_confirmed to 1
                    if ($user_details->admin_token == request()->get('token', '')) {
                        $admin_log->is_confirmed = 1;
                        $admin_log->save();

                        return "Your IP has been approved successfully.";
                    }
                }

                if (!$isNew) {

                    $tenant_IP_config = DB::table('config')->select('ip_verification_enable')->where('tenant_id', '=', $user_details->tenant_id)->first();
                    $verification_enable = 1;
                    if (is_null($tenant_IP_config)) {
                        $verification_enable = 0;
                    } else {
                        $verification_enable = $tenant_IP_config->ip_verification_enable;
                    }
                    if ($verification_enable == 1) {
                        // Send admin login note
                        $attempts = AdminLog::where('ip', $ip)->where('device', $device)->where('user_id', $user_details->id)->where('tenant_id', $tenant_details ? $tenant_details->id : 0)->where('is_request_verify', 1)->where(
                            'created_at',
                            '>=',
                            Carbon::now()->subHours(env('TENANT_ADMIN_LOGIN_VERIFY_MAX_TIME', 1))->toDateTimeString()
                        );
                        if ($attempts->count() >= env('TENANT_ADMIN_LOGIN_VERIFY_MAX_COUNT', 3)) {
                            return "Already sent the verification email to your email address, please confirm your email";
                        } else {
                            if ($is_log_created) {
                                $admin_log->is_request_verify = 1;
                                $admin_log->save();
                            } else {
                                $admin_log = AdminLog::create([
                                    'ip' => $ip,
                                    'device' => $device,
                                    'tenant_id' => $tenant_details ? $tenant_details->id : 0,
                                    'user_id' => $user_details->id,
                                    'is_confirmed' => 0,
                                    'is_request_verify' => 1
                                ]);
                            }
                            $this->sendGridServices->sendAdminLoginNote($user_details, $ip, $device, $tenant_name, $admin_log->tenant_id);
                            return "We sent a email to confirm your IP and Device info. Please check your email and visit the link to verify!";
                        }
                    } else {
                        return '';
                    }
                }
            }
            return '';
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
