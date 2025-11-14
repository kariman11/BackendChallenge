<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationInvitation extends Model
{
    protected $fillable = [
        'organization_id',
        'email',
        'role',
        'token',
        'accepted_at',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
