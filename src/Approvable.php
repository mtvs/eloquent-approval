<?php

namespace Mtvs\EloquentApproval;

use Exception;

trait Approvable
{
    use ApprovalRequired;

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

    /**
     * @return bool|void
     */
    public function approve()
    {
        return $this->updateModelStatus(ApprovalStatuses::APPROVED);
    }

    /**
     * @return bool|void
     */
    public function reject()
    {
        return $this->updateModelStatus(ApprovalStatuses::REJECTED);
    }

    /**
     * @return bool|void
     */
    public function suspend()
    {
        return $this->updateModelStatus(ApprovalStatuses::PENDING);
    }

    /**
     * @param $status
     * @return bool|void
     * @throws Exception
     */
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

    /**
     * @return bool|void
     */
    public function isPending()
    {
        return $this->hasApprovalStatus(ApprovalStatuses::PENDING);
    }

    /**
     * @return bool|void
     */
    public function isApproved()
    {
        return $this->hasApprovalStatus(ApprovalStatuses::APPROVED);
    }

    /**
     * @return bool|void
     */
    public function isRejected()
    {
        return $this->hasApprovalStatus(ApprovalStatuses::REJECTED);
    }

    /**
     * @param $status
     * @return bool|void
     */
    protected function hasApprovalStatus($status)
    {
        if (! $this->exists) {
            return;
        }

        return $this->{$this->getApprovalStatusColumn()} == $status;
    }
}