<?php

namespace Mtvs\EloquentApproval\Tests\Models;

use Mtvs\EloquentApproval\Approvable;

class EntityWithCustomApprovalStatusColumn
{
    use Approvable;

    const APPROVAL_STATUS = 'custom_approval_status';
}