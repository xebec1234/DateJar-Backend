<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'partner_id',
        'user_id',
        'daily_savings',
        'weekly_savings',
        'total_savings',
        'note',
    ];

    // Saving belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Saving belongs to a Partner
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    // Saving belongs to a Goal
    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }
}
