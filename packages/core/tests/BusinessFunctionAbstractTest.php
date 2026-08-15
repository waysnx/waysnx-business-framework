<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core\Tests;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Core\BusinessFunctionAbstract;
use WaysNX\BusinessFramework\Types\BusinessFunctionInterface;

/**
 * Concrete test implementation of BusinessFunctionAbstract
 *
 * Used to test the abstract class (PHP doesn't allow instantiation of abstract classes).
 */
class ConcreteBusinessFunction extends BusinessFunctionAbstract
{
    protected function generateUuid(): string
    {
        return 'test-uuid-' . uniqid();
    }

    protected function initializeAuditTimestamps(): void
    {
        $this->createdAt = 'test-timestamp';
        $this->updatedAt = null;
        $this->deletedAt = null;
    }

    protected function updateTimestamp(): void
    {
        $this->updatedAt = 'test-updated-timestamp';
    }

    protected function deleteTimestamp(): void
    {
        $this->deletedAt = 'test-deleted-timestamp';
    }

    protected function toArray(): array
    {
        return $this->getCompleteContractDefinition();
    }

    protected function toJson(): string
    {
        return json_encode($this->toArray(), \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);
    }

    protected function executeBusiness(array $request): array
    {
        return ['status' => 'success', 'result' => $request];
    }
}

/**
 * BusinessFunctionAbstractTest
 *
 * Tests the framework-independent BusinessFunction model layer.
 *
 * Verifies:
 * - Function Identity (ID, Name, Version, Module, Domain, Capability, Status)
 * - Function Metadata (Owner, Tags, Classification, etc.)
 * - Business Contracts (Request, Response, Errors)
 * - Processing Definition (Validation, Authorization, Business Rules)
 * - Operational Definition (Observability, Audit)
 * - Versioning and Evolution
 * - Complete Contract Definition generation
 * - Lifecycle hooks and initialization
 *
 * Conforms to: WBF-DOC-0005 Business Function Specification
 *
 * @covers \WaysNX\BusinessFramework\Core\BusinessFunctionAbstract
 * @package WaysNX\BusinessFramework\Core\Tests
 */
class BusinessFunctionAbstractTest extends TestCase
{
    /**
     * Create a test business function instance
     *
     * @return ConcreteBusinessFunction
     */
    private function createTestFunction(): ConcreteBusinessFunction
    {
        $func = new ConcreteBusinessFunction();

        // Set Identity
        $func->functionId = 'HR.LEAVE.APPLY.APPLY_LEAVE';
        $func->functionName = 'Apply Leave';
        $func->functionVersion = '1.0.0';
        $func->module = 'HR';
        $func->domain = 'LEAVE';
        $func->capability = 'APPLY';
        $func->lifecycleStatus = 'Released';

        // Set Metadata
        $func->description = 'Submit an employee leave request';
        $func->businessOwner = 'HR Manager';
        $func->technicalOwner = 'HR Team';
        $func->tags = ['leave', 'employee', 'request'];
        $func->classification = 'Core';
        $func->visibility = 'Internal';
        $func->dependencies = [];
        $func->eventsPublished = ['LeaveRequested'];
        $func->eventsConsumed = [];
        $func->keywords = ['leave', 'absence', 'vacation'];

        // Set Contracts
        $func->requestContract = [
            'required' => ['employeeId', 'leaveType', 'startDate', 'endDate'],
            'optional' => ['reason'],
            'fields' => [
                'employeeId' => ['type' => 'string', 'description' => 'Employee ID'],
                'leaveType' => ['type' => 'string', 'description' => 'Type of leave'],
                'startDate' => ['type' => 'date', 'description' => 'Start date'],
                'endDate' => ['type' => 'date', 'description' => 'End date'],
                'reason' => ['type' => 'string', 'description' => 'Reason (optional)'],
            ],
        ];

        $func->responseContract = [
            'fields' => [
                'leaveRequestId' => ['type' => 'string', 'description' => 'Request ID'],
                'status' => ['type' => 'string', 'description' => 'Request status'],
                'remainingBalance' => ['type' => 'integer', 'description' => 'Remaining leave balance'],
            ],
            'success_fields' => ['leaveRequestId', 'status', 'remainingBalance'],
        ];

        $func->errorCategories = ['ValidationError', 'BusinessRuleViolation', 'ResourceNotFound'];

        // Set Processing
        $func->validationRules = [
            'employeeId' => 'required|string',
            'leaveType' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
        ];

        $func->authorizationRequirements = [
            'roles' => ['Employee', 'Manager'],
            'permission' => 'leave.apply',
        ];

        $func->businessRules = [
            'Employee must be active',
            'Leave balance must be sufficient',
            'Requested dates shall not overlap existing approved leave',
        ];

        // Set Operational
        $func->observabilityRequirements = [
            'correlation' => true,
            'metrics' => ['execution_time', 'success_rate'],
        ];

        $func->auditRequirements = [
            'enabled' => true,
            'trackChanges' => true,
        ];

        $func->initializeBusinessFunction();

        return $func;
    }

    // ========================================
    // IDENTITY TESTS
    // ========================================

    /**
     * Test 1: BusinessFunction can be created
     *
     * @test
     */
    public function test_business_function_can_be_created(): void
    {
        $func = $this->createTestFunction();
        $this->assertInstanceOf(BusinessFunctionAbstract::class, $func);
        $this->assertInstanceOf(BusinessFunctionInterface::class, $func);
    }

    /**
     * Test 2: Function ID is available
     *
     * @test
     */
    public function test_function_id_is_available(): void
    {
        $func = $this->createTestFunction();
        $this->assertEquals('HR.LEAVE.APPLY.APPLY_LEAVE', $func->getFunctionId());
    }

    /**
     * Test 3: Function Name is available
     *
     * @test
     */
    public function test_function_name_is_available(): void
    {
        $func = $this->createTestFunction();
        $this->assertEquals('Apply Leave', $func->getFunctionName());
    }

    /**
     * Test 4: Function Version is available
     *
     * @test
     */
    public function test_function_version_is_available(): void
    {
        $func = $this->createTestFunction();
        $this->assertEquals('1.0.0', $func->getFunctionVersion());
    }

    /**
     * Test 5: Module/Domain/Capability are available
     *
     * @test
     */
    public function test_module_domain_capability_are_available(): void
    {
        $func = $this->createTestFunction();
        $this->assertEquals('HR', $func->getModule());
        $this->assertEquals('LEAVE', $func->getDomain());
        $this->assertEquals('APPLY', $func->getCapability());
    }

    /**
     * Test 6: Lifecycle Status is available
     *
     * @test
     */
    public function test_lifecycle_status_is_available(): void
    {
        $func = $this->createTestFunction();
        $this->assertEquals('Released', $func->getLifecycleStatus());
    }

    /**
     * Test 7: Owner information is available
     *
     * @test
     */
    public function test_owner_information_is_available(): void
    {
        $func = $this->createTestFunction();
        $this->assertEquals('HR Manager', $func->getBusinessOwner());
        $this->assertEquals('HR Team', $func->getTechnicalOwner());
    }

    // ========================================
    // METADATA TESTS
    // ========================================

    /**
     * Test 8: Required metadata is supported
     *
     * @test
     */
    public function test_required_metadata_is_supported(): void
    {
        $func = $this->createTestFunction();

        $this->assertEquals('Submit an employee leave request', $func->getDescription());
        $this->assertEquals('Core', $func->getClassification());
        $this->assertEquals('Internal', $func->getVisibility());
        $this->assertContains('leave', $func->getTags());
    }

    /**
     * Test 9: Tags are available
     *
     * @test
     */
    public function test_tags_are_available(): void
    {
        $func = $this->createTestFunction();
        $tags = $func->getTags();
        $this->assertIsArray($tags);
        $this->assertCount(3, $tags);
        $this->assertContains('leave', $tags);
    }

    /**
     * Test 10: Dependencies are available
     *
     * @test
     */
    public function test_dependencies_are_available(): void
    {
        $func = $this->createTestFunction();
        $this->assertIsArray($func->getDependencies());
        $this->assertEmpty($func->getDependencies());
    }

    /**
     * Test 11: Events are declared
     *
     * @test
     */
    public function test_events_are_declared(): void
    {
        $func = $this->createTestFunction();
        $this->assertContains('LeaveRequested', $func->getEventsPublished());
        $this->assertIsArray($func->getEventsConsumed());
    }

    /**
     * Test 12: Keywords are available
     *
     * @test
     */
    public function test_keywords_are_available(): void
    {
        $func = $this->createTestFunction();
        $keywords = $func->getKeywords();
        $this->assertIsArray($keywords);
        $this->assertContains('leave', $keywords);
    }

    // ========================================
    // CONTRACT TESTS
    // ========================================

    /**
     * Test 13: Request contract can be defined
     *
     * @test
     */
    public function test_request_contract_can_be_defined(): void
    {
        $func = $this->createTestFunction();
        $request = $func->getRequestContract();

        $this->assertIsArray($request);
        $this->assertArrayHasKey('required', $request);
        $this->assertArrayHasKey('optional', $request);
        $this->assertArrayHasKey('fields', $request);
        $this->assertContains('employeeId', $request['required']);
    }

    /**
     * Test 14: Response contract can be defined
     *
     * @test
     */
    public function test_response_contract_can_be_defined(): void
    {
        $func = $this->createTestFunction();
        $response = $func->getResponseContract();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('fields', $response);
        $this->assertArrayHasKey('success_fields', $response);
        $this->assertArrayHasKey('leaveRequestId', $response['fields']);
    }

    /**
     * Test 15: Errors can be defined
     *
     * @test
     */
    public function test_errors_can_be_defined(): void
    {
        $func = $this->createTestFunction();
        $errors = $func->getErrorCategories();

        $this->assertIsArray($errors);
        $this->assertContains('ValidationError', $errors);
        $this->assertContains('BusinessRuleViolation', $errors);
    }

    // ========================================
    // PROCESSING DEFINITION TESTS
    // ========================================

    /**
     * Test 16: Validation rules can be represented
     *
     * @test
     */
    public function test_validation_rules_can_be_represented(): void
    {
        $func = $this->createTestFunction();
        $rules = $func->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('employeeId', $rules);
        $this->assertArrayHasKey('leaveType', $rules);
    }

    /**
     * Test 17: Authorization requirements can be represented
     *
     * @test
     */
    public function test_authorization_requirements_can_be_represented(): void
    {
        $func = $this->createTestFunction();
        $auth = $func->getAuthorizationRequirements();

        $this->assertIsArray($auth);
        $this->assertArrayHasKey('roles', $auth);
        $this->assertContains('Employee', $auth['roles']);
    }

    /**
     * Test 18: Business rules can be represented
     *
     * @test
     */
    public function test_business_rules_can_be_represented(): void
    {
        $func = $this->createTestFunction();
        $rules = $func->getBusinessRules();

        $this->assertIsArray($rules);
        $this->assertContains('Employee must be active', $rules);
        $this->assertContains('Leave balance must be sufficient', $rules);
    }

    // ========================================
    // OPERATIONAL DEFINITION TESTS
    // ========================================

    /**
     * Test 19: Observability requirements can be represented
     *
     * @test
     */
    public function test_observability_requirements_can_be_represented(): void
    {
        $func = $this->createTestFunction();
        $obs = $func->getObservabilityRequirements();

        $this->assertIsArray($obs);
        $this->assertTrue($obs['correlation']);
    }

    /**
     * Test 20: Audit requirements can be represented
     *
     * @test
     */
    public function test_audit_requirements_can_be_represented(): void
    {
        $func = $this->createTestFunction();
        $audit = $func->getAuditRequirements();

        $this->assertIsArray($audit);
        $this->assertTrue($audit['enabled']);
    }

    // ========================================
    // COMPLETE CONTRACT TESTS
    // ========================================

    /**
     * Test 21: BusinessFunction can be serialized into canonical representation
     *
     * @test
     */
    public function test_business_function_can_be_serialized(): void
    {
        $func = $this->createTestFunction();
        $definition = $func->getCompleteContractDefinition();

        $this->assertIsArray($definition);
        $this->assertArrayHasKey('identity', $definition);
        $this->assertArrayHasKey('metadata', $definition);
        $this->assertArrayHasKey('contract', $definition);
        $this->assertArrayHasKey('processing', $definition);
        $this->assertArrayHasKey('operational', $definition);
    }

    /**
     * Test 22: Complete contract contains all identity information
     *
     * @test
     */
    public function test_complete_contract_contains_identity(): void
    {
        $func = $this->createTestFunction();
        $definition = $func->getCompleteContractDefinition();
        $identity = $definition['identity'];

        $this->assertEquals('HR.LEAVE.APPLY.APPLY_LEAVE', $identity['functionId']);
        $this->assertEquals('Apply Leave', $identity['functionName']);
        $this->assertEquals('1.0.0', $identity['functionVersion']);
        $this->assertEquals('HR', $identity['module']);
        $this->assertEquals('LEAVE', $identity['domain']);
        $this->assertEquals('APPLY', $identity['capability']);
        $this->assertEquals('Released', $identity['lifecycleStatus']);
    }

    /**
     * Test 23: Complete contract contains all metadata information
     *
     * @test
     */
    public function test_complete_contract_contains_metadata(): void
    {
        $func = $this->createTestFunction();
        $definition = $func->getCompleteContractDefinition();
        $metadata = $definition['metadata'];

        $this->assertEquals('Submit an employee leave request', $metadata['description']);
        $this->assertEquals('HR Manager', $metadata['businessOwner']);
        $this->assertEquals('HR Team', $metadata['technicalOwner']);
        $this->assertContains('LeaveRequested', $metadata['eventsPublished']);
    }

    // ========================================
    // VERSIONING TESTS
    // ========================================

    /**
     * Test 24: BusinessFunction inherits versioning from BaseModel
     *
     * @test
     */
    public function test_business_function_inherits_versioning(): void
    {
        $func = $this->createTestFunction();
        $this->assertIsInt($func->getEntityVersion());
        $this->assertEquals(1, $func->getEntityVersion());
    }

    /**
     * Test 25: BusinessFunction has entity ID (from BaseModel)
     *
     * @test
     */
    public function test_business_function_has_entity_id(): void
    {
        $func = $this->createTestFunction();
        $entityId = $func->getEntityId();

        $this->assertIsString($entityId);
        $this->assertNotEmpty($entityId);
        $this->assertStringStartsWith('test-uuid-', $entityId);
    }

    // ========================================
    // INITIALIZATION TESTS
    // ========================================

    /**
     * Test 26: Missing mandatory fields throw exception
     *
     * @test
     */
    public function test_missing_mandatory_fields_throw_exception(): void
    {
        $func = new ConcreteBusinessFunction();
        $func->functionId = 'TEST.ID';
        // Missing other required fields

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Business Function definition is incomplete');

        $func->initializeBusinessFunction();
    }

    /**
     * Test 27: Missing request contract throws exception
     *
     * @test
     */
    public function test_missing_request_contract_throws_exception(): void
    {
        $func = new ConcreteBusinessFunction();
        $func->functionId = 'HR.LEAVE.APPLY.APPLY_LEAVE';
        $func->functionName = 'Apply Leave';
        $func->functionVersion = '1.0.0';
        $func->module = 'HR';
        $func->domain = 'LEAVE';
        $func->capability = 'APPLY';
        $func->lifecycleStatus = 'Released';
        $func->description = 'Test';
        $func->businessOwner = 'Owner';
        $func->technicalOwner = 'Tech';
        $func->classification = 'Core';
        $func->visibility = 'Internal';
        // requestContract is empty

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('requestContract is empty');

        $func->initializeBusinessFunction();
    }
}
