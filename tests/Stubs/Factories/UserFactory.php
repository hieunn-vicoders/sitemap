<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User;

$factory->define(User::class, function (Faker $faker) {
    $email = $faker->email;
    return [
        'email'        => $email,
        'password'     => $faker->password,
        'username'     => $faker->userName,
        'verify_token' => Hash::make($email),
    ];
});
