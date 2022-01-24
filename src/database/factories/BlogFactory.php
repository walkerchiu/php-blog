<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Blog\Models\Entities\Blog;
use WalkerChiu\Blog\Models\Entities\BlogLang;

$factory->define(Blog::class, function (Faker $faker) {
    return [
        'identifier'     => $faker->slug,
        'language'       => $faker->randomElement(config('wk-core.class.core.language')::getCodes()),
        'is_highlighted' => $faker->boolean,
    ];
});

$factory->define(BlogLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['name', 'description']),
        'value' => $faker->sentence,
    ];
});
