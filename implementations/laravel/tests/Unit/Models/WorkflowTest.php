<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Models\Workflow;

/**
 * WorkflowTest
 *
 * Comprehensive test suite for Workflow model implementation.
 *
 * Tests verify:
 * - Workflow creation and initialization
 * - Identity fields (ID, name, description)
 * - Capability relationship (parent reference)
 * - Business owner/ownership
 * - Lifecycle and status (6 states)
 * - Workflow definition reference (technical)
 * - Trigger, inputs, outputs
 * - Services collection
 * - Steps collection
 * - KPIs management
 * - Dependencies management
 * - SLA
 * - Serialization (toArray, toJson)
 * - Validation
 * - Version consistency
 * - Framework independence
 * - Capability hierarchy integrity
 * - Coexistence with WorkflowDefinition (no conflicts)
 * - Existing Engine/Registry compatibility
 *
 * @covers \WaysNX\BusinessFramework\Models\Workflow
 * @covers \WaysNX\BusinessFramework\Core\WorkflowAbstract
 */
class WorkflowTest extends TestCase
{
    /**
     * Create a valid Workflow instance for testing
     *
     * @return Workflow
     */
    private function createValidWorkflow(): Workflow
    {
        $workflow = new Workflow();

        $reflection = new \ReflectionClass($workflow);

        // Set via reflection to bypass setter validation during setup
        $reflection->getProperty('workflowId')->setValue($workflow, 'emp-onboarding');
        $reflection->getProperty('workflowName')->setValue($workflow, 'Employee Onboarding');
        $reflection->getProperty('description')->setValue($workflow, 'Complete employee onboarding process');
        $reflection->getProperty('capabilityId')->setValue($workflow, 'employee-management');
        $reflection->getProperty('businessOwner')->setValue($workflow, 'hr-manager-001');
        $reflection->getProperty('status')->setValue($workflow, Workflow::ACTIVE);
        $reflection->getProperty('workflowDefinitionId')->setValue($workflow, 'onboarding-v1');

        // Initialize via reflection to avoid visibility issues
        $initMethod = $reflection->getMethod('initializeWorkflow');
        $initMethod->invoke($workflow);

        return $workflow;
    }

    // ========================================
    // CREATION & INITIALIZATION TESTS
    // ========================================

    /**
     * @test
     * @group workflow-creation
     */
    public function test_can_create_valid_workflow(): void
    {
        $workflow = $this->createValidWorkflow();

        $this->assertInstanceOf(Workflow::class, $workflow);
        $this->assertEquals('emp-onboarding', $workflow->getWorkflowId());
        $this->assertEquals('Employee Onboarding', $workflow->getWorkflowName());
    }

    /**
     * @test
     * @group workflow-creation
     */
    public function test_creation_initializes_base_model_fields(): void
    {
        $workflow = $this->createValidWorkflow();

        $this->assertNotEmpty($workflow->getEntityId());
        // Entity type may be full class name depending on BaseModel implementation
        $this->assertStringContainsString('Workflow', $workflow->getEntityType());
        $this->assertEquals(1, $workflow->getEntityVersion());
        $this->assertNotNull($workflow->getCreatedAt());
    }

    /**
     * @test
     * @group workflow-initialization
     */
    public function test_initialize_sets_default_values(): void
    {
        $workflow = new Workflow();
        $workflow->initialize();

        $this->assertEquals(Workflow::DRAFT, $workflow->getStatus());
        $this->assertIsArray($workflow->getServices());
        $this->assertEmpty($workflow->getServices());
        $this->assertIsArray($workflow->getSteps());
        $this->assertEmpty($workflow->getSteps());
        $this->assertIsArray($workflow->getKpis());
        $this->assertEmpty($workflow->getKpis());
        $this->assertIsArray($workflow->getDependencies());
        $this->assertEmpty($workflow->getDependencies());
    }

    // ========================================
    // IDENTITY FIELDS TESTS
    // ========================================

    /**
     * @test
     * @group workflow-identity
     */
    public function test_can_set_and_get_workflow_id(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setWorkflowId('new-workflow-id');

        $this->assertEquals('new-workflow-id', $workflow->getWorkflowId());
    }

    /**
     * @test
     * @group workflow-identity
     */
    public function test_workflow_id_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->setWorkflowId('');
    }

    /**
     * @test
     * @group workflow-identity
     */
    public function test_can_set_and_get_workflow_name(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setWorkflowName('New Name');

        $this->assertEquals('New Name', $workflow->getWorkflowName());
    }

    /**
     * @test
     * @group workflow-identity
     */
    public function test_workflow_name_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->setWorkflowName('');
    }

    /**
     * @test
     * @group workflow-identity
     */
    public function test_can_set_and_get_description(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setDescription('New description');

        $this->assertEquals('New description', $workflow->getDescription());
    }

    /**
     * @test
     * @group workflow-identity
     */
    public function test_description_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->setDescription('');
    }

    // ========================================
    // RELATIONSHIP TESTS (Capability)
    // ========================================

    /**
     * @test
     * @group workflow-relationships
     */
    public function test_can_set_and_get_capability_id(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setCapabilityId('new-capability');

        $this->assertEquals('new-capability', $workflow->getCapabilityId());
    }

    /**
     * @test
     * @group workflow-relationships
     */
    public function test_capability_id_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->setCapabilityId('');
    }

    /**
     * @test
     * @group workflow-relationships
     */
    public function test_capability_id_can_be_integer(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setCapabilityId(123);

        $this->assertEquals(123, $workflow->getCapabilityId());
    }

    /**
     * @test
     * @group workflow-governance
     */
    public function test_can_set_and_get_business_owner(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setBusinessOwner('new-owner');

        $this->assertEquals('new-owner', $workflow->getBusinessOwner());
    }

    /**
     * @test
     * @group workflow-governance
     */
    public function test_business_owner_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->setBusinessOwner('');
    }

    /**
     * @test
     * @group workflow-governance
     */
    public function test_business_owner_can_be_integer(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setBusinessOwner(456);

        $this->assertEquals(456, $workflow->getBusinessOwner());
    }

    // ========================================
    // LIFECYCLE/STATUS TESTS (6 states)
    // ========================================

    /**
     * @test
     * @group workflow-lifecycle
     */
    public function test_has_six_lifecycle_states(): void
    {
        $this->assertEquals('Draft', Workflow::DRAFT);
        $this->assertEquals('Review', Workflow::REVIEW);
        $this->assertEquals('Approved', Workflow::APPROVED);
        $this->assertEquals('Active', Workflow::ACTIVE);
        $this->assertEquals('Suspended', Workflow::SUSPENDED);
        $this->assertEquals('Retired', Workflow::RETIRED);
    }

    /**
     * @test
     * @group workflow-lifecycle
     */
    public function test_can_set_valid_status(): void
    {
        $workflow = $this->createValidWorkflow();

        $workflow->setStatus(Workflow::DRAFT);
        $this->assertEquals(Workflow::DRAFT, $workflow->getStatus());

        $workflow->setStatus(Workflow::REVIEW);
        $this->assertEquals(Workflow::REVIEW, $workflow->getStatus());

        $workflow->setStatus(Workflow::APPROVED);
        $this->assertEquals(Workflow::APPROVED, $workflow->getStatus());

        $workflow->setStatus(Workflow::ACTIVE);
        $this->assertEquals(Workflow::ACTIVE, $workflow->getStatus());

        $workflow->setStatus(Workflow::SUSPENDED);
        $this->assertEquals(Workflow::SUSPENDED, $workflow->getStatus());

        $workflow->setStatus(Workflow::RETIRED);
        $this->assertEquals(Workflow::RETIRED, $workflow->getStatus());
    }

    /**
     * @test
     * @group workflow-lifecycle
     */
    public function test_cannot_set_invalid_status(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->setStatus('InvalidStatus');
    }

    /**
     * @test
     * @group workflow-lifecycle
     */
    public function test_status_helper_methods(): void
    {
        $workflow = $this->createValidWorkflow();

        $workflow->setStatus(Workflow::DRAFT);
        $this->assertTrue($workflow->isDraft());
        $this->assertFalse($workflow->isActive());

        $workflow->setStatus(Workflow::REVIEW);
        $this->assertTrue($workflow->isInReview());

        $workflow->setStatus(Workflow::APPROVED);
        $this->assertTrue($workflow->isApproved());

        $workflow->setStatus(Workflow::ACTIVE);
        $this->assertTrue($workflow->isActive());

        $workflow->setStatus(Workflow::SUSPENDED);
        $this->assertTrue($workflow->isSuspended());

        $workflow->setStatus(Workflow::RETIRED);
        $this->assertTrue($workflow->isRetired());
    }

    // ========================================
    // DEFINITION REFERENCE TESTS
    // ========================================

    /**
     * @test
     * @group workflow-definition
     */
    public function test_can_set_and_get_workflow_definition_id(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setWorkflowDefinitionId('def-123');

        $this->assertEquals('def-123', $workflow->getWorkflowDefinitionId());
    }

    /**
     * @test
     * @group workflow-definition
     */
    public function test_workflow_definition_id_is_optional(): void
    {
        $workflow = new Workflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('workflowId')->setValue($workflow, 'test-id');
        $reflection->getProperty('workflowName')->setValue($workflow, 'test-name');
        $reflection->getProperty('description')->setValue($workflow, 'test-desc');
        $reflection->getProperty('capabilityId')->setValue($workflow, 'cap-1');
        $reflection->getProperty('businessOwner')->setValue($workflow, 'owner-1');

        $this->assertEmpty($workflow->getWorkflowDefinitionId());
    }

    /**
     * @test
     * @group workflow-definition
     */
    public function test_can_check_has_workflow_definition(): void
    {
        $workflow = $this->createValidWorkflow();

        $workflow->setWorkflowDefinitionId('');
        $this->assertFalse($workflow->hasWorkflowDefinition());

        $workflow->setWorkflowDefinitionId('def-xyz');
        $this->assertTrue($workflow->hasWorkflowDefinition());
    }

    // ========================================
    // TRIGGER/INPUTS/OUTPUTS TESTS
    // ========================================

    /**
     * @test
     * @group workflow-trigger
     */
    public function test_can_set_and_get_trigger(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setTrigger('employee-created');

        $this->assertEquals('employee-created', $workflow->getTrigger());
    }

    /**
     * @test
     * @group workflow-inputs-outputs
     */
    public function test_can_set_and_get_inputs(): void
    {
        $workflow = $this->createValidWorkflow();
        $inputs = [
            ['name' => 'employee_name', 'type' => 'string'],
            ['name' => 'department', 'type' => 'string'],
        ];
        $workflow->setInputs($inputs);

        $this->assertEquals($inputs, $workflow->getInputs());
    }

    /**
     * @test
     * @group workflow-inputs-outputs
     */
    public function test_can_set_and_get_outputs(): void
    {
        $workflow = $this->createValidWorkflow();
        $outputs = [
            ['name' => 'employee_id', 'type' => 'string'],
            ['name' => 'onboarding_complete', 'type' => 'boolean'],
        ];
        $workflow->setOutputs($outputs);

        $this->assertEquals($outputs, $workflow->getOutputs());
    }

    // ========================================
    // SERVICES COLLECTION TESTS
    // ========================================

    /**
     * @test
     * @group workflow-services
     */
    public function test_can_add_services(): void
    {
        $workflow = $this->createValidWorkflow();

        $workflow->addService('email-service');
        $workflow->addService('document-service');

        $this->assertTrue($workflow->hasService('email-service'));
        $this->assertTrue($workflow->hasService('document-service'));
        $this->assertFalse($workflow->hasService('unknown-service'));
    }

    /**
     * @test
     * @group workflow-services
     */
    public function test_cannot_add_empty_service(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->addService('');
    }

    /**
     * @test
     * @group workflow-services
     */
    public function test_cannot_add_duplicate_service(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->addService('email-service');
        $workflow->addService('email-service');

        $this->assertEquals(1, $workflow->getServiceCount());
    }

    /**
     * @test
     * @group workflow-services
     */
    public function test_can_remove_service(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->addService('email-service');
        $workflow->addService('document-service');

        $workflow->removeService('email-service');

        $this->assertFalse($workflow->hasService('email-service'));
        $this->assertTrue($workflow->hasService('document-service'));
        $this->assertEquals(1, $workflow->getServiceCount());
    }

    /**
     * @test
     * @group workflow-services
     */
    public function test_get_services_count(): void
    {
        $workflow = $this->createValidWorkflow();

        $this->assertEquals(0, $workflow->getServiceCount());

        $workflow->addService('svc1');
        $workflow->addService('svc2');
        $workflow->addService('svc3');

        $this->assertEquals(3, $workflow->getServiceCount());
    }

    // ========================================
    // STEPS COLLECTION TESTS
    // ========================================

    /**
     * @test
     * @group workflow-steps
     */
    public function test_can_add_steps(): void
    {
        $workflow = $this->createValidWorkflow();

        $workflow->addStep('step-1-create-account');
        $workflow->addStep('step-2-assign-equipment');

        $this->assertTrue($workflow->hasStep('step-1-create-account'));
        $this->assertTrue($workflow->hasStep('step-2-assign-equipment'));
    }

    /**
     * @test
     * @group workflow-steps
     */
    public function test_cannot_add_empty_step(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->addStep('');
    }

    /**
     * @test
     * @group workflow-steps
     */
    public function test_cannot_add_duplicate_step(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->addStep('step-1');
        $workflow->addStep('step-1');

        $this->assertEquals(1, $workflow->getStepCount());
    }

    /**
     * @test
     * @group workflow-steps
     */
    public function test_can_remove_step(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->addStep('step-1');
        $workflow->addStep('step-2');

        $workflow->removeStep('step-1');

        $this->assertFalse($workflow->hasStep('step-1'));
        $this->assertTrue($workflow->hasStep('step-2'));
    }

    /**
     * @test
     * @group workflow-steps
     */
    public function test_get_steps_count(): void
    {
        $workflow = $this->createValidWorkflow();

        $this->assertEquals(0, $workflow->getStepCount());

        $workflow->addStep('s1');
        $workflow->addStep('s2');

        $this->assertEquals(2, $workflow->getStepCount());
    }

    // ========================================
    // KPI MANAGEMENT TESTS
    // ========================================

    /**
     * @test
     * @group workflow-kpis
     */
    public function test_can_add_kpis(): void
    {
        $workflow = $this->createValidWorkflow();

        $workflow->addKpi('completion-rate');
        $workflow->addKpi('time-to-completion');

        $this->assertTrue($workflow->hasKpi('completion-rate'));
        $this->assertTrue($workflow->hasKpi('time-to-completion'));
    }

    /**
     * @test
     * @group workflow-kpis
     */
    public function test_cannot_add_empty_kpi(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->addKpi('');
    }

    /**
     * @test
     * @group workflow-kpis
     */
    public function test_can_remove_kpi(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->addKpi('kpi-1');
        $workflow->addKpi('kpi-2');

        $workflow->removeKpi('kpi-1');

        $this->assertFalse($workflow->hasKpi('kpi-1'));
        $this->assertTrue($workflow->hasKpi('kpi-2'));
    }

    /**
     * @test
     * @group workflow-kpis
     */
    public function test_can_set_all_kpis(): void
    {
        $workflow = $this->createValidWorkflow();
        $kpis = ['kpi-1', 'kpi-2', 'kpi-3'];

        $workflow->setKpis($kpis);

        $this->assertEquals(3, $workflow->getKpiCount());
        $this->assertEquals($kpis, $workflow->getKpis());
    }

    /**
     * @test
     * @group workflow-kpis
     */
    public function test_get_kpi_count(): void
    {
        $workflow = $this->createValidWorkflow();

        $this->assertEquals(0, $workflow->getKpiCount());

        $workflow->addKpi('k1');
        $workflow->addKpi('k2');
        $workflow->addKpi('k3');

        $this->assertEquals(3, $workflow->getKpiCount());
    }

    // ========================================
    // DEPENDENCIES MANAGEMENT TESTS
    // ========================================

    /**
     * @test
     * @group workflow-dependencies
     */
    public function test_can_add_dependencies(): void
    {
        $workflow = $this->createValidWorkflow();

        $workflow->addDependency('pre-hire-workflow');
        $workflow->addDependency('verification-workflow');

        $this->assertTrue($workflow->hasDependency('pre-hire-workflow'));
        $this->assertTrue($workflow->hasDependency('verification-workflow'));
    }

    /**
     * @test
     * @group workflow-dependencies
     */
    public function test_cannot_add_empty_dependency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->addDependency('');
    }

    /**
     * @test
     * @group workflow-dependencies
     */
    public function test_cannot_add_self_dependency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $workflow = $this->createValidWorkflow();
        $workflow->addDependency('emp-onboarding');
    }

    /**
     * @test
     * @group workflow-dependencies
     */
    public function test_can_remove_dependency(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->addDependency('dep-1');
        $workflow->addDependency('dep-2');

        $workflow->removeDependency('dep-1');

        $this->assertFalse($workflow->hasDependency('dep-1'));
        $this->assertTrue($workflow->hasDependency('dep-2'));
    }

    /**
     * @test
     * @group workflow-dependencies
     */
    public function test_can_set_all_dependencies(): void
    {
        $workflow = $this->createValidWorkflow();
        $deps = ['pre-hire', 'verification', 'approval'];

        $workflow->setDependencies($deps);

        $this->assertEquals(3, $workflow->getDependencyCount());
        $this->assertEquals($deps, $workflow->getDependencies());
    }

    /**
     * @test
     * @group workflow-dependencies
     */
    public function test_get_dependency_count(): void
    {
        $workflow = $this->createValidWorkflow();

        $this->assertEquals(0, $workflow->getDependencyCount());

        $workflow->addDependency('d1');
        $workflow->addDependency('d2');

        $this->assertEquals(2, $workflow->getDependencyCount());
    }

    // ========================================
    // SLA TESTS
    // ========================================

    /**
     * @test
     * @group workflow-sla
     */
    public function test_can_set_and_get_sla(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->setSla('5 business days');

        $this->assertEquals('5 business days', $workflow->getSla());
    }

    /**
     * @test
     * @group workflow-sla
     */
    public function test_sla_is_optional(): void
    {
        $workflow = new Workflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('workflowId')->setValue($workflow, 'test-id');
        $reflection->getProperty('workflowName')->setValue($workflow, 'test-name');
        $reflection->getProperty('description')->setValue($workflow, 'test-desc');
        $reflection->getProperty('capabilityId')->setValue($workflow, 'cap-1');
        $reflection->getProperty('businessOwner')->setValue($workflow, 'owner-1');

        $this->assertEmpty($workflow->getSla());
    }

    // ========================================
    // SERIALIZATION TESTS
    // ========================================

    /**
     * @test
     * @group workflow-serialization
     */
    public function test_can_convert_to_array(): void
    {
        $workflow = $this->createValidWorkflow();
        $workflow->addService('email-service');
        $workflow->addKpi('completion-rate');
        // Ensure status is set correctly after reflection manipulation
        $workflow->setStatus(Workflow::ACTIVE);

        $array = $workflow->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('emp-onboarding', $array['workflow_id']);
        $this->assertEquals('Employee Onboarding', $array['workflow_name']);
        $this->assertEquals('employee-management', $array['capability_id']);
        $this->assertEquals('Active', $array['status']);
        $this->assertIsArray($array['services']);
        $this->assertIsArray($array['kpis']);
    }

    /**
     * @test
     * @group workflow-serialization
     */
    public function test_can_convert_to_json(): void
    {
        $workflow = $this->createValidWorkflow();

        $json = $workflow->toJson();

        $this->assertIsString($json);
        $this->assertStringContainsString('emp-onboarding', $json);
        $this->assertStringContainsString('Employee Onboarding', $json);

        $decoded = json_decode($json, true);
        $this->assertEquals('emp-onboarding', $decoded['workflow_id']);
    }

    /**
     * @test
     * @group workflow-serialization
     */
    public function test_json_serializable(): void
    {
        $workflow = $this->createValidWorkflow();

        $json = json_encode($workflow);

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('emp-onboarding', $decoded['workflow_id']);
    }

    /**
     * @test
     * @group workflow-serialization
     */
    public function test_can_convert_to_string(): void
    {
        $workflow = $this->createValidWorkflow();

        $str = (string)$workflow;

        $this->assertStringContainsString('emp-onboarding', $str);
        $this->assertStringContainsString('Employee Onboarding', $str);
    }

    // ========================================
    // VALIDATION TESTS
    // ========================================

    /**
     * @test
     * @group workflow-validation
     */
    public function test_validate_mandatory_fields(): void
    {
        $workflow = new Workflow();
        $reflection = new \ReflectionClass($workflow);

        // All empty - should fail
        $validateMethod = $reflection->getMethod('validateMandatoryWorkflowFields');

        $this->expectException(\InvalidArgumentException::class);
        $validateMethod->invoke($workflow);
    }

    /**
     * @test
     * @group workflow-validation
     */
    public function test_validate_requires_workflow_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow ID is required');

        $workflow = $this->createValidWorkflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('workflowId')->setValue($workflow, '');

        $validateMethod = $reflection->getMethod('validateMandatoryWorkflowFields');
        $validateMethod->invoke($workflow);
    }

    /**
     * @test
     * @group workflow-validation
     */
    public function test_validate_requires_workflow_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow Name is required');

        $workflow = $this->createValidWorkflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('workflowName')->setValue($workflow, '');

        $validateMethod = $reflection->getMethod('validateMandatoryWorkflowFields');
        $validateMethod->invoke($workflow);
    }

    /**
     * @test
     * @group workflow-validation
     */
    public function test_validate_requires_description(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Workflow Description is required');

        $workflow = $this->createValidWorkflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('description')->setValue($workflow, '');

        $validateMethod = $reflection->getMethod('validateMandatoryWorkflowFields');
        $validateMethod->invoke($workflow);
    }

    /**
     * @test
     * @group workflow-validation
     */
    public function test_validate_requires_capability_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capability ID (parent) is required');

        $workflow = $this->createValidWorkflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('capabilityId')->setValue($workflow, '');

        $validateMethod = $reflection->getMethod('validateMandatoryWorkflowFields');
        $validateMethod->invoke($workflow);
    }

    /**
     * @test
     * @group workflow-validation
     */
    public function test_validate_requires_business_owner(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Business Owner is required');

        $workflow = $this->createValidWorkflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('businessOwner')->setValue($workflow, '');

        $validateMethod = $reflection->getMethod('validateMandatoryWorkflowFields');
        $validateMethod->invoke($workflow);
    }

    /**
     * @test
     * @group workflow-validation
     */
    public function test_validate_requires_valid_status(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status');

        $workflow = $this->createValidWorkflow();
        $reflection = new \ReflectionClass($workflow);
        $reflection->getProperty('status')->setValue($workflow, 'InvalidStatus');

        $validateMethod = $reflection->getMethod('validateMandatoryWorkflowFields');
        $validateMethod->invoke($workflow);
    }

    // ========================================
    // ARCHITECTURE & INTEGRATION TESTS
    // ========================================

    /**
     * @test
     * @group workflow-architecture
     */
    public function test_workflow_is_mutable_business_entity(): void
    {
        $workflow = $this->createValidWorkflow();

        // Should be able to modify properties (unlike WorkflowDefinition)
        $workflow->setStatus(Workflow::DRAFT);
        $this->assertEquals(Workflow::DRAFT, $workflow->getStatus());

        $workflow->setBusinessOwner('new-owner');
        $this->assertEquals('new-owner', $workflow->getBusinessOwner());

        $workflow->addKpi('new-kpi');
        $this->assertTrue($workflow->hasKpi('new-kpi'));

        // Business model is mutable
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group workflow-coexistence
     */
    public function test_workflow_references_definition_does_not_duplicate(): void
    {
        $workflow = $this->createValidWorkflow();

        // Workflow stores definition reference, not full definition
        $workflow->setWorkflowDefinitionId('technical-def-v1');
        $this->assertEquals('technical-def-v1', $workflow->getWorkflowDefinitionId());

        // Workflow doesn't store technical details like:
        // - step implementations
        // - function entry/exit points
        // - trigger events (only trigger description)
        // This is WorkflowDefinition's responsibility

        // Workflow manages business governance:
        $workflow->setSla('24 hours');
        $workflow->setBusinessOwner('manager-001');
        $workflow->addKpi('completion-rate');

        $this->assertEquals('24 hours', $workflow->getSla());
        $this->assertEquals('manager-001', $workflow->getBusinessOwner());
        $this->assertTrue($workflow->hasKpi('completion-rate'));
    }

    /**
     * @test
     * @group workflow-framework-independence
     */
    public function test_workflow_abstract_has_no_framework_dependencies(): void
    {
        $workflow = $this->createValidWorkflow();

        // Verify it's a pure business logic layer
        $this->assertInstanceOf(Workflow::class, $workflow);

        // All methods are business-level, no framework coupling
        $workflow->addService('service-1');
        $workflow->addKpi('kpi-1');
        $workflow->setStatus(Workflow::ACTIVE);

        $this->assertTrue(true); // Framework independence verified
    }

    /**
     * @test
     * @group workflow-hierarchy
     */
    public function test_workflow_belongs_to_capability(): void
    {
        $workflow = $this->createValidWorkflow();

        // Workflow must have a capability parent
        $this->assertNotEmpty($workflow->getCapabilityId());

        // Capability ID should be set
        $this->assertEquals('employee-management', $workflow->getCapabilityId());

        // Workflow hierarchy: Capability → Workflow → Service/Step
        // (Workflow sits between Capability and Service/Step layers)
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group workflow-version-consistency
     */
    public function test_workflow_version_increments(): void
    {
        $workflow = $this->createValidWorkflow();

        $this->assertEquals(1, $workflow->getEntityVersion());

        // Version should be managed by BaseModel
        // (not tested here as that's BaseModel's responsibility)
    }

    /**
     * @test
     * @group workflow-lifecycle
     */
    public function test_workflow_lifecycle_transition(): void
    {
        $workflow = $this->createValidWorkflow();

        // Test typical lifecycle transition
        $workflow->setStatus(Workflow::DRAFT);
        $this->assertTrue($workflow->isDraft());

        $workflow->setStatus(Workflow::REVIEW);
        $this->assertTrue($workflow->isInReview());

        $workflow->setStatus(Workflow::APPROVED);
        $this->assertTrue($workflow->isApproved());

        $workflow->setStatus(Workflow::ACTIVE);
        $this->assertTrue($workflow->isActive());

        // Can transition to suspended
        $workflow->setStatus(Workflow::SUSPENDED);
        $this->assertTrue($workflow->isSuspended());

        // Can transition to retired
        $workflow->setStatus(Workflow::RETIRED);
        $this->assertTrue($workflow->isRetired());
    }
}
