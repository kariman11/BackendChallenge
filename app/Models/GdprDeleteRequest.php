<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GdprDeleteRequest extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'reason',
        'approved_at',
        'rejected_at',
        'acted_by',
    ];

    protected $dates = [
        'approved_at',
        'rejected_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
