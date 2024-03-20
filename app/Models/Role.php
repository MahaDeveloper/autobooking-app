<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $hidden = ['created_at','updated_at'];

    //the permissions that belongs to the role
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

      public function scopeActive($query){

        return $query->where('status',1);
    }
}
