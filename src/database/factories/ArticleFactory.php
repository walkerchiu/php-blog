<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Blog\Models\Entities\Article;
use WalkerChiu\Blog\Models\Entities\ArticleLang;

$factory->define(Article::class, function (Faker $faker) {
    return [
        'identifier'     => $faker->slug,
        'title'          => $faker->title,
        'description'    => $faker->text,
        'content'        => $faker->text,
        'can_comment'    => $faker->boolean,
        'can_search'     => $faker->boolean,
        'is_highlighted' => $faker->boolean,
        'edit_at'        => date('Y-m-d H:i:s'),
    ];
});

$factory->define(ArticleLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['name', 'description']),
        'value' => $faker->sentence,
    ];
});
