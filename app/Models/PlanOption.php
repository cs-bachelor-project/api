<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanOption extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stripe_plan',
        'option',
        'value',
    ];
}
