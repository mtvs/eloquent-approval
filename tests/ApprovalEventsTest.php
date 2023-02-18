<?php

namespace Mtvs\EloquentApproval\Tests;

use Illuminate\Support\Arr;
use Mtvs\EloquentApproval\ApprovalStatuses;
use Mtvs\EloquentApproval\Tests\Models\Entity;

class ApprovalEventsTest extends TestCase
{
    protected $actions = [
        'approve',
        'suspend',
        'reject'
    ];

    protected $statuses = [
        ApprovalStatuses::APPROVED,
        ApprovalStatuses::PENDING,
        ApprovalStatuses::REJECTED
    ];

    protected $checks = [
        'isApproved',
        'isPending',
        'isRejected'
    ];

    protected $beforeEvents = [
        'approving',
        'suspending',
        'rejecting'
    ];

    protected $afterEvents = [
        'approved',
        'suspended',
        'rejected'
    ];

    /**
     * @test
     */
    public function it_dispatches_events_before_approval_actions()
    {
        $entity = Entity::factory()->create();

        for ($i = 0; $i < count($this->actions); $i++) {
            $action = $this->actions[$i];
            $event = $this->beforeEvents[$i];
            $listener = $event.'Listener';
            $mock = $this->getMockBuilder('stdClass')
                ->addMethods([$listener])
                ->getMock();
            $mock->expects($this->once())->method($listener);

            Entity::$event([$mock, $listener]);

            $entity->$action();
        }
    }

    /**
     * @test
     */
    public function it_allows_listeners_of_before_action_events_halt_the_action_execution()
    {
        for ($i = 0; $i < count($this->actions); $i++) {
            $action = $this->actions[$i];
            $beforeEvent = $this->beforeEvents[$i];
            $beforeListener = $beforeEvent.'Listener';
            $afterEvent = $this->afterEvents[$i];
            $afterEventListener = $afterEvent.'Listener';
            $mock = $this->getMockBuilder('stdClass')
                ->addMethods([$beforeListener, $afterEventListener])
                ->getMock();
            $mock->method($beforeListener)->will($this->returnValue(false));
            $mock->expects($this->never())->method($afterEventListener);
            Entity::$beforeEvent([$mock, $beforeListener]);
            Entity::$afterEvent([$mock, $afterEventListener]);

            $entity = Entity::factory()->create([
                'approval_status' => Arr::random(Arr::except($this->statuses, [$i]))
            ]);

            $this->assertFalse($entity->$action());

            $this->assertFalse($entity->{$this->checks[$i]}());

            $this->assertDatabaseMissing('entities', [
                'id' => $entity->id,
                'approval_status' => $this->statuses[$i]
            ]);
        }
    }

    /**
     * @test
     */
    public function it_dispatches_events_after_approval_actions()
    {
        $entity = Entity::factory()->create();
        
        for ($i = 0; $i < count($this->actions); $i++) {
            $action = $this->actions[$i];
            $event = $this->afterEvents[$i];
            $listener = $event.'Listener';
            $mock = $this->getMockBuilder('stdClass')
                ->addMethods([$listener, 'approvalChangedListener'])
                ->getMock();
            $mock->expects($this->once())->method($listener);
            $mock->expects($this->once())->method('approvalChangedListener');

            Entity::$event([$mock, $listener]);
            Entity::getEventDispatcher()->forget("eloquent.approvalChanged: ".Entity::class);
            Entity::approvalChanged([$mock, 'approvalChangedListener']);

            $entity->$action();
        }
    }

    /**
     * @test
     */
    public function it_will_not_dispatch_the_events_on_the_duplicate_approvals()
    {
        for($i = 0; $i < count($this->statuses); $i++)
        {
            $entity = Entity::factory()->create([
                'approval_status' => $this->statuses[$i],
                'approval_at' => (new Entity())->freshTimestamp()
            ]);

            $beforeEvent = $this->beforeEvents[$i];
            $afterEvent = $this->afterEvents[$i];

            $mock = $this->getMockBuilder('stdClass')
                ->addMethods([
                    'beforeListener',
                    'afterListener', 
                    'approvalChangedListener'
                ])->getMock();

            $mock->expects($this->never())->method('beforeListener');
            $mock->expects($this->never())->method('afterListener');
            $mock->expects($this->never())->method('approvalChangedListener');

            Entity::$beforeEvent([$mock, 'beforeListener']);
            Entity::$afterEvent([$mock, 'afterListener']);
            Entity::approvalChanged([$mock, 'approvalChangedListener']);

            $entity->{$this->actions[$i]}();
        }
    }

    /**
     * @test
     */
    public function it_supports_observers()
    {
        $observerMock = $this->getMockBuilder('stdClass')
            ->addMethods($events = array_merge($this->beforeEvents, $this->afterEvents))
            ->getMock();

        foreach ($events as $event) {
            $observerMock->expects($this->once())->method($event);
        }

        app()->singleton(get_class($observerMock), function () use ($observerMock) {
            return $observerMock;
        });

        Entity::observe($observerMock);

        $entity = Entity::factory()->create();

        foreach ($this->actions as $action) {
            $entity->$action();
        }
    }                   
}