<?php

namespace App\Http\Controllers\Company;

use App\Events\Messaged;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,manager');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        auth()->user()->messages()->create(['text' => $request->get('msg')]);

        event(new Messaged($request->get('msg')));

        return response('Message was saved successfully.', 201);
    }
}
