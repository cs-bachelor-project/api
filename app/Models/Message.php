<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use Multitenantable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text',
        'company_id',
    ];

    /**
     * Get the company of the message.
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * Get the user of the message.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
