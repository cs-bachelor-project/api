<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\Multitenantable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, Filterable, Multitenantable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'company_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Determine if the user has any of the roles.
     */
    public function hasAnyRole(...$roles)
    {
        if (is_array($roles[0])) {
            $roles = $roles[0];
        }

        foreach ($roles as $role) {
            $has = in_array($role, auth()->payload()['roles']);

            if ($has == true) {
                return true;
            }
        }

        return false;
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
            'company_id' => $this->company_id,
            'roles' => $this->roles()->pluck('name'),
        ];
    }

    /**
     * Get the company of the user.
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * Get the roles of the user.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }

    /**
     * Get the tasks of the user.
     */
    public function tasks()
    {
        return $this->hasMany('App\Models\Task');
    }

    
    /**
     * Get the messages of the company.
     */
    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }
}
