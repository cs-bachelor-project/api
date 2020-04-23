<?php

namespace App\Models;

use App\Traits\Searchable;
use App\Traits\Filterable;
use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use Filterable, Multitenantable, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'person_name',
        'note',
        'user_id',
        'company_id',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'person_name',
        'note',
    ];

    /**
     * Scope a query to only include completed tasks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->doesntHave('details', 'and', function ($q) {
            return $q->whereNull('completed_at');
        });
    }

    /**
     * Scope a query to only include uncompleted tasks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUncompleted($query)
    {
        return $query->whereHas('details', function ($q) {
            return $q->whereNull('completed_at');
        });
    }

    /**
     * Get the user that is assigned to the task.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the details of the task.
     */
    public function details()
    {
        return $this->hasMany('App\Models\TaskDetail');
    }
}
