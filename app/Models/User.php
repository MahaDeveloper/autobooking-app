<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['img_url'];

    public function getImgUrlAttribute(){

        if($this->attributes['image']){

            $test = Storage::disk('digitalocean')->url($this->attributes['image']);

            return $test;
        }
    }

    public function userAddresses(){

    	return $this->hasMany(UserAddress::class);
    }

    public function userEmergencyContacts(){

    	return $this->hasMany(UserEmergencyContact::class);
    }

    public function supports(): MorphTo
    {
        return $this->MorphTo(Support::class, 'supportable');
    }

    public function scopeActive($query){

        return $query->where('status',1);
    }

    public function refferals(){

    	return $this->hasMany(User::class,'refferal_id');
    }

    public function refferer(){

    	return $this->belongsTo(User::class,'refferal_id');
    }

    public function userRewards(){

    	return $this->hasMany(UserReward::class);
    }

    public function userRefferal()
    {
        return $this->belongsTo(User::class,'refferal_id');
    }
}
