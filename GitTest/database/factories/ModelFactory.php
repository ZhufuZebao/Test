<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Enterprise;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'enterprise_id' => 3,
        'enterprise_admin' => 1,
    ];
});

$factory->define(Enterprise::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'id' => 3,
        'user_id' => 1,
        'plan' => 2,
    ];
});

// 日報テストデータ
$factory->define(App\Report::class, function (Faker\Generator $faker) {
    return [
        'log_date' => $faker->dateTimeBetween('-30 days', 'now'),
        'user_id' => $faker->randomNumber(9),
        'work_type' => $faker->randomLetter,
        'title' => $faker->text(mt_rand(10, 20)),
        'worker' => $faker->name,
        'location' => $faker->address,
        'note' => $faker->text(mt_rand(100, 200)),
        'goal' => $faker->text(mt_rand(100, 200)),
        'tips' => $faker->text(mt_rand(100, 200)),
    ];
});
