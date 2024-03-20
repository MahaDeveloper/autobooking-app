<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use HasFactory,HasApiTokens;

    protected $hidden = ['created_at','updated_at','password','remember_token'];

    public function setPasswordAttribute($value)
    {
    	$this->attributes['password'] = Hash::make($value);
    }

    public function role(){

    	return $this->belongsTo(Role::class);
    }

    public function scopeActive($query){

        return $query->where('status',1);
    }
    
}
