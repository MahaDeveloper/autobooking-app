<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class UserAppValidation
{
    public static function returnValidation($validate)
    {
        if ($validate->fails()) {
            return ['status' => 'error', 'message' => $validate->errors()->first()];
        } else {
            return ['status' => 'success'];
        }
    }

    public static function requestOtpValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'mobile' => 'required',
            'type' => 'required',
        ]);

        return self::returnValidation($validate);
    }

    public static function checkOtpValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'otp' => 'required',
            'mobile' => 'required',
            'type' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function userRegisterValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'mobile' => 'required|regex:^[6-9]\d{9}$^|unique:users',
            'email' => 'required|email|unique:users|regex:/(.*)@*\.*/i',
        ]);
        return self::returnValidation($validate);
    }

    public static function userProfileUpdateValidation($request,$id)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'mobile' => 'required|regex:^[6-9]\d{9}$^|unique:users,mobile,'.$id,
            'email' => 'required|email|regex:/(.*)@*\.*/i|unique:users,email,'.$id,
        ]);
        return self::returnValidation($validate);
    }

    public static function supportValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required|sometimes',
            'description' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function driverRegisterValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'mobile' => 'required|regex:^[6-9]\d{9}$^|unique:drivers',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function driverProfileUpdateValidation($request,$id)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'mobile' => 'required|regex:^[6-9]\d{9}$^|unique:drivers,mobile,'.$id,
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required',
            'upi_id' => 'required',
            'qr_code' => 'required',
            'proofs' => 'required|array',
        ]);
        return self::returnValidation($validate);
    }

    public static function checkAreaValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'pickup_pincode' => 'required',
            'drop_pincode' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function rideFareValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'distance' => 'required|numeric|max:25|min:0',
        ],
        [
            'distance.max' => 'The Maximum Distance Should Be In 25 Km.'
        ]);

        return self::returnValidation($validate);
    }

    public static function emergencyContactValidation($request,$id)
    {
        if ($id == null) {

            $validate = Validator::make($request->all(), [
                'contacts' => 'required|array',
                'contacts.*.mobile' => 'required|unique:user_emergency_contacts',
            ],
            [
                'contacts.*.mobile.required' => 'Mobile Field is Required with Contacts Array',
                'contacts.*.mobile.unique' => 'Mobile NUmber Has Been Already Taken!'
            ]);

        } else {
            $validate = Validator::make($request->all(), [
                'contacts.*' => 'required|array',
                'contacts.*.mobile' => 'required',
                'mobile' => 'unique:user_emergency_contacts,mobile,'.$id,
            ],
            [
                'contacts.*.mobile.required' => 'Mobile Field is Required with Contacts Array',
                'mobile.unique' => 'Mobile NUmber Has Been Already Taken!'
            ]);
        }
        return self::returnValidation($validate);
    }

    public static function searchSentDriverValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'distance' => 'required',
            'user_pickup_latitude' => 'required',
            'user_pickup_longitude' => 'required',
            'user_drop_latitude' => 'required',
            'user_drop_longitude' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            'status' => 'required',
            // 'avg_speed' => 'required',
            'ride_duration' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function driverArriveValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function cancelRideValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'status' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            'pickup_latitude' => 'required',
            'pickup_longitude' => 'required',
            'drop_latitude' => 'required',
            'drop_longitude' => 'required',
            'distance' => 'required',
            'ride_type' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function completedRideValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function addRideValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            'pickup_latitude' => 'required',
            'pickup_longitude' => 'required',
            'drop_latitude' => 'required',
            'drop_longitude' => 'required',
            'distance' => 'required',
            'ride_type' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function preBookingValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'user_pickup_latitude' => 'required',
            'user_pickup_longitude' => 'required',
            'user_drop_latitude' => 'required',
            'user_drop_longitude' => 'required',
            'distance' => 'required',
            'status' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            // 'avg_speed' => 'required',
            'ride_duration' => 'required',
            'prebooking_time' => 'required',
            'ride_type' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function searchRideStatusValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'search_ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function rideReviewValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'user_review' => 'required',
            'user_rating' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function cancelSearchRideValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'search_ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function rideDetailValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function offlineSecondSearchAuto($request)
    {
        $validate = Validator::make($request->all(),[
            'distance' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function scratchGiftValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'user_reward_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function requestRewardValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'upi_id' => 'required',
            'amount' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function secondSentDriverValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'distance' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function addRideCancelContinueValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'status' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            'pickup_latitude' => 'required',
            'pickup_longitude' => 'required',
            'drop_latitude' => 'required',
            'drop_longitude' => 'required',
            'distance' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function paymentTypeValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'ride_id' => 'required',
            'payment_type' => 'required',
        ]);
        return self::returnValidation($validate);
    }

}

