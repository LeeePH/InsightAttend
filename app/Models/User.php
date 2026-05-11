<?php

namespace App\Models; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;


    public function getRouteKeyName()
    {
        return 'name';
    }
    
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_users', 'user_id', 'role_id');
    }

    /**
     * Get the employee associated with the user by email
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'email', 'email');
    }

    public function routeNotificationForTwilioSms($notification = null)
    {
        return $this->employee?->phone;
    }

    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
        } else {
            if ($this->hasRole($roles)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole($role)
    {
        if (!$this->exists) {
            return false;
        }

        return $this->roles()->where('slug', $role)->exists();
    }


    protected $fillable = [
        'name', 'email', 'password', 'pin_code', 'managed_schedule_department',
    ];

  
    protected $hidden = [
        'pin_code','password', 'remember_token',
    ];

  
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
