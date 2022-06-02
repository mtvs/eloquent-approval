<?php

namespace Mtvs\EloquentApproval;

trait ApprovalFactoryStates
{
	public function approved()
	{
		return $this->state(function ()
		{
			return $this->approvalState(ApprovalStatuses::APPROVED);
		});
	}

	public function suspended()
	{
		return $this->state(function ()
		{
			return $this->approvalState(ApprovalStatuses::PENDING);
		});
	}

	public function rejected()
	{
		return $this->state(function ()
		{
			return $this->approvalState(ApprovalStatuses::REJECTED);
		});
	}	

	protected function approvalState($status)
	{
		$model = new ($this->modelName());

		return [
			$model->getApprovalStatusColumn() => $status,
			$model->getApprovalAtColumn() => now(),
		];
	}
}
