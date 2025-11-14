<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MagicLink extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'consumed_at',
    ];

    // IMPORTANT: ensures Carbon instances instead of strings
    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

