<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDriverNotification extends Model
{
    use HasFactory;

    public function notifiable()
    {
        return $this->morphTo();
    }
}
