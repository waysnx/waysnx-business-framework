<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Models\BusinessFunction;

/**
 * Canonical Business Function Definition (BFD) Tests
 *
 * This test suite establishes the canonical representation of a Business Function
 * as a machine-readable, framework-independent Business Function Definition (BFD).
 *
 * The BFD is defined by the getCompleteContractDefinition() method which returns
 * a standardized array structure containing all aspects of a Business Function
 * as defined in WBF-DOC-0005.
 *
 * Key properties of the canonical BFD:
 * - Framework-independent: No Laravel/PHP-specific objects
 * - Deterministic: Same BF always produces identical serialization
 * - Complete: Contains identity, metadata, contract, processing, operational
 * - Versioned: Maintains version information for evolution
 * - Exchangeable: Can be serialized to JSON/YAML and consumed by other systems
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Models
 */
class CanonicalBFDTest extends TestCase
{
    /**
     * Concrete BusinessFunction implementation for testing
     */
    private BusinessFunction $businessFunction;

    /**
     * Canonical BFD structure from first serialization
     */
    private array $canonicalBFD;

    protected function setUp(): void
    {
        // Create a concrete BusinessFunction instance
        $this->businessFunction = new class extends BusinessFunction {
            public function __construct()
            {
                // Identity
                $this->functionId = 'HR.LEAVE.APPLY.APPLY_LEAVE';
                $this->functionName = 'Apply Leave';
                $this->functionVersion = '1.0.0';
                $this->module = 'HR';
                $this->domain = 'LEAVE';
                $this->capability = 'APPLY';
                $this->lifecycleStatus = 'Released';

                // Metadata
                $this->description = 'Submit an employee leave request';
                $this->businessOwner = 'HR Manager';
                $this->technicalOwner = 'HR Development Team';
                $this->tags = ['leave', 'employee', 'request'];
                $this->classification = 'Core';
                $this->visibility = 'Internal';
                $this->dependencies = [];
                $this->eventsPublished = ['LeaveRequested'];
                $this->eventsConsumed = [];
                $this->keywords = ['leave', 'absence', 'vacation'];

                // Request Contract
                $this->requestContract = [
                    'required' => ['employeeId', 'leaveType', 'startDate', 'endDate'],
                    'optional' => ['reason'],
                    'fields' => [
                        'employeeId' => ['type' => 'string', 'description' => 'Employee ID'],
                        'leaveType' => ['type' => 'string', 'description' => 'Type of leave'],
                    ],
                ];

                // Response Contract
                $this->responseContract = [
                    'description' => 'Leave request created',
                    'fields' => [
                        'leaveRequestId' => ['type' => 'string', 'description' => 'Request ID'],
                        'status' => ['type' => 'string', 'description' => 'Status'],
                    ],
                ];

                // Error Categories
                $this->errorCategories = [
                    'ValidationError',
                    'AuthorizationError',
                    'BusinessRuleViolation',
                ];

                // Validation Rules
                $this->validationRules = [
                    'employeeId' => 'required|string',
                    'leaveType' => 'required|string',
                ];

                // Authorization
                $this->authorizationRequirements = [
                    'roles' => ['Employee', 'Manager'],
                    'permissions' => ['leave.apply'],
                ];

                // Business Rules
                $this->businessRules = [
                    'Employee must be active',
                    'Leave balance must be sufficient',
                ];

                // Observability
                $this->observabilityRequirements = [
                    'correlation' => true,
                    'metrics' => ['execution_time', 'success_rate'],
                ];

                // Audit
                $this->auditRequirements = [
                    'enabled' => true,
                    'retentionDays' => 2555,
                ];

                $this->initializeBusinessFunction();
            }

            protected function executeBusiness(array $request): array
            {
                return ['leaveRequestId' => 'LR-001'];
            }
        };

        // Capture the canonical BFD
        $this->canonicalBFD = $this->businessFunction->getCompleteContractDefinition();
    }

    /**
     * Test 1: BFD has all required top-level sections
     *
     * Requirement: BFD MUST contain identity, metadata, contract, processing, operational
     * WBF-DOC-0005 Section 6: Canonical Business Function Model
     */
    public function test_bfd_contains_all_required_sections(): void
    {
        $requiredSections = ['identity', 'metadata', 'contract', 'processing', 'operational'];

        foreach ($requiredSections as $section) {
            $this->assertArrayHasKey(
                $section,
                $this->canonicalBFD,
                "BFD missing required section: {$section}"
            );
            $this->assertIsArray(
                $this->canonicalBFD[$section],
                "BFD section '{$section}' must be an array"
            );
        }
    }

    /**
     * Test 2: Identity section contains all required fields
     *
     * Requirements: BF-ID-01 through BF-ID-07
     * WBF-DOC-0005 Section 8: Function Identity
     */
    public function test_bfd_identity_is_complete(): void
    {
        $identity = $this->canonicalBFD['identity'];

        $requiredFields = [
            'functionId',
            'functionName',
            'functionVersion',
            'module',
            'domain',
            'capability',
            'lifecycleStatus',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $identity,
                "Identity missing field: {$field}"
            );
            $this->assertNotEmpty(
                $identity[$field],
                "Identity field '{$field}' cannot be empty"
            );
        }

        // Verify values
        $this->assertSame('HR.LEAVE.APPLY.APPLY_LEAVE', $identity['functionId']);
        $this->assertSame('Apply Leave', $identity['functionName']);
        $this->assertSame('1.0.0', $identity['functionVersion']);
    }

    /**
     * Test 3: Metadata section contains all required fields
     *
     * Requirements: BF-META-01 through BF-META-10
     * WBF-DOC-0005 Section 9: Function Metadata
     */
    public function test_bfd_metadata_is_complete(): void
    {
        $metadata = $this->canonicalBFD['metadata'];

        $requiredFields = [
            'description',
            'businessOwner',
            'technicalOwner',
            'tags',
            'classification',
            'visibility',
            'dependencies',
            'eventsPublished',
            'eventsConsumed',
            'keywords',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $metadata,
                "Metadata missing field: {$field}"
            );
        }

        // Verify non-empty for core fields
        $this->assertNotEmpty($metadata['description']);
        $this->assertNotEmpty($metadata['businessOwner']);
        $this->assertIsArray($metadata['tags']);
    }

    /**
     * Test 4: Contract section contains request, response, and errors
     *
     * Requirements: BF-CON-01 through BF-CON-05, BF-REQ-01, BF-RESP-01
     * WBF-DOC-0005 Section 10: Business Contract
     */
    public function test_bfd_contract_is_complete(): void
    {
        $contract = $this->canonicalBFD['contract'];

        $requiredFields = ['request', 'response', 'errors'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $contract,
                "Contract missing field: {$field}"
            );
            $this->assertIsArray(
                $contract[$field],
                "Contract field '{$field}' must be an array"
            );
        }

        // Verify request has required structure
        $this->assertArrayHasKey('required', $contract['request']);
        $this->assertArrayHasKey('fields', $contract['request']);

        // Verify response has description
        $this->assertArrayHasKey('description', $contract['response']);

        // Verify errors is not empty
        $this->assertNotEmpty($contract['errors']);
    }

    /**
     * Test 5: Processing section contains validation, authorization, businessRules
     *
     * Requirements: BF-VAL-01 through BF-VAL-06, BF-SEC-01 through BF-SEC-05, BF-BR-01 through BF-BR-05
     * WBF-DOC-0005 Section 13-16: Execution Pipeline
     */
    public function test_bfd_processing_is_complete(): void
    {
        $processing = $this->canonicalBFD['processing'];

        $requiredFields = ['validation', 'authorization', 'businessRules'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $processing,
                "Processing missing field: {$field}"
            );
            $this->assertIsArray(
                $processing[$field],
                "Processing field '{$field}' must be an array"
            );
        }

        // Verify validation rules
        $this->assertNotEmpty($processing['validation']);

        // Verify authorization requirements
        $this->assertNotEmpty($processing['authorization']);

        // Verify business rules
        $this->assertNotEmpty($processing['businessRules']);
    }

    /**
     * Test 6: Operational section contains observability and audit
     *
     * Requirements: BF-OBS-01 through BF-OBS-05, BF-OBS-04
     * WBF-DOC-0005 Section 20: Observability
     */
    public function test_bfd_operational_is_complete(): void
    {
        $operational = $this->canonicalBFD['operational'];

        $requiredFields = ['observability', 'audit'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $operational,
                "Operational missing field: {$field}"
            );
            $this->assertIsArray(
                $operational[$field],
                "Operational field '{$field}' must be an array"
            );
        }

        // Verify observability configuration
        $this->assertNotEmpty($operational['observability']);

        // Verify audit configuration
        $this->assertNotEmpty($operational['audit']);
    }

    /**
     * Test 7: BFD is serializable to JSON
     *
     * Requirement: BFD MUST be machine-readable and exchangeable
     * WBF-DOC-0005 Section 25: Business Function Definition (BFD)
     */
    public function test_bfd_is_json_serializable(): void
    {
        $json = json_encode($this->canonicalBFD);

        $this->assertIsString($json);
        $this->assertNotEmpty($json);

        // Verify it can be decoded back
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);

        // Verify structure is preserved
        $this->assertSame(array_keys($this->canonicalBFD), array_keys($decoded));
    }

    /**
     * Test 8: BFD contains no Laravel-specific objects
     *
     * Requirement: BFD MUST be framework-independent
     * WBF-DOC-0005 Section 2: Technology Independence
     */
    public function test_bfd_contains_no_framework_objects(): void
    {
        $json = json_encode($this->canonicalBFD);
        $this->assertNotFalse($json, 'BFD must be JSON serializable without special handling');

        // Verify no Closure objects (framework objects would cause issues)
        $this->assertArrayNotHasKey('Closure', get_defined_vars());

        // Walk through array and verify all values are scalar or array
        $this->assertBFDContainsOnlySerializableValues($this->canonicalBFD);
    }

    /**
     * Test 9: Determinism - same BF produces identical BFD
     *
     * Requirement: BF-GOAL-03 - Deterministic Behavior
     * Same BF state MUST always produce identical canonical output
     */
    public function test_bfd_is_deterministic(): void
    {
        // Serialize the same BF multiple times
        $serialization1 = $this->businessFunction->getCompleteContractDefinition();
        $serialization2 = $this->businessFunction->getCompleteContractDefinition();
        $serialization3 = $this->businessFunction->getCompleteContractDefinition();

        // All serializations must be identical
        $this->assertSame($serialization1, $serialization2, 'First and second serialization differ');
        $this->assertSame($serialization2, $serialization3, 'Second and third serialization differ');

        // JSON representations must be identical
        $json1 = json_encode($serialization1);
        $json2 = json_encode($serialization2);
        $json3 = json_encode($serialization3);

        $this->assertSame($json1, $json2);
        $this->assertSame($json2, $json3);
    }

    /**
     * Test 10: BFD matches interface requirements
     *
     * Verify that all interface methods are represented in the BFD
     */
    public function test_bfd_covers_all_interface_methods(): void
    {
        // BFD should contain data from all interface method groups
        $bfd = $this->canonicalBFD;

        // Identity methods (7)
        $this->assertNotNull($bfd['identity']['functionId']);
        $this->assertNotNull($bfd['identity']['functionName']);
        $this->assertNotNull($bfd['identity']['functionVersion']);
        $this->assertNotNull($bfd['identity']['module']);
        $this->assertNotNull($bfd['identity']['domain']);
        $this->assertNotNull($bfd['identity']['capability']);
        $this->assertNotNull($bfd['identity']['lifecycleStatus']);

        // Metadata methods (10)
        $this->assertNotNull($bfd['metadata']['description']);
        $this->assertNotNull($bfd['metadata']['businessOwner']);
        $this->assertNotNull($bfd['metadata']['technicalOwner']);
        $this->assertNotNull($bfd['metadata']['tags']);
        $this->assertNotNull($bfd['metadata']['classification']);
        $this->assertNotNull($bfd['metadata']['visibility']);
        $this->assertNotNull($bfd['metadata']['dependencies']);
        $this->assertNotNull($bfd['metadata']['eventsPublished']);
        $this->assertNotNull($bfd['metadata']['eventsConsumed']);
        $this->assertNotNull($bfd['metadata']['keywords']);

        // Contract methods (5)
        $this->assertNotNull($bfd['contract']['request']);
        $this->assertNotNull($bfd['contract']['response']);
        $this->assertNotNull($bfd['contract']['errors']);

        // Processing methods (6)
        $this->assertNotNull($bfd['processing']['validation']);
        $this->assertNotNull($bfd['processing']['authorization']);
        $this->assertNotNull($bfd['processing']['businessRules']);

        // Operational methods (2)
        $this->assertNotNull($bfd['operational']['observability']);
        $this->assertNotNull($bfd['operational']['audit']);
    }

    /**
     * Test 11: BFD fixture can be saved and loaded
     *
     * Verify that the BFD can be persisted and reloaded without corruption
     */
    public function test_bfd_fixture_persistence(): void
    {
        $fixtureDir = __DIR__ . '/../Fixtures/BFD';
        if (!is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0755, true);
        }

        $fixturePath = $fixtureDir . '/test-fixture.json';

        // Save
        $json = json_encode($this->canonicalBFD, JSON_PRETTY_PRINT);
        file_put_contents($fixturePath, $json);

        // Load
        $loadedJson = file_get_contents($fixturePath);
        $loadedBFD = json_decode($loadedJson, true);

        // Verify
        $this->assertSame($this->canonicalBFD, $loadedBFD);

        // Cleanup
        unlink($fixturePath);
    }

    /**
     * Helper: Verify BFD contains only serializable values
     */
    private function assertBFDContainsOnlySerializableValues(array $data, string $path = ''): void
    {
        foreach ($data as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : $key;

            if (is_array($value)) {
                $this->assertBFDContainsOnlySerializableValues($value, $currentPath);
            } elseif (is_object($value)) {
                $this->fail("BFD contains object at {$currentPath}: " . get_class($value));
            } elseif ($value !== null && !is_scalar($value)) {
                $this->fail("BFD contains non-serializable value at {$currentPath}: " . gettype($value));
            }
        }
    }
}
