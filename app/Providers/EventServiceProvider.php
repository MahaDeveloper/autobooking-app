<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\UserAddressSave;
use App\Events\DriverDetailSave;
use App\Events\DriverProofSave;
use App\Events\TransactionSave;
use App\Events\DriverSubscriptionSave;
use App\Events\SearchRideStore;
use App\Events\DriverLogStore;
use App\Events\RideDetailSave;
use App\Events\RideSave;
use App\Events\RideBillingDetailSave;
use App\Events\DriverSearchEvent;
use App\Events\DriverPaymentStore;
use App\Events\DriverUserNotificationEvent;
use App\Listeners\SaveDriverDetail;
use App\Listeners\SaveUserAddress;
use App\Listeners\SaveDriverProof;
use App\Listeners\SaveTransaction;
use App\Listeners\SaveDriverSubscription;
use App\Listeners\SaveSearchRide;
use App\Listeners\StoreDriverLog;
use App\Events\WebhookFailure;
use App\Events\WebhookSignature;
use App\Events\WebhookSuccess;
use App\Events\DriverPayTaxSave;
use App\Events\GiftReward;
use App\Events\SendSmsEvent;
use App\Events\PushNotificationEvent;
use App\Listeners\VerifySignature;
use App\Listeners\WebhookFailureResponse;
use App\Listeners\WebhookSuccessResponse;
use App\Listeners\PayTaxStatus;
use App\Listeners\SaveRideDetail;
use App\Listeners\SaveRide;
use App\Listeners\SaveRideBillingDetail;
use App\Listeners\SendSearchDriver;
use App\Listeners\StoreDriverPayment;
use App\Listeners\UserPresentGift;
use App\Listeners\SmsNotificationSend;
use App\Listeners\SendNotification;
use App\Listeners\SendDriverUserNotification;
use App\Listeners\UserDriverNotificationSave;

use App\Events\AdminNotificationSend;
use App\Listeners\SaveAdminNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserAddressSave::class => [
            SaveUserAddress::class,
        ],
        DriverDetailSave::class => [
            SaveDriverDetail::class,
        ],
        DriverProofSave::class => [
            SaveDriverProof::class,
        ],
        TransactionSave::class => [
            SaveTransaction::class,
        ],
        DriverSubscriptionSave::class => [
            SaveDriverSubscription::class,
        ],
        SearchRideStore::class => [
            SaveSearchRide::class,
        ],
        WebhookSignature::class => [
            VerifySignature::class,
        ],
        WebhookFailure::class => [
            WebhookFailureResponse::class,
        ],
        WebhookSuccess::class => [
            WebhookSuccessResponse::class,
        ],
        DriverPayTaxSave::class => [
            PayTaxStatus::class,
        ],
        DriverLogStore::class => [
            StoreDriverLog::class,
        ],
        RideSave::class => [
            SaveRide::class,
        ],
        RideDetailSave::class => [
            SaveRideDetail::class,
        ],
        RideBillingDetailSave::class => [
            SaveRideBillingDetail::class,
        ],
        DriverSearchEvent::class => [
            SendSearchDriver::class,
        ],
        DriverPaymentStore::class => [
            StoreDriverPayment::class,
        ],
        GiftReward::class => [
            UserPresentGift::class,
        ],
        SendSmsEvent::class => [
            SmsNotificationSend::class,
        ],
        PushNotificationEvent::class => [
            SendNotification::class,
        ],
        DriverUserNotificationEvent::class => [
            SendDriverUserNotification::class,
            UserDriverNotificationSave::class,
        ],
        AdminNotificationSend::class => [
            SaveAdminNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
