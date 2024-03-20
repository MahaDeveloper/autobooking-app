<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchRide extends Model
{
    use HasFactory;

    public function user(){

    	return $this->belongsTo(User::class);
    }

    public function ride(){

    	return $this->belongsTo(Ride::class);
    }

}
