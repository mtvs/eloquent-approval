<?php

namespace Mtvs\EloquentApproval\Tests;

use Mtvs\EloquentApproval\ApprovalServiceProvider;
use Mtvs\EloquentApproval\ApprovalStatuses;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $approvalStatuses = [
        ApprovalStatuses::APPROVED,
        ApprovalStatuses::PENDING,
        ApprovalStatuses::REJECTED
    ];

    protected $approvalActions = ['approve', 'suspend', 'reject'];

    protected $approvalChecks = ['isApproved', 'isRejected', 'isPending'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->withoutExceptionHandling();
    }

    protected function getPackageProviders($app)
    {
        return [
            ApprovalServiceProvider::class,
        ];
    }
}