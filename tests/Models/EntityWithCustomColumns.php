<?php

namespace Mtvs\EloquentApproval\Tests\Models;

use Mtvs\EloquentApproval\Approvable;

class EntityWithCustomColumns
{
    use Approvable;

    const APPROVAL_STATUS = 'custom_approval_status';
    const APPROVAL_AT = 'custom_approval_at';
}