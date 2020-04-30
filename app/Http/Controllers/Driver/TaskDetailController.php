<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\TaskDetail;
use Illuminate\Http\Request;
use App\Http\Resources\TaskDetailResource;
use Carbon\Carbon;

class TaskDetailController extends Controller
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
            $q->where('user_id', auth()->id());
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

        return response()->json(['message' => 'Marked as completed']);
    }
}
