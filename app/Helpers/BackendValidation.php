<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class BackendValidation
{

    public static function returnValidation($validate)
    {

        if ($validate->fails()) {

            return ['status' => 'error', 'message' => $validate->errors()->first()];

        } else {

            return ['status' => 'success'];

        }

    }

    public static function loginValidation($request)
    {

        $validate = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        return self::returnValidation($validate);

    }

    public static function adminPasswordValidation($request)
    {

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required',

        ]);

        return self::returnValidation($validator);

    }

    public static function adminValidation($request,$id)
    {
        if ($id == null) {

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'username' => 'required|unique:admins',
                'password' => 'required|min:8',
                'role_id' => 'required',
                'email' => 'required|email|unique:admins',
                'mobile' => 'required',
            ]);

        } else {

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'username' => 'required|unique:admins,username,' . $id,
                'email' => 'required|email|unique:admins,email,' . $id,
                'mobile' => 'required',
            ]);
        }

        return self::returnValidation($validate);
    }

    public static function roleValidation($request,$id)
    {
        if ($id == null) {

            $validate = Validator::make($request->all(), [
                'role' => 'required|unique:roles',
                'permissions' => 'required|array',
            ]);

        } else {

            $validate = Validator::make($request->all(), [
                'role' => 'required|unique:roles,role,' . $id,
                //'permissions' => 'required|array'

            ]);
        }
        return self::returnValidation($validate);
    }

    public static function zoneValidation($request,$id)
    {
        if ($id == null) {

            $validate = Validator::make($request->all(), [
                'city' => 'required',
                'zone' => 'required',
                'pin_code' => 'required|unique:zones',
            ]);

        } else {

            $validate = Validator::make($request->all(), [
                'city' => 'required',
                'zone' => 'required',
                'pin_code' => 'required|unique:zones,pin_code,'.$id,
            ]);
        }
        return self::returnValidation($validate);
    }

    public static function subscriptionValidation($request,$id)
    {
        if ($id == null) {

            $validate = Validator::make($request->all(), [
                'name' => 'required|unique:subscriptions',
                'validity' => 'required',
                'amount' => 'required',
            ]);

        } else {

            $validate = Validator::make($request->all(), [
                'name' => 'required|unique:subscriptions,name,' . $id,
                'validity' => 'required',
                'amount' => 'required',
            ]);

        }
        return self::returnValidation($validate);
    }

    public static function supportValidation($request)
    {

        $validate = Validator::make($request->all(), [
            'description' => 'required',
        ]);

        return self::returnValidation($validate);

    }

    public static function supportReplyValidation($request)
    {

        $validate = Validator::make($request->all(), [
            'reply' => 'required',
            'support_id' => 'required',
        ]);

        return self::returnValidation($validate);

    }

    public static function priceValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'from' => 'required',
            // 'to' => 'required',
            'amount' => 'required',
        ]);

        return self::returnValidation($validate);
    }

    public static function peakChargeValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'from_time' => 'required',
            'to_time' => 'required',
            'percentage' => 'required',
            'type' => 'required',
        ]);

        return self::returnValidation($validate);
    }

    public static function otherChargeValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'min_km_time' => 'required',
            'amount' => 'required',
            'type' => 'required',
        ]);

        return self::returnValidation($validate);
    }

    public static function settingValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'value' => 'required',
            'type' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function proofApproveValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'driver_id' => 'required',
            'verification_status' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function replySupportValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'support_id' => 'required',
            'reply_msg' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function firstSearchDriverValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'user_type' => 'required',
            'distance' => 'required',
            'user_pickup_latitude' => 'required',
            'user_pickup_longitude' => 'required',
            'user_drop_latitude' => 'required',
            'user_drop_longitude' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            'status' => 'required',
            'ride_type' => 'required',
            'user_id' => 'required_if:type,exist',
            'name' => 'required_if:type,new',
            'email' => 'required_if:type,new',
            'mobile' => 'required_if:type,new',
        ]);
        return self::returnValidation($validate);
    }

    public static function searchSecondSentDriverValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'distance' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function preBookingValidation($request)
    {
        $validate = Validator::make($request->all(),[
            'user_type' => 'required',
            'distance' => 'required',
            'user_pickup_latitude' => 'required',
            'user_pickup_longitude' => 'required',
            'user_drop_latitude' => 'required',
            'user_drop_longitude' => 'required',
            'pickup_address' => 'required',
            'drop_address' => 'required',
            'status' => 'required',
            'prebooking_time' => 'required',
            'user_id' => 'required_if:type,exist',
            'name' => 'required_if:type,new',
            'email' => 'required_if:type,new',
            'mobile' => 'required_if:type,new',
        ]);
        return self::returnValidation($validate);
    }

    public static function searchRideStatusValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'search_ride_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function rewardRequestDetailValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function rewardHistoryValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function sendNotification($request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'type' => 'required',
            'user_id' => 'required_if:type,3',
            'driver_id' => 'required_if:type,4',
        ]);
        return self::returnValidation($validate);
    }

    public static function changeSubscriptionEndDateValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'driver_id' => 'required',
            'subscription_end_date' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function SubscriptionPaymentDetailsValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'driver_id' => 'required',
            'subscription_id' => 'required',
        ]);
        return self::returnValidation($validate);
    }

    public static function importZoneValidation($request)
    {
        $validate = Validator::make($request->all(), [
            'import_excel' => 'required|mimes:xlsx,xls,csv',
        ]);
        return self::returnValidation($validate);
    }

}


