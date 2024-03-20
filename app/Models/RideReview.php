<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideReview extends Model
{
    use HasFactory;

    public function ride(){

    	return $this->belongsTo(RideReview::class,'ride_id');
    }

}
