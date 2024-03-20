<?php

namespace App\Services;

use App\Interfaces\UserRideInterface;
use App\Models\Zone;
use App\Models\OtherCharge;
use App\Models\Price;
use App\Models\PeakCharge;
use App\Models\Setting;
use App\Models\RideReview;
use Carbon\Carbon;
use Log;
use App\Helpers\MultipleLanguage;

class UserRideService implements UserRideInterface
{
    public function rideFare($request,$ride): array{

        $distance = $request->distance;
        log::info($distance);
        //base fare

        $base_fare = OtherCharge::where('type',1)->first();//min km

        $base_fare_amt = $base_fare->amount;

        $balance_distance = $distance - $base_fare->min_km_time;

        if($balance_distance < 0){ //negative values

            $balance_distance = 0;
        }

        //distance fare
        $distance_fares = Price::orderBy('from','asc')->get();
        $distance_charge = $base_fare_amt;
        log::info($distance_charge);
        log::info($distance_fares);
        foreach($distance_fares as $distance_fare){

            if(empty($distance_fare->to)){
                $distance_charge += $balance_distance * $distance_fare->amount;
                log::info("last");
                log::info($distance_charge);
                break;
            }
            elseif($distance > $distance_fare->to){
                $diff = $distance_fare->to - $distance_fare->from;
                $distance_charge += $diff * $distance_fare->amount;
                $balance_distance -= $diff;
                if($balance_distance <= 0)
                    $balance_distance = 0;
                log::info("greater than to");
                log::info($distance_charge);
            }
            elseif ($distance >= $distance_fare->from) {
                $diff = $distance - $distance_fare->from;
                if($diff <= 0)
                    $diff = 0;
                $distance_charge += $diff * $distance_fare->amount;
                $balance_distance -= $diff;
                if($balance_distance <= 0)
                    $balance_distance = 0;

                log::info("greater than from");
                log::info($distance_charge);
                break;
            }
        }
        //peak charge
        $now = Carbon::now();
        $peak_charge = PeakCharge::where(function ($query) use ($now) {
                $query->where('from_time','<=', $now->toTimeString())
                      ->where('to_time', '>=', $now->toTimeString());
            })->where('type',1)->first();

        log::info($peak_charge);
        $peak_hr_amount = 0;
        if($peak_charge) {

            $peak_hr_amount = ($peak_charge->percentage * $distance_charge) / 100;
        }

        //night charge

        $night_charge = PeakCharge::where(function ($query) use ($now) {
            $query->where('from_time', '<=',$now->toTimeString())
                  ->where('to_time','>=', $now->toTimeString());
        })->where('type',2)->first();
        log::info($night_charge);
        $night_hr_amt = 0;
        if($night_charge) {

            $night_hr_amt = ($night_charge->percentage * $distance_charge) / 100;
        }

        //estimated tax
        $tax = Setting::where('type',2)->first();//tax

        $tax_percentage = $tax->value;

        $e_tax = $tax_percentage * ($distance_charge + $peak_hr_amount + $night_hr_amt) /100;
        $estimated_tax = round($e_tax);
        //estimate ride amt

        $estimated_ride_fare = round(($distance_charge + $peak_hr_amount + $night_hr_amt) + $estimated_tax);

        //wait charge

        $min_waiting_charge = OtherCharge::where('type',2)->first();//min wait time

        if($ride){
            $ride_start_time = Carbon::parse($ride->ride_started_time);
            $reached_pickup_time = Carbon::parse($ride->reached_pickup_time);

            $waiting_mins = $reached_pickup_time->diffInMinutes($ride_start_time);

            if($min_waiting_charge->min_km_time <= $waiting_mins){

                $waiting_charge = $min_waiting_charge->amount;

                //final tax
                $f_tax = $tax_percentage * ($distance_charge + $peak_hr_amount + $night_hr_amt + $waiting_charge ?? 0 ) / 100;

                $final_tax = round($f_tax);

                //total ride fare
                $total_ride_fare = round(($distance_charge + $peak_hr_amount + $night_hr_amt + $waiting_charge ?? 0 ) + $final_tax ?? 0);
            }
        }

        log::info(now());
        log::info($estimated_tax);
        log::info($tax_percentage);
        log::info($estimated_ride_fare);
        log::info($night_hr_amt);
        log::info($peak_hr_amount);
        log::info($final_tax ?? 0);
        log::info($total_ride_fare ?? 0);

        $dist_charge = round($distance_charge - $base_fare_amt);

        return ['base_fare' => $base_fare_amt, 'distance_fare' => $dist_charge, 'peak_charge' => $peak_hr_amount ?? 0, 'night_charge' => $night_hr_amt ?? 0, 'tax_percentage' => $tax_percentage, 'estimated_tax' => $estimated_tax, 'estimated_ride_fare' => $estimated_ride_fare, 'waiting_charge' => $waiting_charge ?? 0, 'final_tax' => $final_tax ?? 0, 'total_ride_fare' => $total_ride_fare ?? 0];
    }

    public function rideReview($request,$ride_id): array{

        $ride_review = new RideReview();
        $ride_review->ride_id = $ride_id;
        $ride_review->user_review = $request->user_review;
        $ride_review->user_rating = $request->user_rating;
        $ride_review->driver_review = $request->driver_review;
        $ride_review->driver_rating = $request->driver_rating;

        if($request->user_review){

            $user_review = MultipleLanguage::allLanguages($request->user_review);
            $ride_review->languages_user_reviews = json_encode($user_review);

        }else{

            $driver_review = MultipleLanguage::allLanguages($request->driver_review);
            $ride_review->languages_driver_reviews = json_encode($driver_review);
        }

        $ride_review->save();

        return ['status' => 'success'];
    }

}
