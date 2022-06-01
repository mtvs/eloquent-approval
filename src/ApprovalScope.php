<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovalScope implements Scope
{

    protected $extensions = [
        'AnyApprovalStatus',
        'OnlyPending',
        'OnlyRejected',
        'OnlyApproved',
        'Approve',
        'Reject',
        'Suspend'
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where(
            $model->getQualifiedApprovalStatusColumn(),
            ApprovalStatuses::APPROVED
        );
    }

    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{'add'.$extension}($builder);
        }
    }

    protected function addAnyApprovalStatus(Builder $builder)
    {
        $builder->macro('anyApprovalStatus', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    protected function addOnlyPending(Builder $builder)
    {
        $builder->macro('onlyPending', function (Builder $builder) {
            return $this->onlyWithStatus($builder, ApprovalStatuses::PENDING);
        });
    }

    protected function addOnlyRejected(Builder $builder)
    {
        $builder->macro('onlyRejected', function (Builder $builder) {
           return $this->onlyWithStatus($builder, ApprovalStatuses::REJECTED);
        });
    }

    protected function addOnlyApproved(Builder $builder)
    {
        $builder->macro('onlyApproved', function (Builder $builder) {
            return $this->onlyWithStatus($builder, ApprovalStatuses::APPROVED);
        });
    }

    protected function onlyWithStatus(Builder $builder, $status)
    {
        $model = $builder->getModel();

        $builder->anyApprovalStatus()->where(
            $model->getQualifiedApprovalStatusColumn(),
            $status
        );

        return $builder;
    }

    protected function addApprove(Builder $builder)
    {
        $builder->macro('approve', function (Builder $builder) {
            return $this->updateStatus($builder, ApprovalStatuses::APPROVED);
        });
    }

    protected function addReject(Builder $builder)
    {
        $builder->macro('reject', function (Builder $builder) {
            return $this->updateStatus($builder, ApprovalStatuses::REJECTED);
        });
    }

    protected function addSuspend(Builder $builder)
    {
        $builder->macro('suspend', function (Builder $builder) {
            return $this->updateStatus($builder, ApprovalStatuses::PENDING);
        });
    }

    protected function updateStatus(Builder $builder, $status)
    {
        $model = $builder->getModel();

        $builder->anyApprovalStatus();

        $model->timestamps = false;

        return $builder->update([
            $model->getApprovalStatusColumn() => $status,
            $model->getApprovalAtColumn() => $model->freshTimestampString()
        ]);
    }
}