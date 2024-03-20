<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    public function rideDetail(){

    	return $this->hasOne(RideDetail::class);
    }

    public function rideBillingDetails(){

    	return $this->hasMany(RideBillingDetail::class);
    }

    public function user(){

    	return $this->belongsTo(User::class);
    }

    public function driver(){

    	return $this->belongsTo(Driver::class);
    }

    public function rideReview(){

    	return $this->hasMany(RideReview::class,'ride_id');
    }
}
