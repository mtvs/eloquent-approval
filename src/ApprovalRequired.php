<?php

namespace Mtvs\EloquentApproval;


trait ApprovalRequired
{
    protected $approval_required = ['*'];

    protected $approval_not_required = [];

    /**
     * @param array $approval_required
     */
    public function setApprovalRequired($approval_required)
    {
        $this->approval_required = $approval_required;
    }

    /**
     * @param array $approval_not_required
     */
    public function setApprovalNotRequired($approval_not_required)
    {
        $this->approval_not_required = $approval_not_required;
    }

    /**
     * @return array
     */
    public function getApprovalRequired()
    {
        return $this->approval_required;
    }

    /**
     * @return array
     */
    public function getApprovalNotRequired()
    {
        return $this->approval_not_required;
    }

    /**
     * Determine if modification of the given attribute requires approval
     *
     * @param $key
     * @return bool
     */
    public function isApprovalRequired($key)
    {
        if ($this->isApprovalNotRequired($key)) {
            return false;
        }

        if (in_array($key, $this->getApprovalRequired())
            || $this->getApprovalRequired() == ['*']) {
            return true;
        }

        return ! empty($this->getApprovalNotRequired());
    }

    public function isApprovalNotRequired($key)
    {
        return in_array($key, $this->getApprovalNotRequired());
    }
}