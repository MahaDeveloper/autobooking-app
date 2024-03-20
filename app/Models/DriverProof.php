<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DriverProof extends Model
{
    use HasFactory;

    public function scopeActive($query){

        return $query->where('verified',1);
    }

    protected $appends = ['img_url'];

    public function getImgUrlAttribute(){

        if($this->attributes['image']){

            $test = Storage::disk('digitalocean')->url($this->attributes['image']);

            return $test;
        }
    }
}
