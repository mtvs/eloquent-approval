<?php

use Faker\Generator as Faker;
use Mtvs\EloquentApproval\Tests\Models\Entity;
use Mtvs\EloquentApproval\Tests\Models\EntityWithNoApprovalRequiredAttributes;

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
$entityFactory = function (Faker $faker) {
    return [
        'attr_1' => $faker->word,
        'attr_2' => $faker->word,
        'attr_3' => $faker->word,
    ];
};

$factory->define(Entity::class, $entityFactory);
$factory->define(EntityWithNoApprovalRequiredAttributes::class, $entityFactory);