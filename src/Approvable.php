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

    public function getApprovalAtColumn()
    {
        return defined('static::APPROVAL_AT') ? static::APPROVAL_AT : 'approval_at';
    }
}