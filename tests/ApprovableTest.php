<?php

namespace Mtvs\EloquentApproval\Tests;

use Illuminate\Support\Carbon;
use Mtvs\EloquentApproval\ApprovalStatuses;
use Mtvs\EloquentApproval\Tests\Models\Entity;
use Mtvs\EloquentApproval\Tests\Models\EntityWithCustomColumns;

class ApprovableTest extends TestCase
{
    /**
     * @test
     */
    public function its_approval_status_defaults_to_pending_on_creating()
    {
        $entity = Entity::factory()->create();

        $this->assertArrayHasKey('approval_status', $entity->getAttributes());

        $this->assertEquals(ApprovalStatuses::PENDING, $entity->approval_status);

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'approval_status' => ApprovalStatuses::PENDING
        ]);
    }

    /**
     * @test
     */
    public function its_approval_status_default_can_be_overridden()
    {
        $entity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);

        $this->assertEquals(ApprovalStatuses::APPROVED, $entity->approval_status);

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
    }

    /**
     * @test
     */
    public function it_has_default_for_approval_status_column()
    {
        $entity = new Entity();

        $this->assertEquals('approval_status', $entity->getApprovalStatusColumn());
    }

    /**
     * @test
     */
    public function it_can_detect_custom_approval_status_column()
    {
        $entity = new EntityWithCustomColumns();

        $this->assertEquals(
            EntityWithCustomColumns::APPROVAL_STATUS,
            $entity->getApprovalStatusColumn()
        );
    }

    /**
     * @test
     */
    public function it_has_default_for_approval_at_column()
    {
        $entity = new Entity();

        $this->assertEquals('approval_at', $entity->getApprovalAtColumn());
    }

    /**
     * @test
     */
    public function it_can_detect_custom_approval_at_column()
    {
        $entity = new EntityWithCustomColumns();

        $this->assertEquals(
            EntityWithCustomColumns::APPROVAL_AT,
            $entity->getApprovalAtColumn()
        );
    }

    /**
     * @test
     */
    public function it_can_approve_the_entity()
    {
        $entity = Entity::factory()->create();

        $entity->approve();

        $this->assertEquals(ApprovalStatuses::APPROVED, $entity->approval_status);

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
    }

    /**
     * @test
     */
    public function it_can_reject_the_entity()
    {
        $entity = Entity::factory()->create();

        $entity->reject();

        $this->assertEquals(ApprovalStatuses::REJECTED, $entity->approval_status);

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'approval_status' => ApprovalStatuses::REJECTED
        ]);
    }

    /**
     * @test
     */
    public function it_can_suspend_the_entity()
    {
        $entity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);

        $entity->suspend();

        $this->assertEquals(ApprovalStatuses::PENDING, $entity->approval_status);

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'approval_status' => ApprovalStatuses::PENDING
        ]);
    }

    /**
     * @test
     */
    public function it_refreshes_the_entity_approval_at_on_status_update()
    {
        $entity = Entity::factory()->create();

        foreach ($this->approvalActions as $action) {
            $time = (new Entity())->freshTimestamp();

            $entity->{$action}();

            $this->assertEquals($time->timestamp, $entity->approval_at->timestamp);

            $this->assertDatabaseHas('entities', [
                'id' => $entity->id,
                'approval_at' => $entity->fromDateTime($time)
            ]);

            $entity->newQuery()->where('id', $entity->id)->update([
                'approval_at' => $time->subHour()
            ]);
        }
    }

    /** @test */
    public function it_does_not_refresh_the_entity_updated_at()
    {
        $entity = Entity::factory()->create([
            'updated_at' => $time = (new Entity())->freshTimestamp()->subHour(1)
        ]);

        foreach ($this->approvalActions as $action) {
            $entity->$action();

            $this->assertEquals($time->timestamp, $entity->updated_at->timestamp);

            $this->assertDatabaseHas('entities', [
                'id' => $entity->id,
                'updated_at' => $entity->fromDateTime($time)
            ]);
        }
    }


    /**
     * @test
     */
    public function it_returns_true_when_updates_status()
    {
        $entity = Entity::factory()->create();

        foreach ($this->approvalActions as $action) {
            $this->assertTrue($entity->{$action}());
        }
    }

    /**
     * @test
     */
    public function it_refuses_to_update_status_when_not_exists()
    {
        $entity = Entity::factory()->make();

        foreach ($this->approvalActions as $action) {
            $this->assertNull($entity->{$action}());

            $this->assertNull($entity->approval_at);
        }
    }

    /**
     * @test
     **/
    public function it_rejects_the_duplicate_approvals()
    {
        $statuses = [
            ApprovalStatuses::APPROVED,
            ApprovalStatuses::PENDING,
            ApprovalStatuses::REJECTED
        ];

        $actions = [
            'approve',
            'suspend',
            'reject'
        ];

        foreach(range(0, 2) as $i)
        {
            $entity = Entity::factory()->create([
                'approval_status' => $statuses[$i],
                'approval_at' => now()->subHour(1),
            ]);

            $return = $entity->{$actions[$i]}();

            $this->assertNotEquals(now()->timestamp, $entity->approval_at->timestamp);
        }
            $this->assertFalse($return);
        
    }

    /**
     * @test
     */
    public function it_can_check_if_it_is_pending()
    {
        $pendingEntity = Entity::factory()->create();
        $approvedEntity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
        $rejectedEntity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::REJECTED
        ]);

        $this->assertTrue($pendingEntity->isPending());
        $this->assertFalse($approvedEntity->isPending());
        $this->assertFalse($rejectedEntity->isPending());
    }

    /**
     * @test
     */
    public function it_can_check_if_it_is_approved()
    {
        $pendingEntity = Entity::factory()->create();
        $approvedEntity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
        $rejectedEntity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::REJECTED
        ]);

        $this->assertFalse($pendingEntity->isApproved());
        $this->assertTrue($approvedEntity->isApproved());
        $this->assertFalse($rejectedEntity->isApproved());
    }

    /**
     * @test
     */
    public function it_can_check_if_it_is_rejected()
    {
        $pendingEntity = Entity::factory()->create();
        $approvedEntity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
        $rejectedEntity = Entity::factory()->create([
            'approval_status' => ApprovalStatuses::REJECTED
        ]);

        $this->assertFalse($pendingEntity->isRejected());
        $this->assertFalse($approvedEntity->isRejected());
        $this->assertTrue($rejectedEntity->isRejected());
    }

    /**
     * @test
     */
    public function it_refuses_to_check_status_when_not_exists()
    {
        $entity = Entity::factory()->make();

        foreach ($this->approvalChecks as $check) {
            $this->assertNull($entity->{$check}());
        }
    }
}
