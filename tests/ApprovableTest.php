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
        $entity = factory(Entity::class)->create();

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
        $entity = factory(Entity::class)->create([
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
        $entity = factory(Entity::class)->create();

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
        $entity = factory(Entity::class)->create();

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
        $entity = factory(Entity::class)->create([
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
        foreach ($this->approvalActions as $action) {
            $entity = factory(Entity::class)->create();

            $time = (new Entity())->freshTimestamp();

            $entity->{$action}();

            $this->assertEquals($time->timestamp, $entity->approval_at->timestamp);

            $this->assertDatabaseHas('entities', [
                'id' => $entity->id,
                'approval_at' => $entity->fromDateTime($time)
            ]);
        }
    }


    /**
     * @test
     */
    public function it_returns_true_when_updates_status()
    {
        $entity = factory(Entity::class)->create();

        foreach ($this->approvalActions as $action) {
            $this->assertTrue($entity->{$action}());
        }
    }

    /**
     * @test
     */
    public function it_refuses_to_update_status_when_not_exists()
    {
        $entity = factory(Entity::class)->make();

        foreach ($this->approvalActions as $action) {
            $this->assertNull($entity->{$action}());

            $this->assertNull($entity->approval_at);
        }
    }

    /**
     * @test
     */
    public function it_can_check_if_it_is_pending()
    {
        $pendingEntity = factory(Entity::class)->create();
        $approvedEntity = factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
        $rejectedEntity = factory(Entity::class)->create([
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
        $pendingEntity = factory(Entity::class)->create();
        $approvedEntity = factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
        $rejectedEntity = factory(Entity::class)->create([
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
        $pendingEntity = factory(Entity::class)->create();
        $approvedEntity = factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
        $rejectedEntity = factory(Entity::class)->create([
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
        $entity = factory(Entity::class)->make();

        foreach ($this->approvalChecks as $check) {
            $this->assertNull($entity->{$check}());
        }
    }
}
