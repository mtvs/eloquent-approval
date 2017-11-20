<?php

namespace Mtvs\EloquentApproval\Tests;

use Mtvs\EloquentApproval\ApprovalStatuses;
use Mtvs\EloquentApproval\Tests\Models\Entity;

class ApprovalScopeTest extends TestCase
{
    /**
     * @test
     */
    public function it_retrieves_only_approved_by_default()
    {
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::PENDING]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::APPROVED]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::REJECTED]);

        $entities = Entity::all();

        $this->assertCount(1, $entities);

        $this->assertEquals($entities[0]->approval_status, ApprovalStatuses::APPROVED);
    }

    /**
     * @test
     */
    public function it_can_retrieve_all()
    {
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::PENDING]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::APPROVED]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::REJECTED]);

        $entities = Entity::anyApprovalStatus()->get();

        $this->assertCount(3, $entities);
    }

    /**
     * @test
     */
    public function it_can_retrieve_only_pending()
    {
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::PENDING]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::APPROVED]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::REJECTED]);

        $entities = Entity::onlyPending()->get();

        $this->assertCount(1, $entities);

        $this->assertEquals($entities[0]->approval_status, ApprovalStatuses::PENDING);
    }

    /**
     * @test
     */
    public function it_can_retrieve_only_rejected()
    {
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::PENDING]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::APPROVED]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::REJECTED]);

        $entities = Entity::onlyRejected()->get();

        $this->assertCount(1, $entities);

        $this->assertEquals($entities[0]->approval_status, ApprovalStatuses::REJECTED);
    }

    /**
     * @test
     */
    public function it_can_retrieve_only_approved()
    {
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::PENDING]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::APPROVED]);
        factory(Entity::class)->create(['approval_status' => ApprovalStatuses::REJECTED]);

        $entities = Entity::onlyApproved()->get();

        $this->assertCount(1, $entities);

        $this->assertEquals($entities[0]->approval_status, ApprovalStatuses::APPROVED);
    }
}
