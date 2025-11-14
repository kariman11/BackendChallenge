<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationUserRole extends Model
{
    protected $fillable = ['organization_id', 'user_id', 'role_id'];
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

}

