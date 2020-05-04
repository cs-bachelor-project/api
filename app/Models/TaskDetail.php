<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country',
        'postal',
        'city',
        'street',
        'street_number',
        'phone',
        'action',
        'scheduled_at',
        'completed_at',
        'task_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the task of the task-detail.
     */
    public function task()
    {
        return $this->belongsTo('App\Models\Task');
    }
}
