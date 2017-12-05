<?php

namespace Mtvs\EloquentApproval\Tests;

use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;
use Mtvs\EloquentApproval\Tests\Models\Entity;

class ApprovalRequiredtTest extends TestCase
{
    /**
     * @test
     */
    public function it_defaults_to_all_required()
    {
        $entity = new Class extends Model {
            use Approvable;
        };

        $this->assertEquals(['*'], $entity->approvalRequired());
        $this->assertEquals([], $entity->approvalNotRequired());
    }

    /**
     * @test
     */
    public function it_works_when_all_are_required()
    {
        $entity = new Class extends Model {
            use Approvable;
        };

        $this->assertTrue($entity->isApprovalRequired('attr_1'));
        $this->assertTrue($entity->isApprovalRequired('attr_2'));
        $this->assertTrue($entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_all_are_required_except_some_not_required()
    {
        $entity = new Class extends Model {
            use Approvable;

            public function approvalNotRequired()
            {
                return ['attr_3'];
            }
        };

        $this->assertTrue($entity->isApprovalRequired('attr_1'));
        $this->assertTrue($entity->isApprovalRequired('attr_2'));
        $this->assertFalse($entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_some_are_required_and_the_rest_not_required()
    {
        $entity = new Class extends Model {
            use Approvable;

            public function approvalRequired()
            {
                return ['attr_1'];
            }
        };

        $this->assertTrue($entity->isApprovalRequired('attr_1'));
        $this->assertFalse($entity->isApprovalRequired('attr_2'));
        $this->assertFalse($entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_non_is_required()
    {
        $entity = new Class extends Model
        {
            use Approvable;

            public function approvalRequired()
            {
                return [];
            }
        };

        $this->assertFalse($entity->isApprovalRequired('attr_1'));
        $this->assertFalse($entity->isApprovalRequired('attr_2'));
        $this->assertFalse($entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_some_are_required_and_some_not_required()
    {
        $entity = new Class extends Model
        {
            use Approvable;

            public function approvalRequired()
            {
                return ['attr_1'];
            }

            public function approvalNotRequired()
            {
                return ['attr_2'];
            }
        };

        $this->assertTrue($entity->isApprovalRequired('attr_1'));
        $this->assertFalse($entity->isApprovalRequired('attr_2'));
        $this->assertTrue($entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_some_are_not_required_and_the_rest_are_required()
    {
        $entity = new Class extends Model
        {
            use Approvable;

            public function approvalRequired()
            {
                return [];
            }

            public function approvalNotRequired()
            {
                return ['attr_1'];
            }
        };

        $this->assertFalse($entity->isApprovalRequired('attr_1'));
        $this->assertTrue($entity->isApprovalRequired('attr_2'));
        $this->assertTrue($entity->isApprovalRequired('attr_3'));
    }
}