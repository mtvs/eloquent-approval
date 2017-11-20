<?php

namespace Mtvs\EloquentApproval\Tests;

use Mtvs\EloquentApproval\Tests\Models\Entity;
use Mtvs\EloquentApproval\Tests\Models\EntityWithCustomApprovalStatusColumn;

class ApprovableTest extends TestCase
{
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
        $entity = new EntityWithCustomApprovalStatusColumn();

        $this->assertEquals(
            EntityWithCustomApprovalStatusColumn::APPROVAL_STATUS,
            $entity->getApprovalStatusColumn()
        );
    }
}
