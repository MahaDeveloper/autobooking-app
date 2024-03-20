<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverSubscription extends Model
{
    use HasFactory;

    public function subscription(){

        return $this->belongsTo(Subscription::class);
    }

    public function driver(){

        return $this->belongsTo(Driver::class);
    }
}
