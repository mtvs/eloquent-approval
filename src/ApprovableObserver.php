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
}