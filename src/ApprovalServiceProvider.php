<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class ApprovalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ApprovableObserver::class, function () {
           return new ApprovableObserver();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                UiCommand::class,
            ]);
        }
    }

    public function boot()
    {
        Blueprint::mixin(new ApprovalSchemaMethods);
    }
}