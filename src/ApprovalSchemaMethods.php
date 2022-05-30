<?php

namespace Mtvs\EloquentApproval;

class ApprovalSchemaMethods
{
	public function approvals()
	{
		return function ($options = []) {
			$this->enum($options['status_name'] ?? 'approval_status', [
				'pending', 'approved', 'rejected'
			]);

			$this->timestamp($options['timestamp_name'] ?? 'approval_at')
				->nullable();
		};
	}
}