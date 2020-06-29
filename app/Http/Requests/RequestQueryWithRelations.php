<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

class RequestQueryWithRelations
{
    public function attach($resource, Request $request = null)
    {
        $request = $request ?? app('request');
        return tap($resource, function ($resource) use ($request) {
            $this->getRequestIncludes($request)->each(function ($include) use ($resource) {
                if (trim($include) !== '') {
                    $resource->load($include);
                }
            });
        });
    }

    protected function getRequestIncludes(Request $request)
    {
        $param = $request->get("include");

        $exploded = empty($param) || trim($param) === '' ? '' : explode(',', $param);

        return collect($exploded);
    }
}
