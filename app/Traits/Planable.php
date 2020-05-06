<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait Planable
{
    /**
     * Get the plan options of the company.
     */
    public function planOptions()
    {
        if (auth()->user()->company->subscription()) {
            $plan = auth()->user()->company->subscription()->stripe_plan;
            
            return DB::table('plan_options')->select('option', 'value')->where('stripe_plan', $plan)->get();
        }

        return false;
    }
}
