<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['namespace' => 'App\Http\Controllers\Frontend\Driver'],function()
{
    //Login / Otp Verify
    Route::post('request-otp','AuthController@requestOtp');
    Route::post('check-otp','AuthController@checkOtp');

    //register
    Route::post('register','AuthController@register');

    Route::group(['middleware' => ['auth:sanctum']], function ()
    {
        //profile update
        Route::post('profile-update','AuthController@profileUpdate');
        Route::get('profile-view','AuthController@profileView');

        //proof upload
        Route::post('proof-upload','AuthController@proofUpload');

        //logout
        Route::post('logout','AuthController@logout');

         //account delete
         Route::post('account-delete','AuthController@deleteAccount');

        //emergency contact
        Route::resource('emergency-contact','UserEmergencyContactController');

        //support
        Route::post('support-store','UserController@storeSupport');

        //subscription
        Route::get('subscription','SubscriptionController@subscriptionPlan');

        //pay-subscription
        Route::post('pay-subscription','SubscriptionController@subscriptionPayment');

        Route::post('transaction-status','SubscriptionController@transactionStatus');

        //turn Online
        Route::post('turn-online','RideController@turnOnline');

        //driver currect location tracking
        Route::post('current-location/update','RideController@updateCurrentLocation');

        //ride request
        Route::get('user-request','RideController@userRequest');

        //ride accept/reject
        Route::post('ride-accept/reject','RideController@rideAcceptReject');

        Route::post('raise-support','AuthController@storeSupport');

        //user pickup
        Route::post('user-pickup','RideController@userPickup');

        //driver reached pickup
        Route::post('reach-pickup-location','RideController@reachPickupLocation');

        //user otp verify
        Route::post('ride-otp-verify','RideController@rideOtpVerify');

        //complete ride
        Route::post('reached-drop','RideController@reachDropLocation');

        //QR pay
        Route::post('qr-pay','RideController@qrPay');
        Route::post('payment-confirm','RideController@qrPaymentConfirm');

        //add ride request
        Route::post('add-ride-request','RideController@addRideRequest');

        //add trip accept/cancel
        Route::post('add-trip-accept/reject','RideController@addRideAcceptReject');

        //revire/rating submit
        Route::post('driver-review','RideController@driverReviewStore');

        //payment tax
        Route::post('tax-payment','DriverPayTaxController@payTaxPayment');

        Route::get('pay-tax-list','DriverPayTaxController@payTaxList');

        //ride all detials
        Route::get('ride-detail/{ride_id}','RideController@rideDetail');

        Route::get('ride-history','DriverController@rideHistory');

        Route::get('ride-history-view/{ride_id}','DriverController@rideHistoryView');

        Route::get('home-page','DriverController@driverHomePage');

        //cancel ride
        Route::post('cancel-ride','RideController@cancelRide');

        //notifications
        Route::get('driver-notification','DriverController@notificationList');

        Route::get('notification-viewed','DriverController@notificationReadStatus');

    });

});
