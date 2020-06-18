<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Models\TaskCancellation;

class TaskStatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,manager');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cancellations = TaskCancellation::whereHas('task', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->get(['reason', 'created_at'])->groupBy('reason');

        $rs = [];
        foreach ($cancellations as $reason => $val) {
            if (!isset($rs[$reason])) {
                $rs[$reason] = [
                    'name' => $reason,
                    'data' => []
                ];
            }

            foreach ($val as $v) {
                $date = $v->created_at->format('Y-m-d');
                if (!isset($rs[$reason]['data'][$date])) {
                    $rs[$reason]['data'][$date] = 1;
                } else {
                    $rs[$reason]['data'][$date]++;
                }
            }
        }

        return response()->json(array_values($rs))->setStatusCode(200);
    }
}
