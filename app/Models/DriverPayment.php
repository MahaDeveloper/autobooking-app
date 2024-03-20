<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPayment extends Model
{
    use HasFactory;

    public function driver(){

    	return $this->belongsTo(Driver::class);
    }

    public function transaction(){

    	return $this->belongsTo(Transaction::class);
    }
}
