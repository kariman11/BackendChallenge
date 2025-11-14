<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginDaily extends Model
{
    protected $table = 'login_daily';
    protected $fillable = ['user_id','organization_id','date','count'];
    protected $casts = ['date' => 'date'];
}
