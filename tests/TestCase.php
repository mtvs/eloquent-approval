<?php

namespace Mtvs\EloquentApproval\Tests;

use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
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
            ConsoleServiceProvider::class
        ];
    }
}