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
        'Approve'
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

        $this->extend($builder);
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
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->where(
                $model->getQualifiedApprovalStatusColumn(),
                ApprovalStatuses::PENDING
            );

            return $builder;
        });
    }

    protected function addOnlyRejected(Builder $builder)
    {
        $builder->macro('onlyRejected', function (Builder $builder) {
           $model = $builder->getModel();

           $builder->withoutGlobalScope($this)->where(
               $model->getQualifiedApprovalStatusColumn(),
               ApprovalStatuses::REJECTED
           );

           return $builder;
        });
    }

    protected function addOnlyApproved(Builder $builder)
    {
        $builder->macro('onlyApproved', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->where(
                $model->getQualifiedApprovalStatusColumn(),
                ApprovalStatuses::APPROVED
            );

            return $builder;
        });
    }

    protected function addApprove(Builder $builder)
    {
        $builder->macro('approve', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->anyApprovalStatus();

            return $builder->update([
                $model->getApprovalStatusColumn() => ApprovalStatuses::APPROVED
            ]);
        });
    }
}