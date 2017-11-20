<?php

namespace Mtvs\EloquentApproval\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;

class Entity extends Model
{
    use Approvable;
}