<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\UserAppValidation;
use App\Models\OtpVerification;
use App\Helpers\MultipleLanguage;
use App\Models\User;
use App\Models\Language;
use App\Interfaces\AuthenticateInterface;
use App\Models\UserAddress;
use App\Models\UserEmergencyContact;
use App\Events\UserAddressSave;
use App\Events\SendSmsEvent;
use App\Events\DriverUserNotificationEvent;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function requestOtp(AuthenticateInterface $AuthenticateService,Request $request)
    {
        $validate = UserAppValidation::requestOtpValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $response = $AuthenticateService->otpRequest($request);

        if(env('APP_ENV') != 'localhost'){

            $msg = "Welcome! Please enter this code ".$response['random']." to login Mr. AutoKaar Application. Our Fare is Fair!";

            SendSmsEvent::dispatch('1207168059562027366',$request->mobile, $msg);
        }

        return response()->json(['status' => 'success','message' => 'your OTP is : '.$response['random']],200);

    }

    public function checkOtp(AuthenticateInterface $AuthenticateService,Request $request)
    {
        $validate = UserAppValidation::checkOtpValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $response = $AuthenticateService->checkOtp($request);

        if($response['status'] == 'error'){

            return response()->json(['status' => 'error','message' => $response['message']],400);

        }elseif($response['status'] == 'success' && $response['user_type'] =='existing'){

            return response()->json(['status' => 'success','message' => $response['message'],'user_type' => $response['user_type'],'token' => $response['token'], 'existing_user' => $response['existing_user']],200);
        }
        else{

            return response()->json(['status' => 'success','message' =>$response['message'], 'user_type' => $response['user_type']],200);
        }
    }

    public function register(Request $request)
    {
        $validate = UserAppValidation::userRegisterValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        if($request->referal_mobile){

            $refer_user = User::where('mobile',$request->referal_mobile)->first();

            if(!($refer_user))
                return response()->json(['status' => 'error','message' => 'Referral Mobile Number Does Not Match!'],400);
        }
        $user  = new User();
        $user->name = $request->name;

        $language_names = MultipleLanguage::allLanguages($request->name);

        $user->languages_name = json_encode($language_names);

        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->fcm_id = $request->fcm_id;
        if($request->dob)
        $user->dob = $request->dob;
        if($request->gender)
        $user->gender = $request->gender;
        $user->selected_language_id = $request->language_id;

        if($request->referal_mobile){
            $user->refferal_id = $refer_user->id;
        }
        if($request->hasFile('image')){
            $name = Storage::disk('digitalocean')->putFile('user_images',$request->image,'public');
            $user->image = $name;
        }
        $user->save();

        $token = $user->createToken($user->name);

        $title = "AutoKaar";

        $msg = "Hi ".$user->name.", we request you update the necessary details and complete your account profile in ATOKAR.";

        if($request->referal_mobile){

            $msg = "Hi ".$refer_user->name.", thank you for referral.

            We really appreciate your support.";
        }

        DriverUserNotificationEvent::dispatch($user, null, $title, $msg,null,'mrautokaar');

        if(env('APP_ENV') != 'localhost'){

            SendSmsEvent::dispatch('1207168121956454361',$user->mobile, $msg);
        }

        return response()->json(['status' => 'success','message' => 'registration successfully','token' => $token->plainTextToken],200);
    }

    public function profileUpdate(Request $request)
    {
        // return $request;
        $user = auth()->user();

        $validate = UserAppValidation::userProfileUpdateValidation($request,$user->id);

        if($validate['status'] == "error"){
            
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $user->name = $request->name;

        $language_names = MultipleLanguage::allLanguages($request->name);
        $user->languages_name = json_encode($language_names);

        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->primary_mobile = $request->primary_mobile;

        if($request->hasFile('image')){
            $name = Storage::disk('digitalocean')->putFile('user_images',$request->image,'public');
            $user->image = $name;
        }
        $user->save();

        $user_address = UserAddressSave::dispatch($request,$user->id);

        if($user_address[0] == 'type-exist'){

            return response()->json(['status' => 'error','message' => 'The Address Type Has Been Exist!!'],400);
        }

        $user_detail = User::with('userAddresses','userEmergencyContacts')->find($user->id);

        return response()->json(['status' => 'success','message' => 'profile updated successfully','user_detail' => $user_detail],200);
    }

    public function profileView(){

        $user = auth()->user();

        $user_detail= User::with('userAddresses','userEmergencyContacts')->find($user->id);

        return response()->json(['status' => 'success','user_detail' => $user_detail],200);
    }

    public function logout(Request $request)
    {
        $user = request()->user();
        $user->tokens()->delete();

        return response()->json(['status' => 'success','message' => 'Successfully Logged out'],200);
    }

    public function languages()
    {
        $languages = Language::where('status',1)->get();

        return response()->json(['status' => 'success','languages' => $languages],200);
    }

    public function deleteAccount(Request $request){

        $user = auth()->user();

        $user_addresses = UserAddress::where('user_id',$user->id)->delete();

        $user_sos = UserEmergencyContact::where('user_id',$user->id)->delete();

        $user->delete();

        return response()->json(['status' => 'success','message' => 'Your Account Has Been Deleted'],200);
    }


}
