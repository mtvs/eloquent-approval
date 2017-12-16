<?php

namespace Mtvs\EloquentApproval\Tests;

use Mtvs\EloquentApproval\ApprovalServiceProvider;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $approvalActions = ['approve', 'suspend', 'reject'];

    protected $approvalChecks = ['isApproved', 'isRejected', 'isPending'];

    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations('sqlite');

        $this->loadMigrationsFrom([
            '--realpath' => realpath(__DIR__.'/database/migrations')
        ]);

        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            ConsoleServiceProvider::class,
            ApprovalServiceProvider::class,
        ];
    }
}