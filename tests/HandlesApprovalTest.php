<?php

namespace Mtvs\EloquentApproval\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Testing\TestResponse;
use Mtvs\EloquentApproval\Tests\Models\Entity;
use Mtvs\EloquentApproval\HandlesApproval;

class HandlesApprovalTest extends Testcase
{
	use HandlesApproval;

	protected function model()
	{
		return Entity::class;
	}

	/** @test */
	public function it_can_performs_approvals()
	{
		$entity = Entity::factory()->create();

		$key = $entity->id;

		foreach($this->approvalStatuses as $approval_status) {
			$request = Request::create("/admin/enitiy/$key/approval", 'POST', [
				'approval_status' => $approval_status
			]);

			$response = $this->handleRequestUsing($request, function ($request) use($key) {
				return $this->performApproval($key, $request);
			});

			$this->assertDatabaseHas('entities', [
				'id' => $key,
				'approval_status' => $approval_status,
				'approval_at' => now()
			]);

			$response->assertStatus(200);
		}
	}

	protected function handleRequestUsing(Request $request, callable $callback)
	{
		try {
			$response = response(
				(new Pipeline($this->app))
					->send($request)
					->through([])
					->then($callback)
			);
		} catch (\Throwable $e) {
			$this->app[ExceptionHandler::class]
				->report($e);

			$response = $this->app[ExceptionHandler::class]
				->render($request, $e);
		}

		return new TestResponse($response);
	}
}