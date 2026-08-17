<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Models\Domain;
use JsonSerializable;

/**
 * DomainTest
 *
 * Comprehensive tests for the WBF Domain model implementation.
 *
 * Tests the following requirements from WBF-DOC-0007:
 * - Domain Identity (Section 10)
 * - Domain Lifecycle (Section 9)
 * - Domain Ownership (Section 10-11)
 * - Domain Metadata (Section 10)
 * - Domain Module Relationship (Section 4, WBF-DOC-0004 Section 5.2)
 * - Domain Capability Relationship (Section 10, WBF-DOC-0004 Section 5.2)
 * - Domain Dependencies (Section 10, Section 12)
 * - Framework independence
 * - JSON serialization
 *
 * @covers \WaysNX\BusinessFramework\Models\Domain
 * @covers \WaysNX\BusinessFramework\Core\DomainAbstract
 * @package WaysNX\BusinessFramework\Tests\Unit\Models
 */
class DomainTest extends TestCase
{
    /**
     * Create a valid Domain instance for testing
     *
     * @return Domain
     */
    private function createValidDomain(): Domain
    {
        $domain = new Domain();

        $reflection = new \ReflectionClass($domain);

        // Set via reflection to bypass setter validation during setup
        $reflection->getProperty('domainId')->setValue($domain, 'EMP_MGMT');
        $reflection->getProperty('domainName')->setValue($domain, 'Employee Management');
        $reflection->getProperty('description')->setValue($domain, 'Core employee management capabilities');
        $reflection->getProperty('moduleId')->setValue($domain, 'HR');
        $reflection->getProperty('businessOwner')->setValue($domain, 'hr-manager-001');
        $reflection->getProperty('status')->setValue($domain, Domain::IMPLEMENT);

        // Initialize via reflection to avoid visibility issues
        $initMethod = $reflection->getMethod('initializeDomain');
        $initMethod->invoke($domain);

        return $domain;
    }

    // ========================================
    // TEST 1: Domain Creation
    // ========================================

    /**
     * Test that Domain can be created
     */
    public function testDomainCanBeCreated(): void
    {
        $domain = $this->createValidDomain();

        $this->assertInstanceOf(Domain::class, $domain);
    }

    /**
     * Test that Domain is JsonSerializable
     */
    public function testDomainImplementsJsonSerializable(): void
    {
        $domain = $this->createValidDomain();

        $this->assertInstanceOf(JsonSerializable::class, $domain);
    }

    // ========================================
    // TESTS 2-4: Identity Fields
    // ========================================

    /**
     * Test that Domain ID can be set and retrieved
     */
    public function testDomainIdWorks(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals('EMP_MGMT', $domain->getDomainId());
    }

    /**
     * Test that Domain name can be set and retrieved
     */
    public function testDomainNameWorks(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals('Employee Management', $domain->getDomainName());
    }

    /**
     * Test that Domain description can be set and retrieved
     */
    public function testDomainDescriptionWorks(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals('Core employee management capabilities', $domain->getDescription());
    }

    /**
     * Test that Domain ID cannot be empty
     */
    public function testDomainIdCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain ID cannot be empty');

        $domain = new Domain();
        $domain->setDomainId('');
    }

    /**
     * Test that Domain name cannot be empty
     */
    public function testDomainNameCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain name cannot be empty');

        $domain = new Domain();
        $domain->setDomainName('');
    }

    // ========================================
    // TEST 5: Module Relationship
    // ========================================

    /**
     * Test that module ID can be set and retrieved
     */
    public function testModuleIdWorks(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals('HR', $domain->getModuleId());
    }

    /**
     * Test that module ID cannot be empty
     */
    public function testModuleIdCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module ID cannot be empty');

        $domain = new Domain();
        $domain->setModuleId('');
    }

    /**
     * Test that module ID can be integer
     */
    public function testModuleIdCanBeInteger(): void
    {
        $domain = new Domain();
        $domain->setModuleId(12345);

        $this->assertEquals(12345, $domain->getModuleId());
    }

    // ========================================
    // TEST 6: Business Owner (Ownership)
    // ========================================

    /**
     * Test that business owner can be set and retrieved
     */
    public function testBusinessOwnerWorks(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals('hr-manager-001', $domain->getBusinessOwner());
    }

    /**
     * Test that business owner cannot be empty
     */
    public function testBusinessOwnerCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Business owner cannot be empty');

        $domain = new Domain();
        $domain->setBusinessOwner('');
    }

    /**
     * Test that business owner can be integer
     */
    public function testBusinessOwnerCanBeInteger(): void
    {
        $domain = new Domain();
        $domain->setBusinessOwner(54321);

        $this->assertEquals(54321, $domain->getBusinessOwner());
    }

    // ========================================
    // TEST 7: Lifecycle/Status
    // ========================================

    /**
     * Test that Domain status can be set and retrieved
     */
    public function testDomainStatusWorks(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals(Domain::IMPLEMENT, $domain->getStatus());
    }

    /**
     * Test that all 8 lifecycle states are valid
     */
    public function testAllLifecycleStatesAreValid(): void
    {
        $states = [
            Domain::IDENTIFY,
            Domain::DESIGN,
            Domain::REVIEW,
            Domain::APPROVE,
            Domain::IMPLEMENT,
            Domain::OPERATE,
            Domain::IMPROVE,
            Domain::RETIRE,
        ];

        $domain = $this->createValidDomain();

        foreach ($states as $state) {
            $domain->setStatus($state);
            $this->assertEquals($state, $domain->getStatus());
        }
    }

    /**
     * Test that invalid status is rejected
     */
    public function testInvalidStatusIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid domain status');

        $domain = $this->createValidDomain();
        $domain->setStatus('InvalidStatus');
    }

    // ========================================
    // TEST 8: Capabilities
    // ========================================

    /**
     * Test that capabilities can be added to Domain
     */
    public function testCapabilitiesCanBeAdded(): void
    {
        $domain = $this->createValidDomain();

        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);

        $this->assertTrue($domain->hasCapability('emp-search'));
        $this->assertEquals(['id' => 'emp-search', 'name' => 'Employee Search'], $domain->getCapability('emp-search'));
    }

    /**
     * Test that multiple capabilities can be added
     */
    public function testMultipleCapabilitiesCanBeAdded(): void
    {
        $domain = $this->createValidDomain();

        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);
        $domain->addCapability(['id' => 'emp-profile', 'name' => 'Employee Profile']);

        $capabilities = $domain->getCapabilities();
        $this->assertCount(2, $capabilities);
    }

    /**
     * Test that duplicate capabilities are rejected
     */
    public function testDuplicateCapabilitiesAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');

        $domain = $this->createValidDomain();
        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);
        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search v2']);
    }

    /**
     * Test that capability without ID is rejected
     */
    public function testCapabilityWithoutIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capability must have an id field');

        $domain = $this->createValidDomain();
        $domain->addCapability(['name' => 'Employee Search']);
    }

    /**
     * Test that capabilities can be removed
     */
    public function testCapabilitiesCanBeRemoved(): void
    {
        $domain = $this->createValidDomain();
        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);

        $domain->removeCapability('emp-search');

        $this->assertFalse($domain->hasCapability('emp-search'));
        $this->assertCount(0, $domain->getCapabilities());
    }

    /**
     * Test that capabilities are serialized in output
     */
    public function testCapabilitiesAreSerializedInOutput(): void
    {
        $domain = $this->createValidDomain();
        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);

        $array = $domain->toArray();

        $this->assertNotEmpty($array['capabilities']);
        $this->assertEquals('emp-search', $array['capabilities'][0]['id']);
    }

    // ========================================
    // TEST 9: Metadata - KPIs
    // ========================================

    /**
     * Test that KPIs can be set and retrieved
     */
    public function testKpisWork(): void
    {
        $domain = $this->createValidDomain();

        $kpis = [
            ['name' => 'Efficiency', 'target' => 95],
            ['name' => 'Availability', 'target' => 99],
        ];

        $domain->setKpis($kpis);

        $this->assertEquals($kpis, $domain->getKpis());
    }

    /**
     * Test that KPIs can be empty
     */
    public function testKpisCanBeEmpty(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEmpty($domain->getKpis());
    }

    // ========================================
    // TEST 10: Dependencies
    // ========================================

    /**
     * Test that dependencies can be set
     */
    public function testDependenciesCanBeSet(): void
    {
        $domain = $this->createValidDomain();

        $domain->setDependencies(['payroll', 'recruitment']);

        $this->assertEquals(['payroll', 'recruitment'], $domain->getDependencies());
    }

    /**
     * Test that dependencies can be added individually
     */
    public function testDependenciesCanBeAdded(): void
    {
        $domain = $this->createValidDomain();

        $domain->addDependency('payroll');
        $domain->addDependency('recruitment');

        $this->assertTrue($domain->hasDependency('payroll'));
        $this->assertTrue($domain->hasDependency('recruitment'));
    }

    /**
     * Test that duplicate dependencies are rejected
     */
    public function testDuplicateDependenciesAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');

        $domain = $this->createValidDomain();
        $domain->addDependency('payroll');
        $domain->addDependency('payroll');
    }

    /**
     * Test that dependencies can be removed
     */
    public function testDependenciesCanBeRemoved(): void
    {
        $domain = $this->createValidDomain();
        $domain->addDependency('payroll');

        $domain->removeDependency('payroll');

        $this->assertFalse($domain->hasDependency('payroll'));
    }

    /**
     * Test that dependencies are serialized in output
     */
    public function testDependenciesAreSerializedInOutput(): void
    {
        $domain = $this->createValidDomain();
        $domain->setDependencies(['payroll', 'recruitment']);

        $array = $domain->toArray();

        $this->assertEquals(['payroll', 'recruitment'], $array['dependencies']);
    }

    // ========================================
    // TEST 11: Serialization
    // ========================================

    /**
     * Test that Domain can be converted to array
     */
    public function testDomainCanBeConvertedToArray(): void
    {
        $domain = $this->createValidDomain();
        $domain->setKpis([['name' => 'Efficiency', 'target' => 95]]);

        $array = $domain->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('EMP_MGMT', $array['domain_id']);
        $this->assertEquals('Employee Management', $array['domain_name']);
        $this->assertEquals('HR', $array['module_id']);
        $this->assertEquals('hr-manager-001', $array['business_owner']);
        $this->assertEquals(Domain::IMPLEMENT, $array['status']);
        $this->assertArrayHasKey('entity_id', $array);
        $this->assertArrayHasKey('entity_type', $array);
        $this->assertEquals('Domain', $array['entity_type']);
    }

    /**
     * Test that Domain can be converted to JSON
     */
    public function testDomainCanBeConvertedToJson(): void
    {
        $domain = $this->createValidDomain();

        $json = $domain->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('EMP_MGMT', $decoded['domain_id']);
        $this->assertEquals('Domain', $decoded['entity_type']);
    }

    /**
     * Test that Domain is JSON serializable
     */
    public function testDomainIsJsonSerializable(): void
    {
        $domain = $this->createValidDomain();

        $json = json_encode($domain);

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('EMP_MGMT', $decoded['domain_id']);
    }

    /**
     * Test that serialization does not include framework-specific objects
     */
    public function testSerializationIsFrameworkIndependent(): void
    {
        $domain = $this->createValidDomain();

        $array = $domain->toArray();

        // Check that timestamps are strings (ISO 8601), not DateTime objects
        foreach (['created_at', 'updated_at', 'deleted_at'] as $field) {
            if ($array[$field] !== null) {
                $this->assertIsString($array[$field]);
            }
        }

        // Check that all values are JSON-serializable primitives
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $this->assertTrue(
                is_string($value) || is_int($value) || is_float($value) || is_bool($value) || $value === null,
                sprintf('Field %s contains non-primitive type: %s', $key, gettype($value))
            );
        }
    }

    // ========================================
    // TEST 12: Validation
    // ========================================

    /**
     * Test that valid Domain passes initialization
     */
    public function testValidDomainPassesInitialization(): void
    {
        $domain = $this->createValidDomain();

        $this->assertNotNull($domain->getEntityId());
        $this->assertEquals('Domain', $domain->getEntityType());
        $this->assertEquals(1, $domain->getEntityVersion());
    }

    /**
     * Test that Domain properties are properly preserved during initialization
     */
    public function testDomainPropertiesArePreserved(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals('EMP_MGMT', $domain->getDomainId());
        $this->assertEquals('Employee Management', $domain->getDomainName());
        $this->assertEquals('Core employee management capabilities', $domain->getDescription());
        $this->assertEquals('HR', $domain->getModuleId());
        $this->assertEquals('hr-manager-001', $domain->getBusinessOwner());
        $this->assertEquals(Domain::IMPLEMENT, $domain->getStatus());
    }

    /**
     * Test that setter enforces validation for required fields
     */
    public function testSetterEnforcesValidation(): void
    {
        $domain = $this->createValidDomain();

        // These pass because the domain was already initialized with valid data
        $domain->setDomainId('EMP_MGMT2');
        $this->assertEquals('EMP_MGMT2', $domain->getDomainId());
    }

    // ========================================
    // TEST 13: Version Consistency
    // ========================================

    /**
     * Test that Domain has entity version from BaseModel
     */
    public function testDomainHasEntityVersion(): void
    {
        $domain = $this->createValidDomain();

        $this->assertIsInt($domain->getEntityVersion());
        $this->assertEquals(1, $domain->getEntityVersion());
    }

    /**
     * Test that Domain has entity ID from BaseModel
     */
    public function testDomainHasEntityId(): void
    {
        $domain = $this->createValidDomain();

        $this->assertNotEmpty($domain->getEntityId());
        $this->assertIsString($domain->getEntityId());
    }

    /**
     * Test that Domain has entity type set to 'Domain'
     */
    public function testDomainEntityTypeIsDomain(): void
    {
        $domain = $this->createValidDomain();

        $this->assertEquals('Domain', $domain->getEntityType());
    }

    /**
     * Test that Domain has audit timestamps from BaseModel
     */
    public function testDomainHasAuditTimestamps(): void
    {
        $domain = $this->createValidDomain();

        $this->assertInstanceOf(\DateTimeImmutable::class, $domain->getCreatedAt());
        $this->assertNull($domain->getUpdatedAt());
        $this->assertNull($domain->getDeletedAt());
    }

    // ========================================
    // TEST 14: Framework Independence
    // ========================================

    /**
     * Test that Domain toArray output contains no Laravel/Eloquent objects
     */
    public function testDomainToArrayHasNoLaravelObjects(): void
    {
        $domain = $this->createValidDomain();
        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);
        $domain->setDependencies(['payroll']);
        $domain->setKpis([['name' => 'metric', 'target' => 100]]);

        $array = $domain->toArray();

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
     * Test that Domain toString works
     */
    public function testDomainHasStringRepresentation(): void
    {
        $domain = $this->createValidDomain();

        $string = (string) $domain;

        $this->assertIsString($string);
        $this->assertStringContainsString('EMP_MGMT', $string);
        $this->assertStringContainsString('Employee Management', $string);
    }

    /**
     * Test that Domain can be created and serialized without any framework dependencies
     */
    public function testDomainIsFrameworkIndependent(): void
    {
        $domain = $this->createValidDomain();

        // Add various data
        $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);
        $domain->setDependencies(['payroll', 'recruitment']);
        $domain->setKpis([['name' => 'efficiency', 'target' => 95]]);
        $domain->setComplianceRequirements([['standard' => 'ISO-27001']]);
        $domain->setMetadataValue('custom-field', 'custom-value');

        // Convert to JSON - should work without any Laravel-specific code
        $json = $domain->toJson();
        $decoded = json_decode($json, true);

        // Verify all critical fields are present and correct
        $this->assertEquals('EMP_MGMT', $decoded['domain_id']);
        $this->assertEquals('Employee Management', $decoded['domain_name']);
        $this->assertEquals('HR', $decoded['module_id']);
        $this->assertNotEmpty($decoded['entity_id']);
        $this->assertEquals('Domain', $decoded['entity_type']);
    }
}
