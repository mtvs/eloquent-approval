<?php

namespace Mtvs\EloquentApproval;

use Illuminate\Http\Request;

trait HandlesApproval
{
	public function performApproval($key, Request $request)
	{
		$model = $this->findOrFail($key);

		if ($request['approval_status'] == ApprovalStatuses::APPROVED) {
			$result = $model->approve();
		}
		elseif ($request['approval_status'] == ApprovalStatuses::PENDING) {
			$result = $model->suspend();
		}
		elseif ($request['approval_status'] == ApprovalStatuses::REJECTED) {
			$result = $model->reject();
		}
		else {
			abort(422, 'Invalid approval_status value');
		}

		if(! $result)
		{
			abort(403, 'The operation failed.');
		} 
	
		return [
			$model->getApprovalStatusColumn() => 
				$model->{$model->getApprovalStatusColumn()},

			$model->getApprovalAtColumn() => 
				$model->{$model->getApprovalAtColumn()}
		];
		
	}

	protected function findOrFail($key)
	{
		return $this->model()::anyApprovalStatus()->
			findOrFail($key);
	}

	/**
	 * Returns the model class 
	 * 
	 * @return string
	 */
	abstract protected function model();
}