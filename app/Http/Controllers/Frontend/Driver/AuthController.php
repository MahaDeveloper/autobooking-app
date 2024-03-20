<?php

namespace App\Http\Controllers\Frontend\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\DriverAppValidation;
use App\Helpers\UserAppValidation;
use App\Helpers\MultipleLanguage;
use App\Models\Driver;
use App\Models\DriverDetail;
use App\Models\DriverProof;
use App\Events\DriverDetailSave;
use App\Events\SendSmsEvent;
use App\Events\DriverProofSave;
use App\Events\DriverUserNotificationEvent;
use App\Interfaces\AuthenticateInterface;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\SupportInterface;

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
        $validate = DriverAppValidation::registerValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        if($request->referal_mobile){
            $refer_user = Driver::where('mobile',$request->referal_mobile)->first();

            if(!($refer_user))
                return response()->json(['status' => 'error','message' => 'Referral Mobile Number Does Not Match!'],400);
        }
        $driver  = new Driver();
        $driver->name = $request->name;

        $language_names = MultipleLanguage::allLanguages($request->name);

        $driver->languages_name = json_encode($language_names);

        $driver->mobile = $request->mobile;
        $driver->fcm_id = $request->fcm_id;
        $driver->latitude = $request->latitude;
        $driver->longitude = $request->longitude;
        $driver->selected_language_id = $request->language_id;

        if($request->referal_mobile){
            $driver->refferal_id = $refer_user->id;
        }

        if($request->hasFile('image')){
            $name = Storage::disk('digitalocean')->putFile('driver_images',$request->image,'public');
            $driver->image = $name;
        }
        $driver->save();

        $title = "AutoKaar";

        if($request->referal_mobile){

            $msg = "Hi ".$refer_user->name.", thank you for referral.

            We really appreciate your support.";

            DriverUserNotificationEvent::dispatch($driver, null, $title, $msg,null,'mrautokaar');
        }

        DriverDetailSave::dispatch($request,$driver->id);

        $driver->tokens()->delete();
        $token = $driver->createToken($driver->name);

        if(env('APP_ENV') != 'localhost'){

            $msg = "Hi ".$driver->name.", we request you update the necessary details and complete your account profile in ATOKAR.";

            SendSmsEvent::dispatch('1207168121956454361',$driver->mobile, $msg);
        }
        return response()->json(['status' => 'success','message' => 'registration successfully','token' => $token->plainTextToken],200);
    }

    public function profileUpdate(Request $request)
    {
        $driver = auth()->user();

        $validate = DriverAppValidation::profileUpdateValidation($request,$driver->id);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $driver->name = $request->name;

        $language_names = MultipleLanguage::allLanguages($request->name);
        $driver->languages_name = json_encode($language_names);

        $driver->mobile = $request->mobile;
        $driver->latitude = $request->latitude;
        $driver->longitude = $request->longitude;

        if($request->hasFile('image')){
            $name = Storage::disk('digitalocean')->putFile('driver_images',$request->image,'public');
            $driver->image = $name;
        }
        $driver->save();

        DriverDetailSave::dispatch($request,$driver->id);

        return response()->json(['status' => 'success','message' => 'profile updated successfully'],200);
    }

    public function proofUpload(Request $request){

        $validate = DriverAppValidation::proofUploadValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $driver = auth()->user();

        // $driver_detail = DriverDetail::where('driver_id',$driver->id)->first();

        // if($request->hasFile('qr_code')){
        //     $name = Storage::disk('digitalocean')->putFile('driver_images',$request->qr_code,'public');
        //     $driver_detail->qr_code = $name;
        // }
        // $driver_detail->save();

        $proof_upload = DriverProofSave::dispatch($request,$driver->id);

        if($proof_upload[0] == 'error'){

            return response()->json(['status' => 'success','message' => 'The Proof Type Has Been Already Existing'],400);
        }

        $driver->verification_status = 1; //proof request
        $driver->save();

        if(env('APP_ENV') != 'localhost'){

            $msg = "Hi ".$driver->name.", you have successfully completed the registration process in ATOKAR.

            Please wait while we review your documents and the account details. This process may take 2-3 working days.

            We do appreciate your patience.";

            SendSmsEvent::dispatch('1207168121966071074',$driver->mobile, $msg);
        }

        return response()->json(['status' => 'success','message' => 'Proofs Uploaded successfully'],200);
    }

    public function profileView(){

        $driver= Driver::with('driverDetail','driverProofs')->find(auth()->user()->id);

        return response()->json(['status' => 'success','driver' => $driver],200);
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        $user->tokens()->delete();

        $user->current_status = 2; //offline
        $user->save();

        return response()->json(['status' => 'success','message' => 'Successfully Logged out'],200);
    }

    public function storeSupport(Request $request,SupportInterface $support){

        $validate = UserAppValidation::supportValidation($request);

        if ($validate['status'] == "error") {
            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $support->supportStore($request);

        return response()->json(['status' => 'success', 'message' => "The Support Has Been Saved"], 200);
    }

    public function deleteAccount(Request $request){

        $driver = auth()->user();

        $driver_detail = DriverDetail::where('driver_id',$driver->id)->delete();

        $driver_proof = DriverProof::where('driver_id',$driver->id)->delete();

        $driver->delete();

        return response()->json(['status' => 'success','message' => 'Your Account Has Been Deleted'],200);
    }

}

