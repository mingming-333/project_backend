<?php

namespace App;

use App\Notifications\PasswordResetNotification;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = "id";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'role', 'gender', 'email_verified_at', 'password', 'UserAvatarPath'
    ];

    public function stores()
    {
        return $this->hasMany('App\Store', 'SuperUserID');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->id,
            'user_name' => $this->name,
			'user_role' => $this->role,
			'user_avatar' => $this->UserAvatarPath,
            'store' => $this->stores->map(function ($item, $key) {
                return [
                    'store_id' => $item->StoreID,
                    'store_name' => $item->StoreName,
                ];
            })
        ];
    }
}
