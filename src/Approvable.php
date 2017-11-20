<?php

namespace Mtvs\EloquentApproval;

trait Approvable
{
    public static function bootApprovable()
    {
        static::addGlobalScope(new ApprovalScope());
    }

    public function getApprovalStatusColumn()
    {
        return defined('static::APPROVAL_STATUS') ? static::APPROVAL_STATUS : 'approval_status';
    }

    public function getQualifiedApprovalStatusColumn()
    {
        return $this->getTable().'.'.$this->getApprovalStatusColumn();
    }
}