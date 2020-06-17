<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\PlanOption;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(PlanOption::class, function () {
    return [
        'stripe_plan' => 'id',
        'option' => 'max-drivers',
        'value' => '5',
    ];
});
