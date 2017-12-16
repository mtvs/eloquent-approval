<?php

namespace Mtvs\EloquentApproval\Tests;

use Illuminate\Support\Carbon;
use Mtvs\EloquentApproval\ApprovalScope;
use Mtvs\EloquentApproval\ApprovalStatuses;
use Mtvs\EloquentApproval\Tests\Models\Entity;

class ApprovalScopeTest extends TestCase
{
    /**
     * @test
     */
    public function it_retrieves_only_approved_by_default()
    {
        $this->createOneEntityFromEachStatus();

        $entities = Entity::all();

        $this->assertNotEmpty($entities);

        foreach ($entities as $entity) {
            $this->assertEquals(
                ApprovalStatuses::APPROVED,
                $entity->approval_status
            );
        }
    }

    /**
     * @test
     */
    public function it_can_retrieve_all()
    {
        $this->createOneEntityFromEachStatus();

        $entities = Entity::anyApprovalStatus()->get();

        $totalCount = Entity::withoutGlobalScope(new ApprovalScope())->count();

        $this->assertCount($totalCount, $entities);
    }

    /**
     * @test
     */
    public function it_can_retrieve_only_pending()
    {
        $this->createOneEntityFromEachStatus();

        $entities = Entity::onlyPending()->get();

        $this->assertNotEmpty($entities);

        foreach ($entities as $entity) {
            $this->assertEquals(
                ApprovalStatuses::PENDING,
                $entity->approval_status
            );
        }
    }

    /**
     * @test
     */
    public function it_can_retrieve_only_rejected()
    {
        $this->createOneEntityFromEachStatus();

        $entities = Entity::onlyRejected()->get();

        $this->assertNotEmpty($entities);

        foreach ($entities as $entity) {
            $this->assertEquals(
                ApprovalStatuses::REJECTED,
                $entity->approval_status
            );
        }
    }

    /**
     * @test
     */
    public function it_can_retrieve_only_approved()
    {
        $this->createOneEntityFromEachStatus();

        $entities = Entity::onlyApproved()->get();

        $this->assertNotEmpty($entities);

        foreach ($entities as $entity) {
            $this->assertEquals(
                ApprovalStatuses::APPROVED,
                $entity->approval_status
            );
        }
    }

    /**
     * @test
     */
    public function it_can_approve_entities()
    {
        $this->createOneEntityFromEachStatus();

        Entity::query()->approve();

        $entities = Entity::withoutGlobalScope(new ApprovalScope())->get();

        foreach ($entities as $entity) {
            $this->assertEquals(ApprovalStatuses::APPROVED, $entity->approval_status);
        }
    }

    /**
     * @test
     */
    public function it_can_reject_entities()
    {
        $this->createOneEntityFromEachStatus();

        Entity::query()->reject();

        $entities = Entity::withoutGlobalScope(new ApprovalScope())->get();

        foreach ($entities as $entity) {
            $this->assertEquals(ApprovalStatuses::REJECTED, $entity->approval_status);
        }
    }

    /**
     * @test
     */
    public function it_can_suspend_entities()
    {
        $this->createOneEntityFromEachStatus();

        Entity::query()->suspend();

        $entities = Entity::withoutGlobalScope(new ApprovalScope())->get();

        foreach ($entities as $entity) {
            $this->assertEquals(ApprovalStatuses::PENDING, $entity->approval_status);
        }
    }

    /**
     * @test
     */
    public function it_refreshes_approval_at_on_status_update()
    {
        foreach ($this->approvalActions as $action) {
            $entityId = factory(Entity::class)->create()->id;

            $timestampString = (new Entity())->freshTimestampString();

            Entity::whereId($entityId)->{$action}();

            $this->assertDatabaseHas('entities', [
                'id' => $entityId,
                'approval_at' => $timestampString
            ]);
        }
    }

    /**
     * @test
     */
    public function it_refreshes_updated_at_on_status_update()
    {

        foreach ($this->approvalActions as $action) {
            $entityId = factory(Entity::class)->create([
                'updated_at' => (New Entity())->fromDateTime(Carbon::now()->subHour())
            ])->id;

            $timestampString = (new Entity())->freshTimestampString();

            Entity::whereId($entityId)->{$action}();

            $this->assertDatabaseHas('entities', [
                'id' => $entityId,
                'updated_at' => $timestampString
            ]);
        }
    }

    /**
     * @test
     */
    public function it_returns_number_of_updated_entities_on_status_update()
    {
        factory(Entity::class, 3)->create();

        foreach ($this->approvalActions as $action) {
            $this->assertEquals(1, Entity::whereId(1)->{$action}());
            $this->assertEquals(3, Entity::query()->{$action}());
            $this->assertEquals(0, Entity::whereId(0)->{$action}());
        }
    }

    protected function createOneEntityFromEachStatus()
    {
        factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::PENDING
        ]);

        factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::APPROVED
        ]);

        factory(Entity::class)->create([
            'approval_status' => ApprovalStatuses::REJECTED
        ]);
    }
}
