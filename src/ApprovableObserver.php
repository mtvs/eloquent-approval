<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Database\Eloquent\Model;

class ApprovableObserver
{
    public function creating(Model $model)
    {
        $this->initializeApprovalStatus($model);
    }

    public function updating(Model $model)
    {
        $this->resetApprovalStatus($model);
    }

    protected function initializeApprovalStatus(Model $model)
    {
        if ($model->isDirty($model->getApprovalStatusColumn())) {
            return;
        }

        $this->suspend($model);
    }

    protected function resetApprovalStatus(Model $model)
    {
        $modifiedAttributes = array_keys(
            $model->getDirty()
        );

        foreach ($modifiedAttributes as $name) {
            if ($model->isApprovalRequired($name)) {
                $this->suspend($model);

                return;
            }
        }
    }

    protected function suspend(Model $model)
    {
        $model->setAttribute(
            $model->getApprovalStatusColumn(),
            ApprovalStatuses::PENDING
        );

        $model->setAttribute(
            $model->getApprovalAtColumn(),
            null
        );
    }
}