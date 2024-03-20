<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideDetail extends Model
{
    use HasFactory;

    public function ride(){

    	return $this->belongsTo(Ride::class);
    }
}
