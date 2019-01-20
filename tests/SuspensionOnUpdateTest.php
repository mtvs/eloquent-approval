<?php

namespace Mtvs\EloquentApproval\Tests;

use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;
use Mtvs\EloquentApproval\Tests\Models\Entity;
use Mtvs\EloquentApproval\ApprovalStatuses;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * 
 */
class SuspensionOnUpdateTest extends TestCase
{
	use WithFaker;

	/**
	 * @test
	 */
	public function it_works_when_all_attributes_require_approval_on_update()
	{
		with($entity = new class (factory(Entity::class)->raw([
			'approval_status' => ApprovalStatuses::APPROVED,
		])) extends Entity {
			protected $table = 'entities';

			public function approvalRequired()
			{
				return ['*'];
			}

			public function approvalNotRequired()
			{
				return [];
			}
		})->save();

		$entity->update([
			'attr_1' => $this->faker->word
		]);

		$this->assertEquals(ApprovalStatuses::PENDING, $entity->approval_status);
		$this->assertDatabaseHas('entities', [
			'id' => $entity->id,
			'approval_status' => ApprovalStatuses::PENDING
		]);
	}

	/**
	 * @test
	 */
	public function it_works_when_some_attributes_do_not_require_approval_on_update()
	{
		// it isn't suspended on update of the attributes that don't require approval
		with($entity = new class (factory(Entity::class)->raw([
			'approval_status' => ApprovalStatuses::APPROVED,
		])) extends Entity {
			protected $table = 'entities';

			public function approvalRequired()
			{
				return ['*'];
			}

			public function approvalNotRequired()
			{
				return ['attr_1',];
			}
		})->save();

		$entity->update([
			'attr_1' => $this->faker->word,
		]);

		$this->assertEquals(ApprovalStatuses::APPROVED, $entity->approval_status);
		$this->assertDatabaseHas('entities', [
			'id' => $entity->id,
			'approval_status' => ApprovalStatuses::APPROVED
		]);

		// it is suspended on update of the attributes that require approval
		with($entity = new class (factory(Entity::class)->raw([
			'approval_status' => ApprovalStatuses::APPROVED,
		])) extends Entity {
			protected $table = 'entities';

			public function approvalRequired()
			{
				return ['*'];
			}

			public function approvalNotRequired()
			{
				return ['attr_1',];
			}
		})->save();

		$entity->update([
			'attr_2' => $this->faker->word,
		]);

		$this->assertEquals(ApprovalStatuses::PENDING, $entity->approval_status);
		$this->assertDatabaseHas('entities', [
			'id' => $entity->id,
			'approval_status' => ApprovalStatuses::PENDING
		]);
	}

	/**
	 * @test
	 */
	public function it_works_when_some_attributes_require_approval_on_update()
	{
		// it isn't suspended on update of the attributes that don't require approval
		with($entity = new class (factory(Entity::class)->raw([
			'approval_status' => ApprovalStatuses::APPROVED,
		])) extends Entity {
			protected $table = 'entities';

			public function approvalRequired()
			{
				return ['attr_1',];
			}

			public function approvalNotRequired()
			{
				return [];
			}
		})->save();

		$entity->update([
			'attr_2' => $this->faker->word,
			'attr_3' => $this->faker->word,
		]);

		$this->assertEquals(ApprovalStatuses::APPROVED, $entity->approval_status);
		$this->assertDatabaseHas('entities', [
			'id' => $entity->id,
			'approval_status' => ApprovalStatuses::APPROVED
		]);

		// it is suspended on update of the attributes that require approval
		with($entity = new class (factory(Entity::class)->raw([
			'approval_status' => ApprovalStatuses::APPROVED,
		])) extends Entity {
			protected $table = 'entities';

			public function approvalRequired()
			{
				return ['attr_1',];
			}

			public function approvalNotRequired()
			{
				return [];
			}
		})->save();

		$entity->update([
			'attr_1' => $this->faker->word,
		]);

		$this->assertEquals(ApprovalStatuses::PENDING, $entity->approval_status);
		$this->assertDatabaseHas('entities', [
			'id' => $entity->id,
			'approval_status' => ApprovalStatuses::PENDING
		]);
	}

	/**
	 * @test
	 */
	public function it_works_when_no_attribute_requires_approval_on_update()
	{
		with($entity = new class (factory(Entity::class)->raw([
			'approval_status' => ApprovalStatuses::APPROVED,
		])) extends Entity {
			protected $table = 'entities';

			public function approvalRequired()
			{
				return [];
			}

			public function approvalNotRequired()
			{
				return [];
			}
		})->save();

		$entity->update([
			'attr_1' => $this->faker->word,
			'attr_2' => $this->faker->word,
			'attr_3' => $this->faker->word,
		]);

		$this->assertEquals(ApprovalStatuses::APPROVED, $entity->approval_status);
		$this->assertDatabaseHas('entities', [
			'id' => $entity->id,
			'approval_status' => ApprovalStatuses::APPROVED
		]);
	}
}