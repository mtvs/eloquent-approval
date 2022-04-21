<?php

namespace Mtvs\EloquentApproval\Tests;

use Mtvs\EloquentApproval\ApprovalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $approvalActions = ['approve', 'suspend', 'reject'];

    protected $approvalChecks = ['isApproved', 'isRejected', 'isPending'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            ApprovalServiceProvider::class,
        ];
    }
}