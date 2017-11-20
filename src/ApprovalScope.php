<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovalScope implements Scope
{

    protected $extensions = [
        'AnyApprovalStatus'
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
}