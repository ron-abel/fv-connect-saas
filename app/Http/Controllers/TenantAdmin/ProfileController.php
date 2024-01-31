<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;


class ProfileController extends Controller
{
    public function __construct()
    {
        Controller::setSubDomainName();
    }

    /**
     * [GET] Profile Page for Admin
     */
    public function index()
    {
        return $this->_loadContent('admin.pages.profile');
    }

    /**
     * [POST] Update User Profile
     */
    public function profile_update(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {

            $validatedData = $request->validate([
                'full_name' => 'required|min:4|string|max:255',
                'email' => 'required|email|string|max:255|unique:users,email,' . auth()->user()->id,
            ]);
            $user = Auth::user();
            $user->full_name = $request['full_name'];
            $user->email = $request['email'];
            $user->save();

            return redirect()->back()->with('success', 'Profile Successfully Updated');
        }
    }

    /**
     * [POST] Update User Password
     */
    public function update_password(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {

            $validatedData = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:6',
                'confirm_password' => 'required|same:new_password',
            ]);

            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error_pass', 'You have entered wrong current password');
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->back()->with('success_pass', 'Password Successfully Updated');
        }
    }
}
