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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'App\Http\Controllers\Backend'],function(){

    Route::post('admin/login','AuthController@adminLogin');

    //excel exports
    Route::get('export','ReportController@export');

    //bulk download complete ride pdf
    Route::get('completed-rides-pdf','BookingController@downloadPdf');

    //export sample zone data
    Route::get('export-sample-zone','ZoneController@exportZoneSample');

    Route::group(['middleware' => ['auth:sanctum']], function () {

        //roles and permissions
	    Route::get('role/status/{id}','RoleController@status');
        Route::get('active-roles','RoleController@activeRoles');
		Route::resource('role','RoleController');

	    //admins
		Route::get('admin/status/{id}','AdminController@status');
		Route::resource('admin','AdminController');

        //zones
		Route::resource('zone','ZoneController');

        //subscriptions
		Route::get('subscription/status/{id}','SubscriptionController@status');
		Route::resource('subscription','SubscriptionController');

        //logout
        Route::get('admin-logout','AuthController@logout');

        //change password
        Route::post('admin-change-password','AuthController@adminChangePassword');

        //price/charge
        Route::resource('price','PriceController');
        Route::resource('peak-charge','PeakChargeController');
        Route::resource('other-charge','OtherChargeController');

        //setting
        Route::resource('setting','SettingController');

        //driver request/reject
        Route::get('driver-request','DriverController@requestList');
        Route::get('request-reject/view/{id}','DriverController@requestRejectView');

        //proof approve/reject
        Route::post('proof-approve','DriverController@proofApprove');

        //driver list,view
        Route::get('accepted-drivers','DriverController@acceptedDrivers');
        Route::get('driver-profile/{id}','DriverController@driverProfile');
        Route::get('driver-status/{driver_id}','DriverController@driverStatus');
        Route::get('driver-ride-history/{driver_id}','DriverController@driverRideHistory');
        Route::get('driver-ride-history-view/{ride_id}','DriverController@driverRideHistoryView');

        //user list,view
        Route::get('users-list','UserController@userList');
        Route::get('user-view/{user_id}','UserController@userView');
        Route::get('user-status/{user_id}','UserController@userStatus');
        Route::get('user-ride-history/{user_id}','UserController@userRideHistory');
        Route::get('user-ride-history-view/{ride_id}','UserController@rideHistoryView');

        //support list
        Route::get('supports-raised','SupportController@supportList');
        Route::get('supports-resolved','SupportController@resolvedSupportList');
        Route::post('support-reply','SupportController@replySupport');

        //offline booking
        Route::post('offline-prebooking','OfflineBookingController@offlineBooking');

        //first time auto search
        Route::post('offline-first-search','OfflineBookingController@offlineFirstSearchAuto');

        //2nd , 3rd time auto search
        Route::post('offline-second-search/{search_ride_id}','OfflineBookingController@offlineSecondSearchAuto');

        //user list
        Route::get('user-list','OfflineBookingController@userList');

        //ride status
        Route::post('search-ride-status','OfflineBookingController@searchRideStatus');

        //booking list
        Route::get('offline-booking-list','OfflineBookingController@offlineBookingList');

        //rewards
        Route::get('request-reward-list','RewardController@rewardRequestList');

        Route::post('request-reward-detail','RewardController@rewardRequestDetail');

        Route::post('reward-history','RewardController@rewardHistory');

        Route::post('reward-paid','RewardController@paidReward');

        //pay tax
        Route::get('pay-tax-list','PayTaxController@payTaxList');

        Route::get('paid-tax-history','PayTaxController@paidTaxHistory');

        //bookings
        Route::get('bookings','BookingController@bookings');

        Route::get('booking-view/{ride_id}','BookingController@bookingView');

        //pre bookings
        Route::get('pre-bookings','BookingController@preBookings');

        //review rating
        Route::get('user-reviews','UserController@userReviews');

        Route::get('driver-reviews','UserController@driverReviews');

        //cancel rides
        Route::get('cancel-ride','BookingController@cancelRides');

        Route::get('reject-ride-view/{search_ride_id}','BookingController@rejectRideView');

        Route::get('cancel-ride-view/{ride_id}','BookingController@cancelRideView');

        //referals
        Route::get('refferals','UserController@refferals');

        //dashboard
        Route::get('counts','DashboardController@dashboardCounts');

        Route::get('count-percentage','DashboardController@countPercentage');

        Route::get('revenue-graph','DashboardController@revenueGraph');

        Route::get('pay-tax-graph','DashboardController@payTaxGraph');

        //activity log
        Route::get('activity-log','DriverController@activityLog');

        Route::get('activity-log/{driver_id}','DriverController@activityLogView');

        //reports
        Route::get('report','ReportController@filterReport');

        //admin notifications
        Route::get('notification-read-status','NotificationController@readstatus');
        Route::get('notification-read-entry','NotificationController@readEntry');
        Route::get('current-notifications','NotificationController@currentNotificationAdmin');
        Route::get('all-notifications','NotificationController@allNotification');

        Route::resource('d-notification','DynamicNotificationController');

        //change subscription plan
        Route::post('change-subscription-end-date','SubscriptionController@changeSubscriptionEndDate');

        Route::post('subscription-payment-details','SubscriptionController@SubscriptionPaymentDetails');

        //completed rides
        Route::get('completed-rides','BookingController@completedRide');

        //ride fare detail
        Route::post('ride-fare-detail','OfflineBookingController@rideFareDetail');

        //cancel ride when searching
        Route::post('cancel-search-ride','OfflineBookingController@cancelSearchRide');

        //import zone
        Route::post('import-zone','ZoneController@importZone');
    });

});
