<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Runtime;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Runtime\BusinessFunctionRuntime;
use WaysNX\BusinessFramework\Models\BusinessFunction;

/**
 * Runtime Experiment Tests
 *
 * Minimal tests to verify the runtime experiment works and demonstrates
 * what abstractions are actually needed for the execution pipeline.
 *
 * Purpose:
 * - Prove pipeline orchestration via BusinessFunctionAbstract::execute()
 * - Test error handling at each phase
 * - Verify no complex Kernel is needed for basic execution
 * - Measure abstractions required
 *
 * Key Finding: BusinessFunctionAbstract provides the public execute() method
 * that implements the canonical pipeline. The Runtime is framework-specific
 * observability wrapper.
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Runtime
 */
class RuntimeExperimentTest extends TestCase
{
    private BusinessFunctionRuntime $runtime;

    protected function setUp(): void
    {
        $this->runtime = new BusinessFunctionRuntime();
    }

    /**
     * Test 1: Successful execution returns response
     */
    public function test_successful_execution_returns_response(): void
    {
        $bf = $this->createTestBusinessFunction();

        $response = $this->runtime->execute($bf, ['field' => 'value']);

        $this->assertSame(['result' => 'executed'], $response);
        $this->assertTrue($this->runtime->wasSuccessful());
    }

    /**
     * Test 2: Validation failure throws and marks context as failed
     */
    public function test_validation_failure_throws_and_sets_context(): void
    {
        $bf = $this->createBusinessFunctionThatFailsValidation();

        $this->expectException(\InvalidArgumentException::class);
        $this->runtime->execute($bf, []);

        $context = $this->runtime->getContext();
        $this->assertSame('failed', $context['status']);
        $this->assertStringContainsString('Validation failed', $context['error']);
    }

    /**
     * Test 3: Authorization runs after validation succeeds
     */
    public function test_authorization_runs_after_validation(): void
    {
        $bf = $this->createBusinessFunctionThatFailsAuthorization();

        $this->expectException(\RuntimeException::class);
        $this->runtime->execute($bf, []);

        $context = $this->runtime->getContext();
        $this->assertSame('failed', $context['status']);
        $this->assertStringContainsString('Not authorized', $context['error']);
    }

    /**
     * Test 4: Business rules run after authorization succeeds
     */
    public function test_business_rules_run_after_authorization(): void
    {
        $bf = $this->createBusinessFunctionThatFailsBusinessRules();

        $this->expectException(\RuntimeException::class);
        $this->runtime->execute($bf, []);

        $context = $this->runtime->getContext();
        $this->assertSame('failed', $context['status']);
        $this->assertStringContainsString('Business rule violated', $context['error']);
    }

    /**
     * Test 5: Execution runs after all validation phases pass
     */
    public function test_execution_runs_after_all_phases_pass(): void
    {
        $bf = $this->createTestBusinessFunction();

        $response = $this->runtime->execute($bf, []);

        $this->assertSame(['result' => 'executed'], $response);
        $context = $this->runtime->getContext();
        $this->assertSame('success', $context['status']);
    }

    /**
     * Test 6: Events collected after successful execution
     */
    public function test_events_collected_after_execution(): void
    {
        $bf = $this->createTestBusinessFunction();

        $this->runtime->execute($bf, []);

        $events = $this->runtime->getEvents();
        $this->assertGreaterThan(0, count($events));
        $this->assertSame('TestEvent', $events[0]['type']);
    }

    /**
     * Test 7: Events NOT collected on failure
     */
    public function test_events_not_collected_on_failure(): void
    {
        $bf = $this->createBusinessFunctionThatFailsValidation();

        try {
            $this->runtime->execute($bf, []);
        } catch (\InvalidArgumentException) {
        }

        $events = $this->runtime->getEvents();
        $this->assertEmpty($events);
    }

    /**
     * Test 8: Observability metrics captured
     */
    public function test_observability_metrics_captured(): void
    {
        $bf = $this->createTestBusinessFunction();

        $this->runtime->execute($bf, []);

        $context = $this->runtime->getContext();
        $this->assertArrayHasKey('functionId', $context);
        $this->assertArrayHasKey('executionTime', $context);
        $this->assertArrayHasKey('status', $context);
        $this->assertGreaterThan(0, $context['executionTime']);
    }

    /**
     * Test 9: Response transformation applied
     */
    public function test_response_transformation_applied(): void
    {
        $bf = $this->createBusinessFunctionWithTransformation();

        $response = $this->runtime->execute($bf, []);

        // Verify internal fields removed by transformation
        $this->assertArrayHasKey('publicField', $response);
        $this->assertArrayNotHasKey('_internalField', $response);
    }

    /**
     * Helper: Create basic test business function
     */
    private function createTestBusinessFunction(): BusinessFunction
    {
        return new class extends BusinessFunction {
            public function __construct()
            {
                $this->functionId = 'TEST.MODULE.DOMAIN.FUNCTION';
                $this->functionName = 'Test';
                $this->functionVersion = '1.0.0';
                $this->module = 'TEST';
                $this->domain = 'MODULE';
                $this->capability = 'DOMAIN';
                $this->lifecycleStatus = 'Draft';
                $this->description = 'Test';
                $this->businessOwner = 'Owner';
                $this->technicalOwner = 'Tech';
                $this->tags = [];
                $this->classification = 'Core';
                $this->visibility = 'Public';
                $this->dependencies = [];
                $this->eventsPublished = ['TestEvent'];
                $this->eventsConsumed = [];
                $this->keywords = [];
                $this->requestContract = ['required' => ['field']];
                $this->responseContract = ['fields' => ['result' => ['type' => 'string']]];
                $this->errorCategories = [];
                $this->validationRules = [];
                $this->authorizationRequirements = [];
                $this->businessRules = [];
                $this->observabilityRequirements = [];
                $this->auditRequirements = [];
                $this->initializeBusinessFunction();
            }

            protected function executeBusiness(array $request): array
            {
                return ['result' => 'executed'];
            }
        };
    }

    /**
     * Helper: Business function that fails validation
     */
    private function createBusinessFunctionThatFailsValidation(): BusinessFunction
    {
        return new class extends BusinessFunction {
            public function __construct()
            {
                $this->functionId = 'TEST.MODULE.DOMAIN.FUNCTION';
                $this->functionName = 'Test';
                $this->functionVersion = '1.0.0';
                $this->module = 'TEST';
                $this->domain = 'MODULE';
                $this->capability = 'DOMAIN';
                $this->lifecycleStatus = 'Draft';
                $this->description = 'Test';
                $this->businessOwner = 'Owner';
                $this->technicalOwner = 'Tech';
                $this->tags = [];
                $this->classification = 'Core';
                $this->visibility = 'Public';
                $this->dependencies = [];
                $this->eventsPublished = [];
                $this->eventsConsumed = [];
                $this->keywords = [];
                $this->requestContract = ['required' => []];
                $this->responseContract = ['fields' => []];
                $this->errorCategories = [];
                $this->validationRules = [];
                $this->authorizationRequirements = [];
                $this->businessRules = [];
                $this->observabilityRequirements = [];
                $this->auditRequirements = [];
                $this->initializeBusinessFunction();
            }

            protected function validateRequest(array $request): void
            {
                throw new \InvalidArgumentException('Validation failed');
            }

            protected function executeBusiness(array $request): array
            {
                return [];
            }
        };
    }

    /**
     * Helper: Business function that fails authorization
     */
    private function createBusinessFunctionThatFailsAuthorization(): BusinessFunction
    {
        return new class extends BusinessFunction {
            public function __construct()
            {
                $this->functionId = 'TEST.MODULE.DOMAIN.FUNCTION';
                $this->functionName = 'Test';
                $this->functionVersion = '1.0.0';
                $this->module = 'TEST';
                $this->domain = 'MODULE';
                $this->capability = 'DOMAIN';
                $this->lifecycleStatus = 'Draft';
                $this->description = 'Test';
                $this->businessOwner = 'Owner';
                $this->technicalOwner = 'Tech';
                $this->tags = [];
                $this->classification = 'Core';
                $this->visibility = 'Public';
                $this->dependencies = [];
                $this->eventsPublished = [];
                $this->eventsConsumed = [];
                $this->keywords = [];
                $this->requestContract = ['required' => []];
                $this->responseContract = ['fields' => []];
                $this->errorCategories = [];
                $this->validationRules = [];
                $this->authorizationRequirements = [];
                $this->businessRules = [];
                $this->observabilityRequirements = [];
                $this->auditRequirements = [];
                $this->initializeBusinessFunction();
            }

            protected function checkAuthorization(array $request, mixed $caller = null): void
            {
                throw new \RuntimeException('Not authorized');
            }

            protected function executeBusiness(array $request): array
            {
                return [];
            }
        };
    }

    /**
     * Helper: Business function that fails business rules
     */
    private function createBusinessFunctionThatFailsBusinessRules(): BusinessFunction
    {
        return new class extends BusinessFunction {
            public function __construct()
            {
                $this->functionId = 'TEST.MODULE.DOMAIN.FUNCTION';
                $this->functionName = 'Test';
                $this->functionVersion = '1.0.0';
                $this->module = 'TEST';
                $this->domain = 'MODULE';
                $this->capability = 'DOMAIN';
                $this->lifecycleStatus = 'Draft';
                $this->description = 'Test';
                $this->businessOwner = 'Owner';
                $this->technicalOwner = 'Tech';
                $this->tags = [];
                $this->classification = 'Core';
                $this->visibility = 'Public';
                $this->dependencies = [];
                $this->eventsPublished = [];
                $this->eventsConsumed = [];
                $this->keywords = [];
                $this->requestContract = ['required' => []];
                $this->responseContract = ['fields' => []];
                $this->errorCategories = [];
                $this->validationRules = [];
                $this->authorizationRequirements = [];
                $this->businessRules = ['Rule violated'];
                $this->observabilityRequirements = [];
                $this->auditRequirements = [];
                $this->initializeBusinessFunction();
            }

            protected function evaluateBusinessRules(array $request): void
            {
                throw new \RuntimeException('Business rule violated');
            }

            protected function executeBusiness(array $request): array
            {
                return [];
            }
        };
    }

    /**
     * Helper: Business function with response transformation
     */
    private function createBusinessFunctionWithTransformation(): BusinessFunction
    {
        return new class extends BusinessFunction {
            public function __construct()
            {
                $this->functionId = 'TEST.MODULE.DOMAIN.FUNCTION';
                $this->functionName = 'Test';
                $this->functionVersion = '1.0.0';
                $this->module = 'TEST';
                $this->domain = 'MODULE';
                $this->capability = 'DOMAIN';
                $this->lifecycleStatus = 'Draft';
                $this->description = 'Test';
                $this->businessOwner = 'Owner';
                $this->technicalOwner = 'Tech';
                $this->tags = [];
                $this->classification = 'Core';
                $this->visibility = 'Public';
                $this->dependencies = [];
                $this->eventsPublished = [];
                $this->eventsConsumed = [];
                $this->keywords = [];
                $this->requestContract = ['required' => []];
                $this->responseContract = ['fields' => []];
                $this->errorCategories = [];
                $this->validationRules = [];
                $this->authorizationRequirements = [];
                $this->businessRules = [];
                $this->observabilityRequirements = [];
                $this->auditRequirements = [];
                $this->initializeBusinessFunction();
            }

            protected function executeBusiness(array $request): array
            {
                return [
                    'publicField' => 'public',
                    '_internalField' => 'secret',
                ];
            }

            protected function transformToResponse(array $result): array
            {
                return [
                    'publicField' => $result['publicField'],
                ];
            }
        };
    }
}
