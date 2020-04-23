<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Multitenantable
{
    public static function bootMultitenantable()
    {
        if (auth()->check()) {
            static::creating(function ($model) {
                $model->company_id = auth()->user()->company_id;
            });

            static::addGlobalScope('tenancy', function (Builder $builder) {
                return $builder->where('company_id', auth()->user()->company_id);
            });
        }
    }
}
