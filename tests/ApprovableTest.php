<?php

namespace Mtvs\EloquentApproval\Tests;

use Illuminate\Support\Carbon;
use Mtvs\EloquentApproval\ApprovalStatuses;
use Mtvs\EloquentApproval\Tests\Models\Entity;
use Mtvs\EloquentApproval\Tests\Models\EntityWithCustomColumns;
use Mtvs\EloquentApproval\Tests\Models\EntityWithNoApprovalRequiredAttributes;

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
    public function it_is_suspended_on_approval_required_modification()
    {
        $approved = factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);

        $rejected = factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::REJECTED
        ]);

        $approved->update([
            'attr_1' => 'val 1',
        ]);

        $rejected->update([
            'attr_1' => 'val 1',
        ]);

        $this->assertEquals(ApprovalStatuses::PENDING, $approved->approval_status);

        $this->assertEquals(ApprovalStatuses::PENDING, $rejected->approval_status);

        $this->assertDatabaseHas('entities', [
            'id' => $approved->id,
            'approval_status' => ApprovalStatuses::PENDING
        ]);
        $this->assertDatabaseHas('entities', [
            'id' => $rejected->id,
            'approval_status' => ApprovalStatuses::PENDING
        ]);
    }

    /**
     * @test
     */
    public function it_is_not_suspended_on_approval_not_required_modification()
    {
        $approved = factory(EntityWithNoApprovalRequiredAttributes::class)->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);

        $rejected = factory(EntityWithNoApprovalRequiredAttributes::class)->create([
            'approval_status' => ApprovalStatuses::REJECTED
        ]);

        $approved->update([
            'attr_1' => 'val 1',
            'attr_2' => 'val 2',
            'attr_3' => 'val 3',
        ]);

        $rejected->update([
            'attr_1' => 'val 1',
            'attr_2' => 'val 2',
            'attr_3' => 'val 3',
        ]);


        $this->assertEquals(ApprovalStatuses::APPROVED, $approved->approval_status);
        $this->assertEquals(ApprovalStatuses::REJECTED, $rejected->approval_status);

        $this->assertDatabaseHas('entities', [
            'id' => $approved->id,
            'approval_status' => ApprovalStatuses::APPROVED
        ]);
        $this->assertDatabaseHas('entities', [
            'id' => $rejected->id,
            'approval_status' => ApprovalStatuses::REJECTED
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
    public function it_refreshes_the_entity_updated_at_on_status_update()
    {
        $time = (new Entity())->freshTimestamp();

        $entities = factory(Entity::class, 3)->create([
            'updated_at' => (New Entity())->fromDateTime($time->copy()->subHour())
        ]);

        foreach ($this->approvalActions as $action) {
            $entity = factory(Entity::class)->create([
                'updated_at' => (New Entity())->fromDateTime(Carbon::now()->subHour())
            ]);

            $time = (new Entity())->freshTimestamp();

            $entity->{$action}();

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
