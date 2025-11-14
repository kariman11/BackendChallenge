<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function getJWTIdentifier()   { return $this->getKey(); }

    public function organizations()
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_user_roles'
        )
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function hasPermission($orgId, $permissionName)
    {
        return Organization::findOrFail($orgId)
            ->userHasPermission($this->id, $permissionName);
    }

    public function organizationUserRoles()
    {
        return $this->hasMany(OrganizationUserRole::class);
    }


    public function rolesForOrg($orgId)
    {
        return $this->organizationUserRoles()->where('organization_id',$orgId)->with('role')->get()->pluck('role')->unique('id');
    }

    public function rolesInOrg($orgId)
    {
        return $this->organizationUserRoles()
            ->where('organization_id', $orgId)
            ->with('role.permissions');
    }


    public function getJWTCustomClaims() { return []; }
}
