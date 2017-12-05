<?php

namespace Mtvs\EloquentApproval;


trait ApprovalRequired
{
    /**
     * @return array
     */
    public function approvalRequired()
    {
        return ['*'];
    }

    /**
     * @return array
     */
    public function approvalNotRequired()
    {
        return [];
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

        if (in_array($key, $this->approvalRequired())
            || $this->approvalRequired() == ['*']) {
            return true;
        }

        return ! empty($this->approvalNotRequired());
    }

    public function isApprovalNotRequired($key)
    {
        return in_array($key, $this->approvalNotRequired());
    }
}