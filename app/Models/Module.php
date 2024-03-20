<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $hidden = ['created_at','updated_at','status'];

    public function permissions(){

    	return $this->hasMany(Permission::class);
    }

    public function scopeActive($query){

            return $query->where('status',1);
    }
}
