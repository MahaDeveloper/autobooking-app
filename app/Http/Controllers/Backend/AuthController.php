<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\BackendValidation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;

class AuthController extends Controller
{
    //admin login
    public function adminLogin(Request $request)
    {
        $validate = BackendValidation::loginValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);

        }
        if (!Auth::guard('admin')->once($request->only('username', 'password'))) {

            return response()->json(['status' => 'error', 'message' => "Please check your username or password"], 400);
        }
        $admin = Auth::guard('admin')->user();

        $admin->tokens()->delete();

        $permissions = $admin->role->permissions->pluck('code');

        if ($admin->status) {
           // $token = $admin->createToken($admin->username);
           $token = $admin->createToken($admin->username,['crm-permissions']);

            return response()->json(['status' => 'success', 'message' => "The Username and Password Has Been Verified", 'admin' => $admin, 'token' => $token->plainTextToken, 'permissions' => $permissions], 200);
        } else {

            return response()->json(['status' => 'error', 'message' => "Your Account Has Been Temporarily Closed"], 400);
        }
    }

    //logout
    public function logout()
    {
        $admin = request()->user();

        $admin->tokens()->delete();

        return response()->json(['status' => 'success', 'message' => 'Successfully Logged out'], 200);
    }

    //admin change password
    public function adminChangePassword(Request $request)
    {
        $validate = BackendValidation::adminPasswordValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $admin = $request->user();

        if (Hash::check($request->old_password, $admin->password)) {

            $admin->password = $request->password;
            $admin->save();
            return response()->json(['status' => 'success', 'message' => "The Password Has Been Changed"]);
        }
        return response()->json(['status' => 'error', 'message' => "Old password Does not match"]);
    }
}
