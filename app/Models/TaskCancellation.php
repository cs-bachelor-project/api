<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCancellation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reason',
        'task_id',
    ];

    /**
     * Get the task of the task-detail.
     */
    public function task()
    {
        return $this->belongsTo('App\Models\Task');
    }
}
