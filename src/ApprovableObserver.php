<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Database\Eloquent\Model;

class ApprovableObserver
{
    public function creating(Model $model)
    {
        $this->suspendIfApprovalStatusIsNotInitialized($model);
    }

    public function updating(Model $model)
    {
        $this->suspendIfHasApprovalRequiredModification($model);
    }

    protected function suspendIfApprovalStatusIsNotInitialized(Model $model)
    {
        if ($model->isDirty($model->getApprovalStatusColumn())) {
            return;
        }

        $this->suspend($model);
    }

    protected function suspendIfHasApprovalRequiredModification(Model $model)
    {
        foreach ($model->getDirty() as $modifiedAttribute) {
            if ($model->isApprovalRequired($modifiedAttribute)) {
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
    }
}