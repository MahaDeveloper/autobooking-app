<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class DriverAppValidation
{
    public static function returnValidation($validate)
    {
        if ($validate->fails()) {
            return ['status' => 'error', 'message' => $validate->errors()->first()];
        } else {
            return ['status' => 'success'];
        }
    }

    public static function registerValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'mobile' => 'required|regex:^[6-9]\d{9}$^|unique:drivers',
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required',
            'email' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function profileUpdateValidation($request,$id)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'mobile' => 'required|regex:^[6-9]\d{9}$^|unique:drivers,mobile,'.$id,
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required',
            'upi_id' => 'required',
            'qr_code' => 'image|mimes:jpeg,jpg,png',
        ]);

        return self::returnValidation($validate);
    }

    public static function proofUploadValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'proofs.*.image' => 'required',
            'proofs.*.number' => 'required',
            'proofs.*.type' => 'required',
            'proofs.*.verified' => 'required|string',
        ],
        [
           'proofs.*.image.required' => 'Proofs Image Field is Required',
           'proofs.*.number.required' => 'Proofs number field is Required',
           'proofs.*.type.required' => 'Proofs Type field is Required',
           'proofs.*.verified.required' => 'Proofs Verified Status is required',
        ]);
        return self::returnValidation($validate);
    }

    public static function transactionStatusValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'order_id' => 'required',
            'status' => 'required',
            'type' => 'required',//subscription or payatax
        ]);
        return self::returnValidation($validate);
    }

    public static function subscriptionPaymentValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function updateCurrentLocationValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'latitude' => 'required',
            'longitude' => 'required',
            'heading' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function rideAcceptRejectValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'search_ride_id' => 'required',
            'ride_status' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function payTaxValidation($request){

        $validate = Validator::make($request->all(),[
            'amount' => 'required',
            'pay_tax_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function turnOnlineValidation($request){

        $validate = Validator::make($request->all(),[
            'current_status' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function userPickupDetailValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function reachPickupLocationValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function rideOtpVerifyValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'otp' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function reachDropLocationValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'status' => 'required',
            'distance' => 'required',
            'pickup_latitude' => 'required',
            'pickup_longitude' => 'required',
            'drop_latitude' => 'required',
            'drop_longitude' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            // 'ride_type' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function qrPayValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function qrPaymentConfirmValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'status' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function addRideRequestValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function addRideAcceptRejectValidation($request){

        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'status' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function driverReviewStoreValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'driver_review' => 'required',
            'driver_rating' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function cancelRideValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'status' => 'required',
        ]);
        return self::returnValidation($validate);
    }

}
