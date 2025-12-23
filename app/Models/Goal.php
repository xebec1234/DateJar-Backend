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
        'target_date',
        'weekly_amount_per_user',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
