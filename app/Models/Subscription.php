<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    public function scopeActive($query){

        return $query->where('status',1);
    }

    public function driverSubscription(){

    	return $this->belongsTo(DriverSubscription::class);
    }

    public function driver(){

    	return $this->belongsTo(Driver::class);
    }

    public function transaction(){

    	return $this->belongsTo(Transaction::class);
    }

}

