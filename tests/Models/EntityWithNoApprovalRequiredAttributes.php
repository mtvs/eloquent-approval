<?php

namespace Mtvs\EloquentApproval\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;

class EntityWithNoApprovalRequiredAttributes extends Model
{
    protected $table = 'entities';

    protected $guarded = [];

    use Approvable;


    public function approvalRequired()
    {
        return [];
    }
}