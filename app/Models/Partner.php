<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id1',
        'user_id2',
    ];

    // User 1
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_id1');
    }

    // User 2
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_id2');
    }

    // Savings for this partner pair
    public function savings()
    {
        return $this->hasMany(Saving::class);
    }

    // Goals for this partner pair
    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
}
