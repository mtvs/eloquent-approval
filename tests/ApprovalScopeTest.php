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
