<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\UserInvite;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;
use App\Services\SendGridServices;
use App\Services\SlackServices;
use Illuminate\Support\Facades\Log as Logging;

use App\Models\Variable;

class UserController extends Controller
{
    private $sendGridServices;
    private $slackServices;
    public $cur_tenant_id;
    public function __construct(SlackServices $slackServices)
    {
        $this->slackServices = $slackServices;
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
        $this->sendGridServices = new SendGridServices($this->cur_tenant_id);
    }

    /*
    * [GET] List of tenant users
    */
    public function users(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $roles = UserRole::whereIn('id', [2, 5])->get();
            return $this->_loadContent('admin.pages.users', compact('tenant_details', 'roles'));
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
            return view('error');
        }
    }

    /*
    * [GET] Change role user invite and user
    */
    public function change_invite_role(Request $request){
        try {
            $tenant_id = $this->cur_tenant_id;
            $invite = UserInvite::where('tenant_id', $tenant_id)->where('id', $request->get('invite_id'))->first();
            if (isset($invite) and !empty($invite)) {
                if (isset($invite->user) and !empty($invite->user)) {
                    $invite->user->user_role_id = $request->get('role_id');
                    $invite->user->save();
                }
                $invite->user_role_id = $request->get('role_id');
                $invite->save();
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /*
    * [GET] Delete user invite and related user
    */
    public function delete_invite_role(Request $request){
        try {
            $tenant_id = $this->cur_tenant_id;
            $invite = UserInvite::where('tenant_id', $tenant_id)->where('id', $request->get('invite_id'))->first();
            if (isset($invite) and !empty($invite)) {
                if (isset($invite->user) and !empty($invite->user)) {
                    $invite->user->delete();
                }
                $invite->delete();
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /*
    * [POST] Send user invite
    */
    public function user_invite($subdomain, Request $request){
        $request->validate([
            'email' => 'required|unique:user_invites,email'
        ]);
        try {
            $token = Hash::make(rand(0,999999));
            $link = url('/register_user?').http_build_query(['token'=>$token]);
            $tenant_id = $this->cur_tenant_id;
            $email = $request->email;
            if (isset($request->read_only) and !empty($request->read_only)) {
                $role = User::TENANT_VIEWER;
            }else {
                $role = User::TENANT_MANAGER;
            }

            $sg_data = [
                'user_email' => $email,
                'tenant_name' => $request->tenant_details->tenant_name,
                'signup_link' => $link,
            ];

            $data = [
                "tenant_id" => $tenant_id,
                "email" => $email,
                "token" => $token,
                "user_role_id" => $role,
            ];
            UserInvite::create($data);
            if ($this->sendGridServices->sendUserInvite($email, $sg_data)) {
                return redirect()->route('users', ['subdomain' => $subdomain])
                    ->with('success', 'User Invited Successfully!');
            }
        } catch (Exception $e) {
            return redirect()->route('users', ['subdomain' => $subdomain])
                ->with('error', $e->getMessage());
        }
    }

    /*
    * [GET] Register invited user
    */
    public function register_user($subdomain, Request $request){
        try{
            $tenant_id = $this->cur_tenant_id;
            $invite = UserInvite::where("tenant_id", $tenant_id)->where('token', $request->token)->first();
            $error = "";
            if (!isset($invite) and empty($invite)) {
                $error = "Link is invalid please contact support!";
            }
            return $this->_loadContent('admin.pages.register_user', ['error'=>$error, 'tenant_id'=>$tenant_id, 'invite' => $invite, 'subdomain'=>$subdomain, 'token'=>$request->token]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /*
    * [POST] Register invited user
    */
    public function post_register_user($subdomain, Request $request){
        $request->validate([
            'name' => 'required',
            'user-token' => "required",
            'password' => 'required | min:6',
            'confirm_password' => 'required|same:password'
        ]);
        try {
            $tenant_id = $this->cur_tenant_id;
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $data = $request->all();
            $inviteToken = $data['user-token'];
            $name = $data['name'];
            $password = $data['password'];
            $invite = UserInvite::where('token', $inviteToken)->first();
            $email = "";
            if (isset($invite) and !empty($invite)) {
                $email = $invite->email;

                $token = $tenant_details->tenant_name . \Str::random(32);

                $tenant_user =  User::create([
                    'user_role_id' => $invite->user_role_id,
                    'full_name' => $name,
                    'email' => $email,
                    'email_verified_at' => date("Y-m-d H:i:s"),
                    'tenant_id' => $invite->tenant_id,
                    'password' => Hash::make($request['password']),
                    'remember_token' => $token,
                ]);

                $invite->update(['user_id'=>$tenant_user->id]);

                return redirect()->route('admin.login', ['subdomain' => $subdomain])
                ->with('success', "User Registered Successfully");
            }

            return redirect()->route('user.register', ['subdomain' => $subdomain, 'token'=>$inviteToken])
                ->with('error', "No invite found for you");

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

