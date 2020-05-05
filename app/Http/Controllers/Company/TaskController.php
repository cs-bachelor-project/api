<?php

namespace App\Http\Controllers\Company;

use App\Events\TaskAssigned;
use App\Events\TaskUnassigned;
use App\Events\TaskUpdated;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $rules = [
        'person_name' => 'required|max:255',
        'details.*.postal' => 'required|size:4',
        'details.*.city' => 'required|max:255',
        'details.*.street' => 'required|max:255',
        'details.*.street_number' => 'required|max:255',
        'details.*.action' => 'required|max:4',
        'details.*.scheduled_at' => 'required|date',
    ];

    protected $customMessages = [
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

    public function __construct()
    {
        $this->middleware('role:admin,manager');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tasks = new Task;

        if ($request->get('status') == 'completed') {
            $tasks = $tasks->completed();
        }

        if ($request->get('status') == 'uncompleted') {
            $tasks = $tasks->uncompleted();
        }

        if ($request->get('status') == 'cancelled') {
            $tasks = $tasks->has('cancellation');
        }

        return TaskResource::collection(withRelations($tasks->orderBy('id', 'desc')->filter($request)->paginate(10)->appends($request->except(['page', 'token']))))->response()->setStatusCode(200);
    }

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

        if ($request->get('user_id') != null) {
            event(new TaskAssigned($task));
        }

        return response()->json(['message' => "The task was created successfully."]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return response(new TaskResource(withRelations($task)), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), $this->rules, $this->customMessages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        $previousUserId = $task->user_id;

        DB::transaction(function () use ($request, $task) {
            $task->update($request->except('details'));

            if ($request->get('details')) {
                foreach ($request->get('details') as $detail) {
                    $task->details()->find($detail['id'])->update($detail);
                }
            }
        });

        if ($task->cancellation == null) {
            if ($previousUserId != null && $previousUserId != $request->get('user_id')) {
                event(new TaskUnassigned($task, $previousUserId));
            }

            if ($request->get('user_id') != null && $request->get('user_id') == $previousUserId) {
                event(new TaskUpdated($task));
            }

            if ($request->get('user_id') != null && $request->get('user_id') != $previousUserId) {
                event(new TaskAssigned($task));
            }
        }

        return response()->json(['message' => "The task was updated successfully."]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        if ($task->user_id != null && $task->cancellation == null) {
            event(new TaskUnassigned($task, $task->user_id));
        }

        $task->delete();

        return response()->json(['message' => "The task was deleted successfully."]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if (!$request->get('q')) {
            return response()->json(['message' => 'No search term was entered.'], 422);
        }

        $tasks = Task::search($request->get('q'));

        if ($request->get('status') == 'completed') {
            $tasks = $tasks->completed();
        }

        if ($request->get('status') == 'uncompleted') {
            $tasks = $tasks->uncompleted();
        }

        return TaskResource::collection(withRelations($tasks->orderBy('id', 'desc')->paginate(10)->appends($request->except(['page', 'token']))))->response()->setStatusCode(200);
    }
}
