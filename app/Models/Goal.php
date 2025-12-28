<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'total_goal',
        'individual_goal', // per user
        'weekly_goal', // per user per week
        'target_date',
    ];

    // Goal belongs to a Partner
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    // Goal has many savings (from both users)
    public function savings()
    {
        return $this->hasMany(Saving::class);
    }
}
