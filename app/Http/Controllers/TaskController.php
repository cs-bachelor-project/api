<?php

namespace App\Http\Controllers;

use App\Events\NewTask;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $rules = [
        'company_id' => 'required|max:255',
        'person_name' => 'required|max:255',
        'details.*.postal' => 'required|size:4',
        'details.*.city' => 'required|max:255',
        'details.*.street' => 'required|max:255',
        'details.*.street_number' => 'required|max:255',
        'details.*.action' => 'required|max:4',
        'details.*.scheduled_at' => 'required|date',
    ];

    protected $customMessages = [
        'company_id.required' => 'You must select a company.',
        'person_name.required' => 'The passenger name is required.',
        'details.0.postal.required' => 'The pick up postal code is required.',
        'details.0.postal.size' => 'The pick up postal code must be of 4 digits.',
        'details.0.city.required' => 'The pick up city is required.',
        'details.0.street.required' => 'The pick up street is required.',
        'details.0.street_number.required' => 'The pick up street number is required.',
        'details.0.action.required' => 'The pick up action is required.',
        'details.0.scheduled_at.required' => 'The pick up time is required.',
        'details.0.scheduled_at.date' => 'The pick up date is not a valid date',
        'details.1.postal.required' => 'The drop off postal code is required.',
        'details.1.postal.size' => 'The drop off postal code must be of 4 digits.',
        'details.1.city.required' => 'The drop off city is required.',
        'details.1.street.required' => 'The drop off street is required.',
        'details.1.street_number.required' => 'The drop off street number is required.',
        'details.1.action.required' => 'The drop off action is required.',
        'details.1.scheduled_at.required' => 'The drop off time is required.',
        'details.1.scheduled_at.date' => 'The drop off date is not a valid date',
    ];

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules, $this->customMessages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        $task = Task::create($request->except('details'));

        if ($request->get('details')) {
            $task->details()->createMany($request->get('details'));
        }

        event((new NewTask($task)));

        return response()->json(['message' => 'Your booking has been received.']);
    }
}
