<?php

namespace Mtvs\EloquentApproval\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mtvs\EloquentApproval\ApprovableFactoryStates;
use Mtvs\EloquentApproval\Tests\Models\Entity;

class EntityFactory extends Factory
{
    use ApprovableFactoryStates;

    protected $model = Entity::class;

    public function definition()
    {
        return [
            'attr_1' => $this->faker->word,
            'attr_2' => $this->faker->word,
            'attr_3' => $this->faker->word,
        ];
    }
}