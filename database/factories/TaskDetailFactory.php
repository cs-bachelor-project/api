<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TaskDetail;
use App\Models\Task;
use Faker\Generator as Faker;

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

$factory->define(TaskDetail::class, function (Faker $faker) {
    return [
        'country' => 'Denmark',
        'city' => $faker->city,
        'street' => $faker->streetName,
        'street_number' => $faker->randomNumber(),
        'task' => $faker->randomElement(['pick', 'drop']),
        'scheduled_at' => $faker->dateTimeBetween('-5 months', $endDate = '+5 months'),
        'task_id' => factory(Task::class),
    ];
});
