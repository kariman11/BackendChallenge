<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }



    public function userHasPermission($userId, $permissionName)
    {
        return $this->users()
            ->where('users.id', $userId)
            ->whereHas('organizationUserRoles.role.permissions', function ($q) use ($permissionName) {
                $q->where('name', $permissionName);
            })
            ->exists();
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function organizationUserRoles()
    {
        return $this->hasMany(OrganizationUserRole::class);
    }


}

