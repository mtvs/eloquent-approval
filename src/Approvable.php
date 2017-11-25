<?php

namespace Mtvs\EloquentApproval;

use Exception;

trait Approvable
{
    public static function bootApprovable()
    {
        static::addGlobalScope(new ApprovalScope());

        static::observe(ApprovableObserver::class);
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

    public function approve()
    {
        return $this->updateModelStatus(ApprovalStatuses::APPROVED);
    }

    public function reject()
    {
        return $this->updateModelStatus(ApprovalStatuses::REJECTED);
    }

    public function suspend()
    {
        return $this->updateModelStatus(ApprovalStatuses::PENDING);
    }

    protected function updateModelStatus($status)
    {
        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        if (! $this->exists) {
            return;
        }

        $this->{$this->getApprovalStatusColumn()} = $status;

        $time = $this->freshTimestamp();

        $this->{$this->getApprovalAtColumn()} = $time;


        $columns = [
            $this->getApprovalStatusColumn() => $status,

            $this->getApprovalAtColumn() => $this->fromDateTime($time)
        ];

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $this->newQueryWithoutScopes()
            ->where($this->getKeyName(), $this->getKey())
            ->update($columns);

        return true;
    }
}