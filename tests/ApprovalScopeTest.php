<?php

namespace Mtvs\EloquentApproval\Tests;

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
                $entity->approval_status,
                ApprovalStatuses::APPROVED
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
                $entity->approval_status,
                ApprovalStatuses::PENDING
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
                $entity->approval_status,
                ApprovalStatuses::REJECTED
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
                $entity->approval_status,
                ApprovalStatuses::APPROVED
            );
        }
    }

    /**
     * @test
     */
    public function it_can_approve_entities()
    {
        $this->createOneEntityFromEachStatus();

        Entity::approve();

        $entities = Entity::withoutGlobalScope(new ApprovalScope())->get();

        foreach ($entities as $entity) {
            $this->assertEquals($entity->approval_status, ApprovalStatuses::APPROVED);
        }
    }

    /**
     * @test
     */
    public function it_can_reject_entities()
    {
        $this->createOneEntityFromEachStatus();

        Entity::reject();

        $entities = Entity::withoutGlobalScope(new ApprovalScope())->get();

        foreach ($entities as $entity) {
            $this->assertEquals($entity->approval_status, ApprovalStatuses::REJECTED);
        }
    }

    /**
     * @test
     */
    public function it_can_suspend_entities()
    {
        $this->createOneEntityFromEachStatus();

        Entity::suspend();

        $entities = Entity::withoutGlobalScope(new ApprovalScope())->get();

        foreach ($entities as $entity) {
            $this->assertEquals($entity->approval_status, ApprovalStatuses::PENDING);
        }
    }

    /**
     * @test
     */
    public function it_refreshes_approval_at_on_status_update()
    {
        factory(Entity::class, 3)->create();

        $timestampString = (new Entity())->freshTimestampString();

        Entity::whereId(1)->approve();
        Entity::whereId(2)->reject();
        Entity::whereId(3)->suspend();

        $approved = Entity::withoutGlobalScope(new ApprovalScope())->find(1);
        $rejected = Entity::withoutGlobalScope(new ApprovalScope())->find(2);
        $suspended = Entity::withoutGlobalScope(new ApprovalScope())->find(3);

        $entities = collect([$approved, $rejected, $suspended]);

        foreach ($entities as $entity) {
            $this->assertNotNull($entity->approval_at);
            $this->assertEquals($timestampString, $entity->approval_at);
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
