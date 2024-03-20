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
Route::group(['namespace' => 'App\Http\Controllers\Frontend\User'],function()
{
    //Login / Otp Verify
    Route::post('request-otp','AuthController@requestOtp');
    Route::post('check-otp','AuthController@checkOtp');

    Route::get('languages','AuthController@languages');

    //register
    Route::post('register','AuthController@register');

    Route::group(['middleware' => ['auth:sanctum']], function ()
    {
        //profile update
        Route::post('profile-update','AuthController@profileUpdate');
        Route::get('profile-view','AuthController@profileView');

        //logout
        Route::post('logout','AuthController@logout');

        //account delete
        Route::post('account-delete','AuthController@deleteAccount');

        //emergency contact
        Route::resource('emergency-contact','UserEmergencyContactController');

        //support
        Route::post('support-store','UserController@storeSupport');

        //Ride
        Route::post('check-area','RideController@ServiceableAreaCheck');

        //ride-now/later
        Route::post('book-ride','RideController@bookRide');

        //searching driver
        Route::post('user-nearby-auto','RideController@userNearbyAutos');

        Route::post('search-driver/first','RideController@searchFirstSentDriver');

        Route::post('search-driver/second/{search_ride_id}','RideController@searchSecondSentDriver');

        //driver arrive
        Route::post('driver-arriving','RideController@driverArrive');

        //cancel ride
        Route::post('cancel-ride','RideController@cancelRide');

        //ride complete
        Route::post('complete-ride','RideController@completedRide');

        //ride prebooking
        Route::post('pre-booking','RideController@preBooking');

        //add another ride
        Route::post('add-ride','RideController@addRide');

        Route::post('add-ride-confirm','RideController@addRideConfirm');

        //ride status
        Route::post('search-ride-status','RideController@SearchRideStatus');

        //revire/rating submit
        Route::post('user-review','RideController@userReviewStore');

        //emergency trigger
        Route::get('emergency-trigger','UserController@emergencyTrigger');

        //prebooking schedule
        Route::post('prebooking-schedule','RideController@startPrebookingRide');

        //search cancel
        Route::post('cancel-search-ride','RideController@cancelSearchRide');

        //ride all detials
        Route::post('ride-detail','RideController@rideDetail');

        //user rewards
        Route::get('user-reward','RewardController@userGifts');

        Route::post('scratch-reward','RewardController@scratchGift');

        Route::post('request-reward','RewardController@requestReward');

        //user ride

        Route::get('user-ride-list','UserController@userRideList');

        Route::get('prebooking-ride-list','UserController@prebookingRideList');

        Route::get('ride-show/{ride_id}','UserController@rideShow');

        //track accepted driver

        Route::get('driver-current-location/{driver_id}','RideController@trackDriverLocation');

        //notifications
        Route::get('user-notification','UserController@notificationList');

        Route::get('notification-viewed','UserController@notificationReadStatus');

        //add trip cancel/continue
        Route::post('add-ride-continue/cancel','RideController@addRideCancelContinue');

        //payment
        Route::post('payment-type','RideController@paymentType');

        //user current ride
        Route::get('current-ride','RideController@currrentRide');

    });

});
