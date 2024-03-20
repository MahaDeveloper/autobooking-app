<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Notification extends Model
{
    use HasFactory;

    protected $appends = ['img_url'];

    public function getImgUrlAttribute(){

        if($this->attributes['image']){

            $test = Storage::disk('digitalocean')->url($this->attributes['image']);

            return $test;
        }
    }
}
