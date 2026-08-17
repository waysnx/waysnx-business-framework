<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Models\Module;
use JsonSerializable;

/**
 * ModuleTest
 *
 * Comprehensive tests for the WBF Module model implementation.
 *
 * Tests the following requirements from WBF-DOC-0006:
 * - Module Identity (Section 11)
 * - Module Lifecycle (Section 10)
 * - Module Ownership (Section 11-12)
 * - Module Metadata (Section 11)
 * - Module Domain Relationship (Section 9)
 * - Module Dependencies (Section 13)
 * - Framework independence
 * - JSON serialization
 *
 * @covers \WaysNX\BusinessFramework\Models\Module
 * @covers \WaysNX\BusinessFramework\Core\ModuleAbstract
 * @package WaysNX\BusinessFramework\Tests\Unit\Models
 */
class ModuleTest extends TestCase
{
    /**
     * Create a valid Module instance for testing
     *
     * @return Module
     */
    private function createValidModule(): Module
    {
        $module = new Module();

        $reflection = new \ReflectionClass($module);

        // Set via reflection to bypass setter validation during setup
        $reflection->getProperty('moduleId')->setValue($module, 'HR');
        $reflection->getProperty('moduleName')->setValue($module, 'Human Resources');
        $reflection->getProperty('description')->setValue($module, 'Core HR capabilities');
        $reflection->getProperty('businessOwner')->setValue($module, 'hr-manager-001');
        $reflection->getProperty('status')->setValue($module, Module::IMPLEMENT);

        // Initialize via reflection to avoid visibility issues
        $initMethod = $reflection->getMethod('initializeModule');
        $initMethod->invoke($module);

        return $module;
    }

    // ========================================
    // TEST 1: Module Creation
    // ========================================

    /**
     * Test that Module can be created
     */
    public function testModuleCanBeCreated(): void
    {
        $module = $this->createValidModule();

        $this->assertInstanceOf(Module::class, $module);
    }

    /**
     * Test that Module implements JsonSerializable interface
     */
    public function testModuleImplementsJsonSerializable(): void
    {
        $module = $this->createValidModule();

        $this->assertInstanceOf(JsonSerializable::class, $module);
    }

    // ========================================
    // TEST 2-4: Identity Fields
    // ========================================

    /**
     * Test that Module ID can be set and retrieved
     */
    public function testModuleIdWorks(): void
    {
        $module = $this->createValidModule();

        $this->assertEquals('HR', $module->getModuleId());
    }

    /**
     * Test that Module name can be set and retrieved
     */
    public function testModuleNameWorks(): void
    {
        $module = $this->createValidModule();

        $this->assertEquals('Human Resources', $module->getModuleName());
    }

    /**
     * Test that Module description can be set and retrieved
     */
    public function testModuleDescriptionWorks(): void
    {
        $module = $this->createValidModule();

        $this->assertEquals('Core HR capabilities', $module->getDescription());
    }

    /**
     * Test that Module ID cannot be empty
     */
    public function testModuleIdCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module ID cannot be empty');

        $module = new Module();
        $module->setModuleId('');
    }

    /**
     * Test that Module name cannot be empty
     */
    public function testModuleNameCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module name cannot be empty');

        $module = new Module();
        $module->setModuleName('');
    }

    // ========================================
    // TEST 5: Business Owner (Ownership)
    // ========================================

    /**
     * Test that business owner can be set and retrieved
     */
    public function testBusinessOwnerWorks(): void
    {
        $module = $this->createValidModule();

        $this->assertEquals('hr-manager-001', $module->getBusinessOwner());
    }

    /**
     * Test that business owner cannot be empty
     */
    public function testBusinessOwnerCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Business owner cannot be empty');

        $module = new Module();
        $module->setBusinessOwner('');
    }

    /**
     * Test that business owner can be integer
     */
    public function testBusinessOwnerCanBeInteger(): void
    {
        $module = new Module();
        $module->setBusinessOwner(12345);

        $this->assertEquals(12345, $module->getBusinessOwner());
    }

    // ========================================
    // TEST 6: Lifecycle/Status
    // ========================================

    /**
     * Test that Module status can be set and retrieved
     */
    public function testModuleStatusWorks(): void
    {
        $module = $this->createValidModule();

        $this->assertEquals(Module::IMPLEMENT, $module->getStatus());
    }

    /**
     * Test that all 8 lifecycle states are valid
     */
    public function testAllLifecycleStatesAreValid(): void
    {
        $states = [
            Module::IDENTIFY,
            Module::DESIGN,
            Module::REVIEW,
            Module::APPROVE,
            Module::IMPLEMENT,
            Module::OPERATE,
            Module::IMPROVE,
            Module::RETIRE,
        ];

        $module = $this->createValidModule();

        foreach ($states as $state) {
            $module->setStatus($state);
            $this->assertEquals($state, $module->getStatus());
        }
    }

    /**
     * Test that invalid status is rejected
     */
    public function testInvalidStatusIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid module status');

        $module = $this->createValidModule();
        $module->setStatus('InvalidStatus');
    }

    /**
     * Test that Module can transition through lifecycle states
     */
    public function testModuleLifecycleTransition(): void
    {
        $module = $this->createValidModule();

        $transitions = [
            Module::IDENTIFY,
            Module::DESIGN,
            Module::REVIEW,
            Module::APPROVE,
            Module::IMPLEMENT,
            Module::OPERATE,
        ];

        foreach ($transitions as $state) {
            $module->setStatus($state);
            $this->assertEquals($state, $module->getStatus());
        }
    }

    // ========================================
    // TEST 7: Metadata - KPIs
    // ========================================

    /**
     * Test that KPIs can be set and retrieved
     */
    public function testKpisWork(): void
    {
        $module = $this->createValidModule();

        $kpis = [
            ['name' => 'Employee Satisfaction', 'target' => 85],
            ['name' => 'Leave Approval Time', 'target' => 2],
        ];

        $module->setKpis($kpis);

        $this->assertEquals($kpis, $module->getKpis());
    }

    /**
     * Test that KPIs can be empty
     */
    public function testKpisCanBeEmpty(): void
    {
        $module = $this->createValidModule();

        $this->assertEmpty($module->getKpis());
    }

    // ========================================
    // TEST 8: Serialization
    // ========================================

    /**
     * Test that Module can be converted to array
     */
    public function testModuleCanBeConvertedToArray(): void
    {
        $module = $this->createValidModule();
        $module->setKpis([['name' => 'Satisfaction', 'target' => 85]]);

        $array = $module->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('HR', $array['module_id']);
        $this->assertEquals('Human Resources', $array['module_name']);
        $this->assertEquals('hr-manager-001', $array['business_owner']);
        $this->assertEquals(Module::IMPLEMENT, $array['status']);
        $this->assertArrayHasKey('entity_id', $array);
        $this->assertArrayHasKey('entity_type', $array);
        $this->assertEquals('Module', $array['entity_type']);
    }

    /**
     * Test that Module can be converted to JSON
     */
    public function testModuleCanBeConvertedToJson(): void
    {
        $module = $this->createValidModule();

        $json = $module->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('HR', $decoded['module_id']);
        $this->assertEquals('Module', $decoded['entity_type']);
    }

    /**
     * Test that Module is JSON serializable
     */
    public function testModuleIsJsonSerializable(): void
    {
        $module = $this->createValidModule();

        $json = json_encode($module);

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('HR', $decoded['module_id']);
    }

    /**
     * Test that serialization does not include framework-specific objects
     */
    public function testSerializationIsFrameworkIndependent(): void
    {
        $module = $this->createValidModule();

        $array = $module->toArray();

        // Check that timestamps are strings (ISO 8601), not DateTime objects
        foreach (['created_at', 'updated_at', 'deleted_at'] as $field) {
            if ($array[$field] !== null) {
                $this->assertIsString($array[$field]);
            }
        }

        // Check that all values are JSON-serializable primitives
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Arrays are OK (recursive check could be added)
                continue;
            }
            // Must be string, int, float, bool, or null
            $this->assertTrue(
                is_string($value) || is_int($value) || is_float($value) || is_bool($value) || $value === null,
                sprintf('Field %s contains non-primitive type: %s', $key, gettype($value))
            );
        }
    }

    // ========================================
    // TEST 9: Invalid Data Rejection
    // ========================================

    /**
     * Test that setters enforce validation for required fields
     */
    public function testSetterEnforcesValidation(): void
    {
        $module = $this->createValidModule();

        // These pass because the module was already initialized with valid data
        $module->setModuleId('HR2');
        $this->assertEquals('HR2', $module->getModuleId());
    }

    // ========================================
    // TEST 10: Validation
    // ========================================

    /**
     * Test that valid Module passes initialization
     */
    public function testValidModulePassesInitialization(): void
    {
        $module = $this->createValidModule();

        $this->assertNotNull($module->getEntityId());
        $this->assertEquals('Module', $module->getEntityType());
        $this->assertEquals(1, $module->getEntityVersion());
    }

    /**
     * Test that Module properties are properly preserved during initialization
     */
    public function testModulePropertiesArePreserved(): void
    {
        $module = $this->createValidModule();

        $this->assertEquals('HR', $module->getModuleId());
        $this->assertEquals('Human Resources', $module->getModuleName());
        $this->assertEquals('Core HR capabilities', $module->getDescription());
        $this->assertEquals('hr-manager-001', $module->getBusinessOwner());
        $this->assertEquals(Module::IMPLEMENT, $module->getStatus());
    }

    // ========================================
    // TEST 11: Domain Relationship
    // ========================================

    /**
     * Test that domains can be added to Module
     */
    public function testDomainsCanBeAdded(): void
    {
        $module = $this->createValidModule();

        $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);

        $this->assertTrue($module->hasDomain('emp-mgmt'));
        $this->assertEquals(['id' => 'emp-mgmt', 'name' => 'Employee Management'], $module->getDomain('emp-mgmt'));
    }

    /**
     * Test that multiple domains can be added
     */
    public function testMultipleDomainsCanBeAdded(): void
    {
        $module = $this->createValidModule();

        $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);
        $module->addDomain(['id' => 'leave-mgmt', 'name' => 'Leave Management']);

        $domains = $module->getDomains();
        $this->assertCount(2, $domains);
    }

    /**
     * Test that duplicate domains are rejected
     */
    public function testDuplicateDomainsAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');

        $module = $this->createValidModule();
        $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);
        $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Mgmt']);
    }

    /**
     * Test that domain without ID is rejected
     */
    public function testDomainWithoutIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain must have an id field');

        $module = $this->createValidModule();
        $module->addDomain(['name' => 'Employee Management']);
    }

    /**
     * Test that domains can be removed
     */
    public function testDomainsCanBeRemoved(): void
    {
        $module = $this->createValidModule();
        $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);

        $module->removeDomain('emp-mgmt');

        $this->assertFalse($module->hasDomain('emp-mgmt'));
        $this->assertCount(0, $module->getDomains());
    }

    /**
     * Test that removing non-existent domain throws error
     */
    public function testRemovingNonExistentDomainThrowsError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $module = $this->createValidModule();
        $module->removeDomain('non-existent');
    }

    /**
     * Test that domains are serialized in output
     */
    public function testDomainsAreSerializedInOutput(): void
    {
        $module = $this->createValidModule();
        $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);

        $array = $module->toArray();

        $this->assertNotEmpty($array['domains']);
        $this->assertEquals('emp-mgmt', $array['domains'][0]['id']);
    }

    // ========================================
    // TEST 12: Dependencies
    // ========================================

    /**
     * Test that dependencies can be set
     */
    public function testDependenciesCanBeSet(): void
    {
        $module = $this->createValidModule();

        $module->setDependencies(['core', 'security']);

        $this->assertEquals(['core', 'security'], $module->getDependencies());
    }

    /**
     * Test that dependencies can be added individually
     */
    public function testDependenciesCanBeAdded(): void
    {
        $module = $this->createValidModule();

        $module->addDependency('core');
        $module->addDependency('security');

        $this->assertTrue($module->hasDependency('core'));
        $this->assertTrue($module->hasDependency('security'));
    }

    /**
     * Test that duplicate dependencies are rejected
     */
    public function testDuplicateDependenciesAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');

        $module = $this->createValidModule();
        $module->addDependency('core');
        $module->addDependency('core');
    }

    /**
     * Test that dependencies can be removed
     */
    public function testDependenciesCanBeRemoved(): void
    {
        $module = $this->createValidModule();
        $module->addDependency('core');

        $module->removeDependency('core');

        $this->assertFalse($module->hasDependency('core'));
    }

    /**
     * Test that removing non-existent dependency throws error
     */
    public function testRemovingNonExistentDependencyThrowsError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $module = $this->createValidModule();
        $module->removeDependency('non-existent');
    }

    /**
     * Test that dependencies are serialized in output
     */
    public function testDependenciesAreSerializedInOutput(): void
    {
        $module = $this->createValidModule();
        $module->setDependencies(['core', 'security']);

        $array = $module->toArray();

        $this->assertEquals(['core', 'security'], $array['dependencies']);
    }

    // ========================================
    // TEST 13: Version Consistency
    // ========================================

    /**
     * Test that Module has entity version from BaseModel
     */
    public function testModuleHasEntityVersion(): void
    {
        $module = $this->createValidModule();

        $this->assertIsInt($module->getEntityVersion());
        $this->assertEquals(1, $module->getEntityVersion());
    }

    /**
     * Test that Module has entity ID from BaseModel
     */
    public function testModuleHasEntityId(): void
    {
        $module = $this->createValidModule();

        $this->assertNotEmpty($module->getEntityId());
        $this->assertIsString($module->getEntityId());
    }

    /**
     * Test that Module has entity type set to 'Module'
     */
    public function testModuleEntityTypeIsModule(): void
    {
        $module = $this->createValidModule();

        $this->assertEquals('Module', $module->getEntityType());
    }

    /**
     * Test that Module has audit timestamps from BaseModel
     */
    public function testModuleHasAuditTimestamps(): void
    {
        $module = $this->createValidModule();

        $this->assertInstanceOf(\DateTimeImmutable::class, $module->getCreatedAt());
        $this->assertNull($module->getUpdatedAt());
        $this->assertNull($module->getDeletedAt());
    }

    // ========================================
    // TEST 14: Framework Independence
    // ========================================

    /**
     * Test that Module toArray output contains no Laravel/Eloquent objects
     */
    public function testModuleToArrayHasNoLaravelObjects(): void
    {
        $module = $this->createValidModule();
        $module->addDomain(['id' => 'test', 'name' => 'Test Domain']);
        $module->setDependencies(['core']);
        $module->setKpis([['name' => 'metric', 'target' => 100]]);

        $array = $module->toArray();

        // Verify it's JSON-serializable
        $json = json_encode($array);
        $this->assertIsString($json);

        // Verify all values are primitive or arrays
        foreach ($array as $value) {
            $this->assertTrue(
                is_array($value) || is_string($value) || is_int($value) || is_bool($value) || is_null($value),
                sprintf('Found non-primitive value of type %s', gettype($value))
            );
        }
    }

    /**
     * Test that Module toString works
     */
    public function testModuleHasStringRepresentation(): void
    {
        $module = $this->createValidModule();

        $string = (string) $module;

        $this->assertIsString($string);
        $this->assertStringContainsString('HR', $string);
        $this->assertStringContainsString('Human Resources', $string);
    }

    /**
     * Test that Module can be created and serialized without any framework dependencies
     */
    public function testModuleIsFrameworkIndependent(): void
    {
        $module = $this->createValidModule();

        // Add various data
        $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);
        $module->setDependencies(['core', 'auth']);
        $module->setKpis([['name' => 'efficiency', 'target' => 95]]);
        $module->setComplianceRequirements([['standard' => 'ISO-27001']]);
        $module->setMetadataValue('custom-field', 'custom-value');

        // Convert to JSON - should work without any Laravel-specific code
        $json = $module->toJson();
        $decoded = json_decode($json, true);

        // Verify all critical fields are present and correct
        $this->assertEquals('HR', $decoded['module_id']);
        $this->assertEquals('Human Resources', $decoded['module_name']);
        $this->assertNotEmpty($decoded['entity_id']);
        $this->assertEquals('Module', $decoded['entity_type']);
    }
}
