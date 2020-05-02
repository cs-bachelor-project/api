<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Company;
use Faker\Generator as Faker;
use Faker\Provider\da_DK\Address;

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

$factory->define(Company::class, function (Faker $faker) {
    return [
        'cvr' => $faker->cvr,
        'name' => $faker->name,
        'country' => 'Denmark',
        'postal' => Address::postcode(),
        'city' => $faker->city,
        'street' => $faker->streetName,
        'street_number' => $faker->randomNumber(),
    ];
});
