<?php

namespace App\Http\Controllers\Driver;

use App\Events\TaskCancelled;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:driver');
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
