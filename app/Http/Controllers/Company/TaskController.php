<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,manager')->except('index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->hasAnyRole('driver')) {
            return TaskResource::collection(withRelations($request->user()->tasks()->filter($request)->paginate(10)->appends($request->except(['page', 'token']))))->response()->setStatusCode(200);
        }

        $tasks = new Task;

        if($request->get('status') == 'completed') {
            $tasks = $tasks->completed();
        }

        if($request->get('status') == 'uncompleted') {
            $tasks = $tasks->uncompleted();
        }

        if ($request->user()->hasAnyRole('admin', 'manager')) {
            return TaskResource::collection(withRelations($tasks->orderBy('id', 'desc')->filter($request)->paginate(10)->appends($request->except(['page', 'token']))))->response()->setStatusCode(200);
        }

        return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $task = Task::create($request->except('details'));

        if ($request->get('details')) {
            $task->details()->createMany($request->get('details'));
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
        DB::transaction(function () use ($request, $task) {
            $task->update($request->except('details'));

            if ($request->get('details')) {
                foreach ($request->get('details') as $detail) {
                    $task->details()->find($detail['id'])->update($detail);
                }
            }
        });

        return response()->json(['message' => "Task #{$task->id} was updated successfully."]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['message' => "Task #{$task->id} was deleted successfully."]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if (!$request->get('q')) {
            return response()->json(['message' => 'No search term was entered.'], 200);
        }

        $tasks = Task::search($request->get('q'));

        if($request->get('status') == 'completed') {
            $tasks = $tasks->completed();
        }

        if($request->get('status') == 'uncompleted') {
            $tasks = $tasks->uncompleted();
        }

        return TaskResource::collection(withRelations($tasks->orderBy('id', 'desc')->paginate(10)->appends($request->except(['page', 'token']))))->response()->setStatusCode(200);
    }
}
