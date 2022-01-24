<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Blog\Models\Entities\Tag;

$factory->define(Tag::class, function (Faker $faker) {
    return [
        'id'         => $faker->uuid(),
        'identifier' => $faker->slug,
    ];
});
