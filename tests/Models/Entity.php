<?php

namespace Mtvs\EloquentApproval\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mtvs\EloquentApproval\Approvable;
use Mtvs\EloquentApproval\Tests\Database\Factories\EntityFactory;

class Entity extends Model
{
    use Approvable, HasFactory;

    static protected function newFactory()
    {
        return new EntityFactory;
    }

    protected $guarded = [];
}
