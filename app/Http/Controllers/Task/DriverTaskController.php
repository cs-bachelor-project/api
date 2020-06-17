<?php

namespace App\Http\Controllers\Task;

use App\Events\TaskCancelled;
use App\Events\TaskDetailCompleted;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskDetailResource;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskDetail;
use Carbon\Carbon;

class DriverTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:driver');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $date = $request->get('date') ? Carbon::createFromFormat('Y-m-d', $request->get('date'))->toDateString() : Carbon::today()->toDateString();

        $tasks = TaskDetail::whereDate('scheduled_at', $date)->whereHas('task', function ($q) {
            $q->where('user_id', auth()->id())->doesnthave('cancellation');
        })->orderBy('scheduled_at', 'asc')->get();

        return TaskDetailResource::collection(withRelations($tasks))->response()->setStatusCode(200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request, TaskDetail $detail)
    {
        $date = new Carbon($request->get('completed_at'));

        $detail->update([
            'completed_at' => $date->toDateTimeString(),
        ]);

        event(new TaskDetailCompleted($detail));

        return response()->json(['message' => 'Marked as completed']);
    }

    /**
     * Add Task to cancellation
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request, Task $task)
    {
        $task->cancellation()->create([
            'reason' => $request->get('reason'),
        ]);

        event(new TaskCancelled($task, $task->company_id));

        return response()->json(['message' => 'Marked as cancelled']);
    }
}
