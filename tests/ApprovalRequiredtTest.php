<?php

namespace Mtvs\EloquentApproval\Tests;

use Mtvs\EloquentApproval\Tests\Models\Entity;

class ApprovalRequiredtTest extends TestCase
{
    /** @var Entity */
    protected $entity;

    protected function setUp()
    {
        parent::setUp();

        $this->entity = new Entity();
    }

    /**
     * @test
     */
    public function it_defaults_to_all_required()
    {
        $this->assertEquals(['*'], $this->entity->getApprovalRequired());
        $this->assertCount(0, $this->entity->getApprovalNotRequired());
    }

    /**
     * @test
     */
    public function it_works_when_all_are_required()
    {
        $this->assertTrue($this->entity->isApprovalRequired('attr_1'));
        $this->assertTrue($this->entity->isApprovalRequired('attr_2'));
        $this->assertTrue($this->entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_all_are_required_except_some_not_required()
    {
        $this->entity->setApprovalNotRequired(['attr_3']);

        $this->assertTrue($this->entity->isApprovalRequired('attr_1'));
        $this->assertTrue($this->entity->isApprovalRequired('attr_2'));
        $this->assertFalse($this->entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_some_are_required_and_the_rest_not_required()
    {
        $this->entity->setApprovalRequired(['attr_1']);

        $this->assertTrue($this->entity->isApprovalRequired('attr_1'));
        $this->assertFalse($this->entity->isApprovalRequired('attr_2'));
        $this->assertFalse($this->entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_non_is_required()
    {
        $this->entity->setApprovalRequired([]);

        $this->assertFalse($this->entity->isApprovalRequired('attr_1'));
        $this->assertFalse($this->entity->isApprovalRequired('attr_2'));
        $this->assertFalse($this->entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_some_are_required_and_some_not_required()
    {
        $this->entity->setApprovalRequired(['attr_1']);
        $this->entity->setApprovalNotRequired(['attr_2']);

        $this->assertTrue($this->entity->isApprovalRequired('attr_1'));
        $this->assertFalse($this->entity->isApprovalRequired('attr_2'));
        $this->assertTrue($this->entity->isApprovalRequired('attr_3'));
    }

    /**
     * @test
     */
    public function it_works_when_some_are_not_required_and_the_rest_are_required()
    {
        $this->entity->setApprovalRequired([]);
        $this->entity->setApprovalNotRequired(['attr_1']);

        $this->assertFalse($this->entity->isApprovalRequired('attr_1'));
        $this->assertTrue($this->entity->isApprovalRequired('attr_2'));
        $this->assertTrue($this->entity->isApprovalRequired('attr_3'));
    }
}