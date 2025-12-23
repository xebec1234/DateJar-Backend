<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Saving;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->id)) {
                do {
                    $id = random_int(1000000000, 9999999999); // 10-digit random number
                } while (self::where('id', $id)->exists());

                $user->id = $id;
            }
        });
    }
    
    public function savings()
    {
        return $this->hasMany(Saving::class);
    }

    // Partner connections where this user is user1
    public function partnerAsUserOne()
    {
        return $this->hasOne(Partner::class, 'user_id1');
    }

    // Partner connections where this user is user2
    public function partnerAsUserTwo()
    {
        return $this->hasOne(Partner::class, 'user_id2');
    }

    // Get the Partner record this user belongs to (either user1 or user2)
    public function partner()
    {
        return $this->partnerAsUserOne()->orWhere('user_id2', $this->id);
    }

}
