<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Support\ServiceProvider;

class ApprovalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ApprovableObserver::class, function () {
           return new ApprovableObserver();
        });
    }
}