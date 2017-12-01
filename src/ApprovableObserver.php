<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Database\Eloquent\Model;

class ApprovableObserver
{
    public function creating(Model $model)
    {
        if ($model->isDirty($model->getApprovalStatusColumn())) {
            return;
        }

        $model->setAttribute(
            $model->getApprovalStatusColumn(),
            ApprovalStatuses::PENDING
        );
    }

    public function updating(Model $model)
    {
        $this->suspendIfHasApprovalRequiredModification($model);
    }

    public function suspendIfHasApprovalRequiredModification(Model $model)
    {
        foreach ($model->getDirty() as $modifiedAttribute) {
            if ($model->isApprovalRequired($modifiedAttribute)) {
                $model->setAttribute(
                    $model->getApprovalStatusColumn(),
                    ApprovalStatuses::PENDING
                );

                return;
            }
        }
    }
}