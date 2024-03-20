<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DriverDetail extends Model
{
    use HasFactory;

    protected $appends = ['img_url'];

    public function getImgUrlAttribute(){

        if($this->attributes['qr_code']){

            $test = Storage::disk('digitalocean')->url($this->attributes['qr_code']);

            return $test;
        }
    }
}
