<?php

use Faker\Generator as Faker;

$factory->define(App\NamedUser::class, function (Faker $faker) {
    return [
        'email' => $faker->unique()->safeEmail,
    ];
});
