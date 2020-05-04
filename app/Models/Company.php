<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cvr',
        'name',
        'country',
        'postal',
        'city',
        'street',
        'street_number',
    ];

    /**
     * Get the users of the company.
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    /**
     * Get the messages of the company.
     */
    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }
}
