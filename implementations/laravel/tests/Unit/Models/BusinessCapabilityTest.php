<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Models\BusinessCapability;

/**
 * BusinessCapabilityTest
 *
 * Comprehensive test suite for BusinessCapability model implementation.
 *
 * Tests verify:
 * - Capability creation and initialization
 * - Identity fields (ID, name, description)
 * - Domain relationship
 * - Business owner/ownership
 * - Lifecycle and status
 * - Business outcome
 * - KPIs
 * - Dependencies
 * - Workflows
 * - Services
 * - Business rules
 * - Policies
 * - Events
 * - Serialization (toArray, toJson)
 * - Validation
 * - Version consistency
 * - Framework independence
 * - Domain hierarchy integrity
 * - Existing Domain behavior preservation
 *
 * @covers \WaysNX\BusinessFramework\Models\BusinessCapability
 * @covers \WaysNX\BusinessFramework\Core\BusinessCapabilityAbstract
 */
class BusinessCapabilityTest extends TestCase
{
    /**
     * Create a valid BusinessCapability instance for testing
     *
     * @return BusinessCapability
     */
    private function createValidCapability(): BusinessCapability
    {
        $capability = new BusinessCapability();

        $reflection = new \ReflectionClass($capability);

        // Set via reflection to bypass setter validation during setup
        $reflection->getProperty('capabilityId')->setValue($capability, 'emp-search');
        $reflection->getProperty('capabilityName')->setValue($capability, 'Employee Search');
        $reflection->getProperty('description')->setValue($capability, 'Search employee records');
        $reflection->getProperty('domainId')->setValue($capability, 'EMP_MGMT');
        $reflection->getProperty('businessOwner')->setValue($capability, 'hr-manager-001');
        $reflection->getProperty('status')->setValue($capability, BusinessCapability::IMPLEMENT);
        $reflection->getProperty('businessOutcome')->setValue($capability, 'Locate employee info');

        // Initialize via reflection to avoid visibility issues
        $initMethod = $reflection->getMethod('initializeCapability');
        $initMethod->invoke($capability);

        return $capability;
    }

    /**
     * @test
     * @group capability-creation
     */
    public function test_can_create_valid_capability(): void
    {
        $capability = $this->createValidCapability();

        $this->assertInstanceOf(BusinessCapability::class, $capability);
        $this->assertEquals('emp-search', $capability->getCapabilityId());
        $this->assertEquals('Employee Search', $capability->getCapabilityName());
    }

    /**
     * @test
     * @group capability-creation
     */
    public function test_creation_initializes_base_model_fields(): void
    {
        $capability = $this->createValidCapability();

        $this->assertNotEmpty($capability->getEntityId());
        $this->assertEquals('Capability', $capability->getEntityType());
        $this->assertEquals(1, $capability->getEntityVersion());
        $this->assertNotNull($capability->getCreatedAt());
    }

    // ========================================
    // IDENTITY FIELDS TESTS
    // ========================================

    /**
     * @test
     * @group capability-identity
     */
    public function test_capability_id_getter_setter(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals('emp-search', $capability->getCapabilityId());
    }

    /**
     * @test
     * @group capability-identity
     */
    public function test_capability_id_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capability ID cannot be empty');

        $capability = $this->createValidCapability();
        $capability->setCapabilityId('');
    }

    /**
     * @test
     * @group capability-identity
     */
    public function test_capability_name_getter_setter(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals('Employee Search', $capability->getCapabilityName());
    }

    /**
     * @test
     * @group capability-identity
     */
    public function test_capability_name_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capability name cannot be empty');

        $capability = $this->createValidCapability();
        $capability->setCapabilityName('');
    }

    /**
     * @test
     * @group capability-identity
     */
    public function test_description_getter_setter(): void
    {
        $capability = $this->createValidCapability();
        $capability->setDescription('Search and filter employee records');

        $this->assertEquals('Search and filter employee records', $capability->getDescription());
    }

    /**
     * @test
     * @group capability-identity
     */
    public function test_description_can_be_empty(): void
    {
        $capability = $this->createValidCapability();
        $capability->setDescription('');

        $this->assertEquals('', $capability->getDescription());
    }

    // ========================================
    // DOMAIN RELATIONSHIP TESTS
    // ========================================

    /**
     * @test
     * @group capability-domain
     */
    public function test_domain_id_getter_setter(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals('EMP_MGMT', $capability->getDomainId());
    }

    /**
     * @test
     * @group capability-domain
     */
    public function test_domain_id_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain ID cannot be empty');

        $capability = $this->createValidCapability();
        $capability->setDomainId('');
    }

    /**
     * @test
     * @group capability-domain
     */
    public function test_domain_id_can_be_integer(): void
    {
        $capability = $this->createValidCapability();
        $capability->setDomainId(123);

        $this->assertEquals(123, $capability->getDomainId());
    }

    // ========================================
    // BUSINESS OWNER TESTS
    // ========================================

    /**
     * @test
     * @group capability-owner
     */
    public function test_business_owner_getter_setter(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals('hr-manager-001', $capability->getBusinessOwner());
    }

    /**
     * @test
     * @group capability-owner
     */
    public function test_business_owner_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Business owner cannot be empty');

        $capability = $this->createValidCapability();
        $capability->setBusinessOwner('');
    }

    /**
     * @test
     * @group capability-owner
     */
    public function test_business_owner_can_be_integer(): void
    {
        $capability = $this->createValidCapability();
        $capability->setBusinessOwner(123);

        $this->assertEquals(123, $capability->getBusinessOwner());
    }

    // ========================================
    // LIFECYCLE/STATUS TESTS
    // ========================================

    /**
     * @test
     * @group capability-lifecycle
     */
    public function test_all_valid_lifecycle_states(): void
    {
        $states = [
            BusinessCapability::IDENTIFY,
            BusinessCapability::ANALYZE,
            BusinessCapability::DESIGN,
            BusinessCapability::REVIEW,
            BusinessCapability::APPROVE,
            BusinessCapability::IMPLEMENT,
            BusinessCapability::OPERATE,
            BusinessCapability::IMPROVE,
            BusinessCapability::RETIRE,
        ];

        foreach ($states as $state) {
            $capability = $this->createValidCapability();
            $capability->setStatus($state);
            $this->assertEquals($state, $capability->getStatus());
        }
    }

    /**
     * @test
     * @group capability-lifecycle
     */
    public function test_invalid_status_raises_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid capability status');

        $capability = $this->createValidCapability();
        $capability->setStatus('InvalidStatus');
    }

    /**
     * @test
     * @group capability-lifecycle
     */
    public function test_default_status_is_identify(): void
    {
        $capability = new BusinessCapability();
        $this->assertEquals(BusinessCapability::IDENTIFY, $capability->getStatus());
    }

    // ========================================
    // BUSINESS OUTCOME TESTS
    // ========================================

    /**
     * @test
     * @group capability-outcome
     */
    public function test_business_outcome_getter_setter(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals('Locate employee info', $capability->getBusinessOutcome());
    }

    /**
     * @test
     * @group capability-outcome
     */
    public function test_business_outcome_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Business outcome cannot be empty');

        $capability = $this->createValidCapability();
        $capability->setBusinessOutcome('');
    }

    /**
     * @test
     * @group capability-outcome
     */
    public function test_business_outcome_metadata_preserved(): void
    {
        $capability = $this->createValidCapability();
        $outcome = 'Enable rapid employee search across organization';
        $capability->setBusinessOutcome($outcome);

        $this->assertEquals($outcome, $capability->getBusinessOutcome());
    }

    // ========================================
    // WORKFLOWS TESTS
    // ========================================

    /**
     * @test
     * @group capability-workflows
     */
    public function test_add_workflow_with_valid_structure(): void
    {
        $capability = $this->createValidCapability();
        $workflow = ['id' => 'wf-search', 'name' => 'Search Workflow'];
        $capability->addWorkflow($workflow);

        $this->assertTrue($capability->hasWorkflow('wf-search'));
        $this->assertEquals($workflow, $capability->getWorkflow('wf-search'));
    }

    /**
     * @test
     * @group capability-workflows
     */
    public function test_cannot_add_duplicate_workflow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow with id "wf-search" already exists');

        $capability = $this->createValidCapability();
        $capability->addWorkflow(['id' => 'wf-search', 'name' => 'Search Workflow']);
        $capability->addWorkflow(['id' => 'wf-search', 'name' => 'Duplicate Workflow']);
    }

    /**
     * @test
     * @group capability-workflows
     */
    public function test_remove_workflow_by_id(): void
    {
        $capability = $this->createValidCapability();
        $capability->addWorkflow(['id' => 'wf-search', 'name' => 'Search Workflow']);
        $capability->removeWorkflow('wf-search');

        $this->assertFalse($capability->hasWorkflow('wf-search'));
    }

    /**
     * @test
     * @group capability-workflows
     */
    public function test_get_workflow_returns_correct_workflow(): void
    {
        $capability = $this->createValidCapability();
        $workflow1 = ['id' => 'wf-search', 'name' => 'Search Workflow'];
        $workflow2 = ['id' => 'wf-filter', 'name' => 'Filter Workflow'];
        $capability->addWorkflow($workflow1);
        $capability->addWorkflow($workflow2);

        $this->assertEquals($workflow1, $capability->getWorkflow('wf-search'));
        $this->assertEquals($workflow2, $capability->getWorkflow('wf-filter'));
    }

    /**
     * @test
     * @group capability-workflows
     */
    public function test_has_workflow_checks_correctly(): void
    {
        $capability = $this->createValidCapability();
        $capability->addWorkflow(['id' => 'wf-search', 'name' => 'Search Workflow']);

        $this->assertTrue($capability->hasWorkflow('wf-search'));
        $this->assertFalse($capability->hasWorkflow('wf-nonexistent'));
    }

    /**
     * @test
     * @group capability-workflows
     */
    public function test_workflow_must_have_id_field(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow must have an id field');

        $capability = $this->createValidCapability();
        $capability->addWorkflow(['name' => 'Workflow without ID']);
    }

    /**
     * @test
     * @group capability-workflows
     */
    public function test_multiple_workflows_supported(): void
    {
        $capability = $this->createValidCapability();
        $capability->addWorkflow(['id' => 'wf-1', 'name' => 'Workflow 1']);
        $capability->addWorkflow(['id' => 'wf-2', 'name' => 'Workflow 2']);
        $capability->addWorkflow(['id' => 'wf-3', 'name' => 'Workflow 3']);

        $workflows = $capability->getWorkflows();
        $this->assertCount(3, $workflows);
    }

    // ========================================
    // SERVICES TESTS
    // ========================================

    /**
     * @test
     * @group capability-services
     */
    public function test_add_service_with_valid_structure(): void
    {
        $capability = $this->createValidCapability();
        $service = ['id' => 'svc-search', 'name' => 'Employee Search Service'];
        $capability->addService($service);

        $this->assertTrue($capability->hasService('svc-search'));
        $this->assertEquals($service, $capability->getService('svc-search'));
    }

    /**
     * @test
     * @group capability-services
     */
    public function test_cannot_add_duplicate_service(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Service with id "svc-search" already exists');

        $capability = $this->createValidCapability();
        $capability->addService(['id' => 'svc-search', 'name' => 'Employee Search Service']);
        $capability->addService(['id' => 'svc-search', 'name' => 'Duplicate Service']);
    }

    /**
     * @test
     * @group capability-services
     */
    public function test_remove_service_by_id(): void
    {
        $capability = $this->createValidCapability();
        $capability->addService(['id' => 'svc-search', 'name' => 'Employee Search Service']);
        $capability->removeService('svc-search');

        $this->assertFalse($capability->hasService('svc-search'));
    }

    /**
     * @test
     * @group capability-services
     */
    public function test_get_service_returns_correct_service(): void
    {
        $capability = $this->createValidCapability();
        $service1 = ['id' => 'svc-search', 'name' => 'Search Service'];
        $service2 = ['id' => 'svc-create', 'name' => 'Create Service'];
        $capability->addService($service1);
        $capability->addService($service2);

        $this->assertEquals($service1, $capability->getService('svc-search'));
        $this->assertEquals($service2, $capability->getService('svc-create'));
    }

    /**
     * @test
     * @group capability-services
     */
    public function test_has_service_checks_correctly(): void
    {
        $capability = $this->createValidCapability();
        $capability->addService(['id' => 'svc-search', 'name' => 'Employee Search Service']);

        $this->assertTrue($capability->hasService('svc-search'));
        $this->assertFalse($capability->hasService('svc-nonexistent'));
    }

    /**
     * @test
     * @group capability-services
     */
    public function test_service_must_have_id_field(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Service must have an id field');

        $capability = $this->createValidCapability();
        $capability->addService(['name' => 'Service without ID']);
    }

    /**
     * @test
     * @group capability-services
     */
    public function test_multiple_services_supported(): void
    {
        $capability = $this->createValidCapability();
        $capability->addService(['id' => 'svc-1', 'name' => 'Service 1']);
        $capability->addService(['id' => 'svc-2', 'name' => 'Service 2']);
        $capability->addService(['id' => 'svc-3', 'name' => 'Service 3']);

        $services = $capability->getServices();
        $this->assertCount(3, $services);
    }

    // ========================================
    // KPIs TESTS
    // ========================================

    /**
     * @test
     * @group capability-kpis
     */
    public function test_kpis_getter_setter(): void
    {
        $capability = $this->createValidCapability();
        $kpis = [
            ['name' => 'Search Efficiency', 'target' => 95],
            ['name' => 'Response Time', 'target' => 100],
        ];
        $capability->setKpis($kpis);

        $this->assertEquals($kpis, $capability->getKpis());
    }

    /**
     * @test
     * @group capability-kpis
     */
    public function test_kpis_can_be_empty_array(): void
    {
        $capability = $this->createValidCapability();
        $capability->setKpis([]);

        $this->assertEmpty($capability->getKpis());
    }

    /**
     * @test
     * @group capability-kpis
     */
    public function test_kpi_metadata_preserved(): void
    {
        $capability = $this->createValidCapability();
        $kpis = [
            ['name' => 'Efficiency', 'target' => 95, 'unit' => 'percent', 'owner' => 'manager'],
        ];
        $capability->setKpis($kpis);

        $this->assertEquals($kpis, $capability->getKpis());
    }

    // ========================================
    // DEPENDENCIES TESTS
    // ========================================

    /**
     * @test
     * @group capability-dependencies
     */
    public function test_add_dependency(): void
    {
        $capability = $this->createValidCapability();
        $capability->addDependency('emp-profile');

        $this->assertTrue($capability->hasDependency('emp-profile'));
    }

    /**
     * @test
     * @group capability-dependencies
     */
    public function test_cannot_add_duplicate_dependency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capability dependency "emp-profile" already exists');

        $capability = $this->createValidCapability();
        $capability->addDependency('emp-profile');
        $capability->addDependency('emp-profile');
    }

    /**
     * @test
     * @group capability-dependencies
     */
    public function test_remove_dependency(): void
    {
        $capability = $this->createValidCapability();
        $capability->addDependency('emp-profile');
        $capability->removeDependency('emp-profile');

        $this->assertFalse($capability->hasDependency('emp-profile'));
    }

    /**
     * @test
     * @group capability-dependencies
     */
    public function test_has_dependency_checks_correctly(): void
    {
        $capability = $this->createValidCapability();
        $capability->addDependency('emp-profile');

        $this->assertTrue($capability->hasDependency('emp-profile'));
        $this->assertFalse($capability->hasDependency('emp-nonexistent'));
    }

    /**
     * @test
     * @group capability-dependencies
     */
    public function test_get_dependencies_returns_all(): void
    {
        $capability = $this->createValidCapability();
        $capability->addDependency('emp-profile');
        $capability->addDependency('emp-history');
        $capability->addDependency('emp-documents');

        $dependencies = $capability->getDependencies();
        $this->assertCount(3, $dependencies);
        $this->assertContains('emp-profile', $dependencies);
    }

    // ========================================
    // BUSINESS RULES TESTS
    // ========================================

    /**
     * @test
     * @group capability-business-rules
     */
    public function test_business_rules_getter_setter(): void
    {
        $capability = $this->createValidCapability();
        $rules = [
            ['ruleId' => 'rule-1', 'description' => 'Rule 1'],
            ['ruleId' => 'rule-2', 'description' => 'Rule 2'],
        ];
        $capability->setBusinessRules($rules);

        $this->assertEquals($rules, $capability->getBusinessRules());
    }

    /**
     * @test
     * @group capability-business-rules
     */
    public function test_can_store_rule_definitions(): void
    {
        $capability = $this->createValidCapability();
        $capability->setBusinessRules([
            ['ruleId' => 'br-001', 'description' => 'Only active employees'],
            ['ruleId' => 'br-002', 'description' => 'Only managers can view salary'],
        ]);

        $rules = $capability->getBusinessRules();
        $this->assertCount(2, $rules);
    }

    // ========================================
    // POLICIES TESTS
    // ========================================

    /**
     * @test
     * @group capability-policies
     */
    public function test_policies_getter_setter(): void
    {
        $capability = $this->createValidCapability();
        $policies = [
            ['policyId' => 'pol-1', 'description' => 'Policy 1'],
            ['policyId' => 'pol-2', 'description' => 'Policy 2'],
        ];
        $capability->setPolicies($policies);

        $this->assertEquals($policies, $capability->getPolicies());
    }

    /**
     * @test
     * @group capability-policies
     */
    public function test_can_store_policy_definitions(): void
    {
        $capability = $this->createValidCapability();
        $capability->setPolicies([
            ['policyId' => 'policy-001', 'description' => 'Data retention policy'],
            ['policyId' => 'policy-002', 'description' => 'Access control policy'],
        ]);

        $policies = $capability->getPolicies();
        $this->assertCount(2, $policies);
    }

    // ========================================
    // EVENTS TESTS
    // ========================================

    /**
     * @test
     * @group capability-events
     */
    public function test_events_getter_setter(): void
    {
        $capability = $this->createValidCapability();
        $events = [
            'published' => [['eventId' => 'evt-1', 'name' => 'EmployeeSearched']],
            'consumed' => [['eventId' => 'evt-2', 'name' => 'EmployeeUpdated']],
        ];
        $capability->setEvents($events);

        $this->assertEquals($events, $capability->getEvents());
    }

    /**
     * @test
     * @group capability-events
     */
    public function test_can_store_event_definitions(): void
    {
        $capability = $this->createValidCapability();
        $capability->setEvents([
            'published' => [
                ['eventId' => 'employee.searched', 'description' => 'Employee search completed'],
            ],
            'consumed' => [
                ['eventId' => 'employee.created', 'description' => 'New employee created'],
            ],
        ]);

        $events = $capability->getEvents();
        $this->assertNotEmpty($events);
    }

    // ========================================
    // SERIALIZATION TESTS
    // ========================================

    /**
     * @test
     * @group capability-serialization
     */
    public function test_to_array_returns_all_fields(): void
    {
        $capability = $this->createValidCapability();
        $capability->setKpis([['name' => 'Efficiency', 'target' => 95]]);

        $array = $capability->toArray();

        $this->assertArrayHasKey('capability_id', $array);
        $this->assertArrayHasKey('capability_name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('domain_id', $array);
        $this->assertArrayHasKey('business_owner', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('business_outcome', $array);
        $this->assertArrayHasKey('kpis', $array);
        $this->assertArrayHasKey('entity_id', $array);
        $this->assertArrayHasKey('entity_type', $array);
    }

    /**
     * @test
     * @group capability-serialization
     */
    public function test_to_json_returns_valid_json_string(): void
    {
        $capability = $this->createValidCapability();

        $json = $capability->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('emp-search', $decoded['capability_id']);
    }

    /**
     * @test
     * @group capability-serialization
     */
    public function test_json_serializable_works(): void
    {
        $capability = $this->createValidCapability();

        $json = json_encode($capability);

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('emp-search', $decoded['capability_id']);
    }

    /**
     * @test
     * @group capability-serialization
     */
    public function test_timestamps_in_iso_8601_format(): void
    {
        $capability = $this->createValidCapability();

        $array = $capability->toArray();

        $this->assertNotNull($array['created_at']);
        // Verify ISO 8601 format (YYYY-MM-DDTHH:MM:SS+00:00 or similar)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $array['created_at']);
    }

    // ========================================
    // VALIDATION TESTS
    // ========================================

    /**
     * @test
     * @group capability-validation
     */
    public function test_valid_capability_passes_validation(): void
    {
        $capability = $this->createValidCapability();

        // No exceptions thrown
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group capability-validation
     */
    public function test_missing_capability_id_fails_validation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capability ID is required');

        $capability = new BusinessCapability();
        
        $reflection = new \ReflectionClass($capability);
        
        // Set all fields except capabilityId (which is empty by default)
        $reflection->getProperty('capabilityName')->setValue($capability, 'Employee Search');
        $reflection->getProperty('description')->setValue($capability, 'Search employees');
        $reflection->getProperty('domainId')->setValue($capability, 'EMP_MGMT');
        $reflection->getProperty('businessOwner')->setValue($capability, 'hr-manager-001');
        $reflection->getProperty('status')->setValue($capability, BusinessCapability::IMPLEMENT);
        $reflection->getProperty('businessOutcome')->setValue($capability, 'Find employees quickly');
        
        // Try to initialize - should fail because capabilityId is empty
        $initMethod = $reflection->getMethod('initializeCapability');
        $initMethod->invoke($capability);
    }

    /**
     * @test
     * @group capability-validation
     */
    public function test_missing_required_fields_fail_validation(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $capability = new BusinessCapability();
        
        $reflection = new \ReflectionClass($capability);
        
        // Set only capabilityId, leave others empty
        $reflection->getProperty('capabilityId')->setValue($capability, 'emp-search');
        // capabilityName is empty by default
        
        // Try to initialize - should fail
        $initMethod = $reflection->getMethod('initializeCapability');
        $initMethod->invoke($capability);
    }

    // ========================================
    // VERSION CONSISTENCY TESTS
    // ========================================

    /**
     * @test
     * @group capability-version
     */
    public function test_entity_version_inherited_from_base_model(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals(1, $capability->getEntityVersion());
    }

    /**
     * @test
     * @group capability-version
     */
    public function test_entity_type_set_to_capability(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals('Capability', $capability->getEntityType());
    }

    /**
     * @test
     * @group capability-version
     */
    public function test_version_tracked_by_base_model(): void
    {
        $capability = $this->createValidCapability();

        $version = $capability->getEntityVersion();
        $this->assertIsInt($version);
        $this->assertGreaterThanOrEqual(1, $version);
    }

    // ========================================
    // FRAMEWORK INDEPENDENCE TESTS
    // ========================================

    /**
     * @test
     * @group capability-framework-independence
     */
    public function test_no_laravel_objects_in_to_array(): void
    {
        $capability = $this->createValidCapability();

        $array = $capability->toArray();

        // Verify all values are primitives or arrays
        foreach ($array as $key => $value) {
            $this->assertTrue(
                is_string($value) || is_int($value) || is_bool($value) || is_null($value) || is_array($value),
                "Key '{$key}' contains non-primitive type: " . gettype($value)
            );
        }
    }

    /**
     * @test
     * @group capability-framework-independence
     */
    public function test_serialization_produces_pure_json(): void
    {
        $capability = $this->createValidCapability();

        $json = $capability->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertEquals('emp-search', $decoded['capability_id']);
        $this->assertEquals('Employee Search', $decoded['capability_name']);
    }

    // ========================================
    // DOMAIN HIERARCHY TESTS
    // ========================================

    /**
     * @test
     * @group capability-hierarchy
     */
    public function test_capability_remains_in_domain_hierarchy(): void
    {
        $capability = $this->createValidCapability();

        // Capability belongs to exactly one Domain
        $this->assertEquals('EMP_MGMT', $capability->getDomainId());
    }

    /**
     * @test
     * @group capability-hierarchy
     */
    public function test_parent_domain_reference_preserved(): void
    {
        $capability = $this->createValidCapability();

        $this->assertEquals('EMP_MGMT', $capability->getDomainId());
    }

    // ========================================
    // STRING REPRESENTATION TESTS
    // ========================================

    /**
     * @test
     * @group capability-string
     */
    public function test_string_representation(): void
    {
        $capability = $this->createValidCapability();

        $str = $capability->__toString();
        $this->assertStringContainsString('Capability(emp-search', $str);
        $this->assertStringContainsString('Employee Search', $str);
    }
}
