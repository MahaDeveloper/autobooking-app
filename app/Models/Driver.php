<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\Driver as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class Driver extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['img_url'];

    public function getImgUrlAttribute(){

        if($this->attributes['image']){

            $test = Storage::disk('digitalocean')->url($this->attributes['image']);

            return $test;
        }
    }

    public function supports(): MorphTo
    {
        return $this->MorphTo(Support::class, 'supportable');
    }

    public function driverDetail(){

    	return $this->hasOne(DriverDetail::class);
    }

    public function driverProofs(){

    	return $this->hasMany(DriverProof::class);
    }

    public function rides(){

    	return $this->hasMany(Ride::class);
    }

    public function refferals(){

    	return $this->hasMany(Driver::class,'refferal_id');
    }

    public function refferer(){

    	return $this->belongsTo(Driver::class,'refferal_id');
    }

    public function scopeActive($query){

        return $query->where('status',1);
    }

    public function driverSubscription(){

        return $this->belongsTo(DriverSubscription::class);
    }

    public function driverPayments(){

        return $this->hasMany(DriverPayment::class);
    }

}
