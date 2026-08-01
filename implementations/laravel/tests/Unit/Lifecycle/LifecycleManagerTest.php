<?php

declare(strict_types=1);

namespace Tests\Unit\Lifecycle;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\DuplicateHandlerException;
use WaysNX\BusinessFramework\Exceptions\HandlerNotFoundException;
use WaysNX\BusinessFramework\Exceptions\LifecycleException;
use WaysNX\BusinessFramework\Lifecycle\LifecycleContext;
use WaysNX\BusinessFramework\Lifecycle\LifecycleEvent;
use WaysNX\BusinessFramework\Lifecycle\LifecycleHandler;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;

/**
 * LifecycleManagerTest
 *
 * Tests for LifecycleManager functionality.
 *
 * @package Tests\Unit\Lifecycle
 */
class LifecycleManagerTest extends TestCase
{
    /**
     * @var LifecycleManager
     */
    private LifecycleManager $manager;

    protected function setUp(): void
    {
        $this->manager = new LifecycleManager();
    }

    /**
     * Test handler registration
     */
    public function testRegisterHandler(): void
    {
        $handler = new LifecycleHandler(
            id: 'test-handler',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $result = $this->manager->register($handler);

        $this->assertSame($this->manager, $result);
        $this->assertTrue($this->manager->exists('test-handler'));
        $this->assertEquals(1, $this->manager->count());
    }

    /**
     * Test duplicate handler registration throws exception
     */
    public function testRegisterDuplicateHandlerThrowsException(): void
    {
        $handler = new LifecycleHandler(
            id: 'test-handler',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $this->expectException(DuplicateHandlerException::class);
        $this->expectExceptionMessage("Handler with ID 'test-handler' is already registered");

        $this->manager->register($handler);
    }

    /**
     * Test handler unregistration
     */
    public function testUnregisterHandler(): void
    {
        $handler = new LifecycleHandler(
            id: 'test-handler',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);
        $result = $this->manager->unregister('test-handler');

        $this->assertSame($this->manager, $result);
        $this->assertFalse($this->manager->exists('test-handler'));
        $this->assertEquals(0, $this->manager->count());
    }

    /**
     * Test unregister non-existent handler throws exception
     */
    public function testUnregisterNonExistentHandlerThrowsException(): void
    {
        $this->expectException(HandlerNotFoundException::class);
        $this->expectExceptionMessage("Handler with ID 'non-existent' not found");

        $this->manager->unregister('non-existent');
    }

    /**
     * Test check handler existence
     */
    public function testExistsHandler(): void
    {
        $handler = new LifecycleHandler(
            id: 'test-handler',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $this->assertFalse($this->manager->exists('test-handler'));

        $this->manager->register($handler);

        $this->assertTrue($this->manager->exists('test-handler'));
    }

    /**
     * Test handler dispatch execution
     */
    public function testDispatchExecutesHandlers(): void
    {
        $calls = [];

        $handler = new LifecycleHandler(
            id: 'test-handler',
            callable: function(LifecycleEvent $event, LifecycleContext $context) use (&$calls) {
                $calls[] = [
                    'event' => $event->name,
                    'entityId' => $context->entityId,
                ];
            },
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $event = new LifecycleEvent('beforeCreate');
        $context = new LifecycleContext(entityId: 'user-123');

        $this->manager->dispatch($event, $context);

        $this->assertCount(1, $calls);
        $this->assertEquals('beforeCreate', $calls[0]['event']);
        $this->assertEquals('user-123', $calls[0]['entityId']);
    }

    /**
     * Test dispatch respects execution order by priority
     */
    public function testDispatchRespectsPriority(): void
    {
        $executionOrder = [];

        $handler1 = new LifecycleHandler(
            id: 'low-priority',
            callable: function() use (&$executionOrder) {
                $executionOrder[] = 'low';
            },
            supportedEvents: ['beforeCreate'],
            priority: 1
        );

        $handler2 = new LifecycleHandler(
            id: 'high-priority',
            callable: function() use (&$executionOrder) {
                $executionOrder[] = 'high';
            },
            supportedEvents: ['beforeCreate'],
            priority: 10
        );

        $handler3 = new LifecycleHandler(
            id: 'medium-priority',
            callable: function() use (&$executionOrder) {
                $executionOrder[] = 'medium';
            },
            supportedEvents: ['beforeCreate'],
            priority: 5
        );

        $this->manager->register($handler1);
        $this->manager->register($handler2);
        $this->manager->register($handler3);

        $event = new LifecycleEvent('beforeCreate');
        $context = new LifecycleContext();

        $this->manager->dispatch($event, $context);

        $this->assertEquals(['high', 'medium', 'low'], $executionOrder);
    }

    /**
     * Test dispatch skips disabled handlers
     */
    public function testDispatchSkipsDisabledHandlers(): void
    {
        $calls = [];

        $handler1 = new LifecycleHandler(
            id: 'enabled-handler',
            callable: function() use (&$calls) {
                $calls[] = 'enabled';
            },
            supportedEvents: ['beforeCreate'],
            enabled: true
        );

        $handler2 = new LifecycleHandler(
            id: 'disabled-handler',
            callable: function() use (&$calls) {
                $calls[] = 'disabled';
            },
            supportedEvents: ['beforeCreate'],
            enabled: false
        );

        $this->manager->register($handler1);
        $this->manager->register($handler2);

        $event = new LifecycleEvent('beforeCreate');
        $context = new LifecycleContext();

        $this->manager->dispatch($event, $context);

        $this->assertCount(1, $calls);
        $this->assertEquals('enabled', $calls[0]);
    }

    /**
     * Test dispatch with multiple events
     */
    public function testDispatchMultipleEvents(): void
    {
        $calls = [];

        $handler = new LifecycleHandler(
            id: 'multi-handler',
            callable: function(LifecycleEvent $event) use (&$calls) {
                $calls[] = $event->name;
            },
            supportedEvents: ['beforeCreate', 'afterCreate', 'beforeUpdate']
        );

        $this->manager->register($handler);

        $this->manager->dispatch(new LifecycleEvent('beforeCreate'), new LifecycleContext());
        $this->manager->dispatch(new LifecycleEvent('afterCreate'), new LifecycleContext());
        $this->manager->dispatch(new LifecycleEvent('beforeUpdate'), new LifecycleContext());
        $this->manager->dispatch(new LifecycleEvent('beforeDelete'), new LifecycleContext());

        $this->assertCount(3, $calls);
        $this->assertContains('beforeCreate', $calls);
        $this->assertContains('afterCreate', $calls);
        $this->assertContains('beforeUpdate', $calls);
    }

    /**
     * Test context propagation to handlers
     */
    public function testContextPropagation(): void
    {
        $receivedContext = null;

        $handler = new LifecycleHandler(
            id: 'context-handler',
            callable: function(LifecycleEvent $event, LifecycleContext $context) use (&$receivedContext) {
                $receivedContext = $context;
            },
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $event = new LifecycleEvent('beforeCreate');
        $context = new LifecycleContext(
            entityId: 'user-123',
            entityType: 'User',
            moduleId: 'crm',
            businessFunctionId: 'create-user',
            correlationId: 'req-456',
            metadata: ['userId' => 789]
        );

        $this->manager->dispatch($event, $context);

        $this->assertNotNull($receivedContext);
        $this->assertEquals('user-123', $receivedContext->entityId);
        $this->assertEquals('User', $receivedContext->entityType);
        $this->assertEquals('crm', $receivedContext->moduleId);
        $this->assertEquals('create-user', $receivedContext->businessFunctionId);
        $this->assertEquals('req-456', $receivedContext->correlationId);
        $this->assertEquals(789, $receivedContext->getMetadataValue('userId'));
    }

    /**
     * Test get handlers for event
     */
    public function testGetHandlersForEvent(): void
    {
        $handler1 = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $handler2 = new LifecycleHandler(
            id: 'handler-2',
            callable: fn() => null,
            supportedEvents: ['beforeCreate', 'afterCreate']
        );

        $handler3 = new LifecycleHandler(
            id: 'handler-3',
            callable: fn() => null,
            supportedEvents: ['beforeUpdate']
        );

        $this->manager->register($handler1);
        $this->manager->register($handler2);
        $this->manager->register($handler3);

        $beforeCreateHandlers = $this->manager->handlers('beforeCreate');

        $this->assertCount(2, $beforeCreateHandlers);
        $this->assertTrue(in_array('handler-1', array_map(fn($h) => $h->id, $beforeCreateHandlers)));
        $this->assertTrue(in_array('handler-2', array_map(fn($h) => $h->id, $beforeCreateHandlers)));
    }

    /**
     * Test get all handlers when no event specified
     */
    public function testGetAllHandlers(): void
    {
        $handler1 = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $handler2 = new LifecycleHandler(
            id: 'handler-2',
            callable: fn() => null,
            supportedEvents: ['afterCreate']
        );

        $this->manager->register($handler1);
        $this->manager->register($handler2);

        $allHandlers = $this->manager->handlers();

        $this->assertCount(2, $allHandlers);
    }

    /**
     * Test get supported events
     */
    public function testSupportedEvents(): void
    {
        $handler1 = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate', 'afterCreate']
        );

        $handler2 = new LifecycleHandler(
            id: 'handler-2',
            callable: fn() => null,
            supportedEvents: ['beforeUpdate', 'afterUpdate']
        );

        $this->manager->register($handler1);
        $this->manager->register($handler2);

        $supportedEvents = $this->manager->supportedEvents();

        $this->assertCount(4, $supportedEvents);
        $this->assertContains('beforeCreate', $supportedEvents);
        $this->assertContains('afterCreate', $supportedEvents);
        $this->assertContains('beforeUpdate', $supportedEvents);
        $this->assertContains('afterUpdate', $supportedEvents);
    }

    /**
     * Test is enabled check
     */
    public function testIsEnabled(): void
    {
        $handler = new LifecycleHandler(
            id: 'test-handler',
            callable: fn() => null,
            supportedEvents: ['beforeCreate'],
            enabled: true
        );

        $this->manager->register($handler);

        $this->assertTrue($this->manager->isEnabled('test-handler'));
    }

    /**
     * Test is disabled check
     */
    public function testIsDisabled(): void
    {
        $handler = new LifecycleHandler(
            id: 'test-handler',
            callable: fn() => null,
            supportedEvents: ['beforeCreate'],
            enabled: false
        );

        $this->manager->register($handler);

        $this->assertTrue($this->manager->isDisabled('test-handler'));
    }

    /**
     * Test is enabled check on non-existent handler throws exception
     */
    public function testIsEnabledNonExistentThrowsException(): void
    {
        $this->expectException(HandlerNotFoundException::class);

        $this->manager->isEnabled('non-existent');
    }

    /**
     * Test clear all handlers
     */
    public function testClearHandlers(): void
    {
        $handler1 = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $handler2 = new LifecycleHandler(
            id: 'handler-2',
            callable: fn() => null,
            supportedEvents: ['afterCreate']
        );

        $this->manager->register($handler1);
        $this->manager->register($handler2);

        $result = $this->manager->clear();

        $this->assertSame($this->manager, $result);
        $this->assertEquals(0, $this->manager->count());
        $this->assertEmpty($this->manager->supportedEvents());
    }

    /**
     * Test handler not found for event returns empty array
     */
    public function testGetHandlersForUnknownEventReturnsEmpty(): void
    {
        $handler = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $handlers = $this->manager->handlers('unknownEvent');

        $this->assertEmpty($handlers);
    }

    /**
     * Test handler execution failure throws lifecycle exception
     */
    public function testHandlerExecutionFailureThrowsException(): void
    {
        $handler = new LifecycleHandler(
            id: 'failing-handler',
            callable: function() {
                throw new \RuntimeException('Handler failed');
            },
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $this->expectException(LifecycleException::class);
        $this->expectExceptionMessageMatches("/Handler 'failing-handler' failed/");

        $this->manager->dispatch(new LifecycleEvent('beforeCreate'), new LifecycleContext());
    }

    /**
     * Test has handlers check
     */
    public function testHasHandlers(): void
    {
        $this->assertFalse($this->manager->hasHandlers());

        $handler = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $this->assertTrue($this->manager->hasHandlers());
    }

    /**
     * Test has handlers for event
     */
    public function testHasHandlersFor(): void
    {
        $this->assertFalse($this->manager->hasHandlersFor('beforeCreate'));

        $handler = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $this->assertTrue($this->manager->hasHandlersFor('beforeCreate'));
        $this->assertFalse($this->manager->hasHandlersFor('afterCreate'));
    }

    /**
     * Test handler count for event
     */
    public function testHandlerCountFor(): void
    {
        $handler1 = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $handler2 = new LifecycleHandler(
            id: 'handler-2',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler1);
        $this->manager->register($handler2);

        $this->assertEquals(2, $this->manager->handlerCountFor('beforeCreate'));
        $this->assertEquals(0, $this->manager->handlerCountFor('afterCreate'));
    }

    /**
     * Test method chaining
     */
    public function testMethodChaining(): void
    {
        $handler1 = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: ['beforeCreate']
        );

        $handler2 = new LifecycleHandler(
            id: 'handler-2',
            callable: fn() => null,
            supportedEvents: ['afterCreate']
        );

        $result = $this->manager
            ->register($handler1)
            ->register($handler2)
            ->unregister('handler-1')
            ->clear();

        $this->assertSame($this->manager, $result);
        $this->assertEquals(0, $this->manager->count());
    }

    /**
     * Test empty handlers dispatch does nothing
     */
    public function testDispatchWithoutHandlersDoesNothing(): void
    {
        $event = new LifecycleEvent('beforeCreate');
        $context = new LifecycleContext();

        // Should not throw
        $this->manager->dispatch($event, $context);

        $this->assertTrue(true);
    }

    /**
     * Test handler with no supported events
     */
    public function testHandlerWithNoSupportedEvents(): void
    {
        $handler = new LifecycleHandler(
            id: 'handler-1',
            callable: fn() => null,
            supportedEvents: []
        );

        $this->manager->register($handler);

        $this->assertTrue($this->manager->exists('handler-1'));
        $this->assertEquals(1, $this->manager->count());
        $this->assertEmpty($this->manager->supportedEvents());
        $this->assertEmpty($this->manager->handlers('beforeCreate'));
    }

    /**
     * Test dispatch with event metadata
     */
    public function testDispatchWithEventMetadata(): void
    {
        $receivedEvent = null;

        $handler = new LifecycleHandler(
            id: 'handler-1',
            callable: function(LifecycleEvent $event) use (&$receivedEvent) {
                $receivedEvent = $event;
            },
            supportedEvents: ['beforeCreate']
        );

        $this->manager->register($handler);

        $event = new LifecycleEvent('beforeCreate', ['action' => 'create', 'version' => 2]);

        $this->manager->dispatch($event, new LifecycleContext());

        $this->assertNotNull($receivedEvent);
        $this->assertEquals('create', $receivedEvent->getMetadataValue('action'));
        $this->assertEquals(2, $receivedEvent->getMetadataValue('version'));
    }
}
