<?php

namespace App\Traits;

trait MaxDrivers
{
    use Planable;

    public function notReachedMaxDrivers()
    {
        if ($this->planOptions() == false) {
            return response()->json([
                'errors' => [
                    'You do not have an active subscription.'
                ]
            ], 400);
        }

        $max_drivers = $this->planOptions()[0]->value;

        $drivers = auth()->user()->company->users()->whereHas('roles', function ($q) {
            $q->whereName('driver');
        })->count();

        if ($drivers >= $max_drivers) {
            return response()->json([
                'errors' => [
                    "Your current subscription is limited to {$max_drivers} drivers. Consider upgrading."
                ]
            ], 400);
        }

        return false;
    }
}
