<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait Filterable
{
    public function scopeFilter($query, Request $request)
    {
        if (!empty($request->get("filter"))) {
            collect($request->get("filter"))->each(function ($values, $filter) use ($query) {
                if (strpos($values, ",") !== false) {
                    $exploeded = explode(",", $values);

                    foreach ($exploeded as $value) {
                        $query->orWhere($filter, $value);
                    }
                } else {
                    $query->where($filter, $values);
                }
            });
        }

        return $query;
    }
}
