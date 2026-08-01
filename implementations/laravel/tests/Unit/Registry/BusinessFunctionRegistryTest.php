<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Registry;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\BusinessFunctionNotFoundException;
use WaysNX\BusinessFramework\Exceptions\DuplicateBusinessFunctionException;
use WaysNX\BusinessFramework\Registry\BusinessFunctionDefinition;
use WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry;

/**
 * BusinessFunctionRegistryTest
 *
 * Comprehensive test suite for BusinessFunctionRegistry.
 *
 * Test Coverage:
 * - Registration and unregistration
 * - Duplicate detection
 * - Lookup by ID, name, alias, module
 * - Filtering (enabled, disabled, category, tag)
 * - Metadata and entity type queries
 * - Alias management
 * - Permission filtering
 * - Edge cases and error conditions
 * - Extension points and hooks
 *
 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class BusinessFunctionRegistryTest extends TestCase
{
    /**
     * Test registry instance
     *

     * @var BusinessFunctionRegistry
     */
    private BusinessFunctionRegistry $registry;

    /**
     * Set up test fixtures
     *

     * @return void
     */
    protected function setUp(): void
    {
        $this->registry = new BusinessFunctionRegistry();
    }

    /**
     * Test registering a single business function
     *

     * @return void
     */
    public function testRegisterSingleFunction(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            description: 'Creates a new customer',
            moduleId: 'crm',
            category: 'customer-management',
            version: '1.0.0',
            inputDefinitions: [['name' => 'firstName', 'type' => 'string']],
            outputDefinitions: [['name' => 'customerId', 'type' => 'string']],
            supportedEntityTypes: ['Customer'],
            requiredPermissions: ['customer.create'],
            enabled: true,
            tags: ['core', 'customer'],
            metadata: ['owner' => 'acme']
        );

        $result = $this->registry->register($definition);

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->exists('create-customer'));
        $this->assertSame(1, $this->registry->count());
    }

    /**
     * Test registering multiple functions
     *

     * @return void
     */
    public function testRegisterMultipleFunctions(): void
    {
        $create = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            moduleId: 'crm'
        );

        $update = new BusinessFunctionDefinition(
            id: 'update-customer',
            name: 'UpdateCustomer',
            displayName: 'Update Customer',
            moduleId: 'crm'
        );

        $this->registry->register($create);
        $this->registry->register($update);

        $this->assertSame(2, $this->registry->count());
        $this->assertTrue($this->registry->exists('create-customer'));
        $this->assertTrue($this->registry->exists('update-customer'));
    }

    /**
     * Test registering duplicate ID throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateIdThrowsException(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);

        $this->expectException(DuplicateBusinessFunctionException::class);
        $this->expectExceptionMessage("Function with ID 'create-customer' is already registered");

        $this->registry->register($definition);
    }

    /**
     * Test registering duplicate name throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateNameThrowsException(): void
    {
        $func1 = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $func2 = new BusinessFunctionDefinition(
            id: 'create-customer-alt',
            name: 'CreateCustomer',
            displayName: 'Create Customer Alt'
        );

        $this->registry->register($func1);

        $this->expectException(DuplicateBusinessFunctionException::class);
        $this->expectExceptionMessage("Function with name 'CreateCustomer' is already registered");

        $this->registry->register($func2);
    }

    /**
     * Test finding function by ID
     *

     * @return void
     */
    public function testFindById(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);

        $found = $this->registry->findById('create-customer');

        $this->assertSame('create-customer', $found->id);
        $this->assertSame('CreateCustomer', $found->name);
    }

    /**
     * Test finding non-existent function by ID throws exception
     *

     * @return void
     */
    public function testFindByIdNotFoundThrowsException(): void
    {
        $this->expectException(BusinessFunctionNotFoundException::class);
        $this->expectExceptionMessage("Function with ID 'create-customer' not found in registry");

        $this->registry->findById('create-customer');
    }

    /**
     * Test finding function by name
     *

     * @return void
     */
    public function testFindByName(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);

        $found = $this->registry->findByName('CreateCustomer');

        $this->assertSame('create-customer', $found->id);
    }

    /**
     * Test finding non-existent function by name throws exception
     *

     * @return void
     */
    public function testFindByNameNotFoundThrowsException(): void
    {
        $this->expectException(BusinessFunctionNotFoundException::class);
        $this->expectExceptionMessage("Function with name 'CreateCustomer' not found in registry");

        $this->registry->findByName('CreateCustomer');
    }

    /**
     * Test unregistering a function
     *

     * @return void
     */
    public function testUnregisterFunction(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);
        $this->assertTrue($this->registry->exists('create-customer'));

        $result = $this->registry->unregister('create-customer');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->exists('create-customer'));
        $this->assertSame(0, $this->registry->count());
    }

    /**
     * Test unregistering non-existent function throws exception
     *

     * @return void
     */
    public function testUnregisterNonExistentThrowsException(): void
    {
        $this->expectException(BusinessFunctionNotFoundException::class);

        $this->registry->unregister('create-customer');
    }

    /**
     * Test getting all functions
     *

     * @return void
     */
    public function testGetAllFunctions(): void
    {
        $create = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $update = new BusinessFunctionDefinition(
            id: 'update-customer',
            name: 'UpdateCustomer',
            displayName: 'Update Customer'
        );

        $this->registry->register($create);
        $this->registry->register($update);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('create-customer', $all);
        $this->assertArrayHasKey('update-customer', $all);
    }

    /**
     * Test counting functions
     *

     * @return void
     */
    public function testCountFunctions(): void
    {
        $this->assertSame(0, $this->registry->count());

        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);

        $this->assertSame(1, $this->registry->count());
    }

    /**
     * Test checking function existence
     *

     * @return void
     */
    public function testCheckFunctionExists(): void
    {
        $this->assertFalse($this->registry->exists('create-customer'));

        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->exists('create-customer'));
    }

    /**
     * Test checking name existence
     *

     * @return void
     */
    public function testCheckNameExists(): void
    {
        $this->assertFalse($this->registry->nameExists('CreateCustomer'));

        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->nameExists('CreateCustomer'));
    }

    /**
     * Test adding an alias
     *

     * @return void
     */
    public function testAddAlias(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);

        $result = $this->registry->alias('create-customer', 'create-cust');

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->aliasExists('create-cust'));
    }

    /**
     * Test finding function by alias
     *

     * @return void
     */
    public function testFindByAlias(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);
        $this->registry->alias('create-customer', 'create-cust');

        $found = $this->registry->findByAlias('create-cust');

        $this->assertSame('create-customer', $found->id);
    }

    /**
     * Test finding non-existent alias throws exception
     *

     * @return void
     */
    public function testFindByAliasNotFoundThrowsException(): void
    {
        $this->expectException(BusinessFunctionNotFoundException::class);
        $this->expectExceptionMessage("Function alias 'create-cust' not found in registry");

        $this->registry->findByAlias('create-cust');
    }

    /**
     * Test adding duplicate alias throws exception
     *

     * @return void
     */
    public function testAddDuplicateAliasThrowsException(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);
        $this->registry->alias('create-customer', 'create-cust');

        $this->expectException(DuplicateBusinessFunctionException::class);
        $this->expectExceptionMessage("Alias 'create-cust' is already registered");

        $this->registry->alias('create-customer', 'create-cust');
    }

    /**
     * Test removing an alias
     *

     * @return void
     */
    public function testRemoveAlias(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);
        $this->registry->alias('create-customer', 'create-cust');

        $this->assertTrue($this->registry->aliasExists('create-cust'));

        $result = $this->registry->removeAlias('create-cust');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->aliasExists('create-cust'));
    }

    /**
     * Test getting aliases for function
     *

     * @return void
     */
    public function testGetAliasesForFunction(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);
        $this->registry->alias('create-customer', 'create-cust');
        $this->registry->alias('create-customer', 'new-cust');

        $aliases = $this->registry->getAliasesFor('create-customer');

        $this->assertCount(2, $aliases);
        $this->assertContains('create-cust', $aliases);
        $this->assertContains('new-cust', $aliases);
    }

    /**
     * Test finding functions by module
     *

     * @return void
     */
    public function testFindByModule(): void
    {
        $create = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            moduleId: 'crm'
        );

        $update = new BusinessFunctionDefinition(
            id: 'update-customer',
            name: 'UpdateCustomer',
            displayName: 'Update Customer',
            moduleId: 'crm'
        );

        $approve = new BusinessFunctionDefinition(
            id: 'approve-invoice',
            name: 'ApproveInvoice',
            displayName: 'Approve Invoice',
            moduleId: 'accounting'
        );

        $this->registry->register($create);
        $this->registry->register($update);
        $this->registry->register($approve);

        $crmFunctions = $this->registry->findByModule('crm');

        $this->assertCount(2, $crmFunctions);
        $this->assertArrayHasKey('create-customer', $crmFunctions);
        $this->assertArrayHasKey('update-customer', $crmFunctions);
    }

    /**
     * Test finding functions by category
     *

     * @return void
     */
    public function testFindByCategory(): void
    {
        $create = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            category: 'customer-management'
        );

        $approve = new BusinessFunctionDefinition(
            id: 'approve-invoice',
            name: 'ApproveInvoice',
            displayName: 'Approve Invoice',
            category: 'invoice-management'
        );

        $this->registry->register($create);
        $this->registry->register($approve);

        $customerFuncs = $this->registry->findByCategory('customer-management');

        $this->assertCount(1, $customerFuncs);
        $this->assertArrayHasKey('create-customer', $customerFuncs);
    }

    /**
     * Test finding functions by tag
     *

     * @return void
     */
    public function testFindByTag(): void
    {
        $create = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            tags: ['core', 'customer']
        );

        $update = new BusinessFunctionDefinition(
            id: 'update-customer',
            name: 'UpdateCustomer',
            displayName: 'Update Customer',
            tags: ['core', 'customer']
        );

        $this->registry->register($create);
        $this->registry->register($update);

        $coreFuncs = $this->registry->findByTag('core');

        $this->assertCount(2, $coreFuncs);
        $this->assertArrayHasKey('create-customer', $coreFuncs);
        $this->assertArrayHasKey('update-customer', $coreFuncs);
    }

    /**
     * Test getting enabled functions
     *

     * @return void
     */
    public function testGetEnabledFunctions(): void
    {
        $enabled = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            enabled: true
        );

        $disabled = new BusinessFunctionDefinition(
            id: 'delete-customer',
            name: 'DeleteCustomer',
            displayName: 'Delete Customer',
            enabled: false
        );

        $this->registry->register($enabled);
        $this->registry->register($disabled);

        $enabledFuncs = $this->registry->enabled();

        $this->assertCount(1, $enabledFuncs);
        $this->assertArrayHasKey('create-customer', $enabledFuncs);
    }

    /**
     * Test getting disabled functions
     *

     * @return void
     */
    public function testGetDisabledFunctions(): void
    {
        $enabled = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            enabled: true
        );

        $disabled = new BusinessFunctionDefinition(
            id: 'delete-customer',
            name: 'DeleteCustomer',
            displayName: 'Delete Customer',
            enabled: false
        );

        $this->registry->register($enabled);
        $this->registry->register($disabled);

        $disabledFuncs = $this->registry->disabled();

        $this->assertCount(1, $disabledFuncs);
        $this->assertArrayHasKey('delete-customer', $disabledFuncs);
    }

    /**
     * Test getting experimental functions
     *

     * @return void
     */
    public function testGetExperimentalFunctions(): void
    {
        $experimental = new BusinessFunctionDefinition(
            id: 'new-feature',
            name: 'NewFeature',
            displayName: 'New Feature',
            metadata: ['experimental' => true]
        );

        $stable = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($experimental);
        $this->registry->register($stable);

        $experimFuncs = $this->registry->experimental();

        $this->assertCount(1, $experimFuncs);
        $this->assertArrayHasKey('new-feature', $experimFuncs);
    }

    /**
     * Test getting deprecated functions
     *

     * @return void
     */
    public function testGetDeprecatedFunctions(): void
    {
        $deprecated = new BusinessFunctionDefinition(
            id: 'old-function',
            name: 'OldFunction',
            displayName: 'Old Function',
            metadata: ['deprecated' => true]
        );

        $current = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($deprecated);
        $this->registry->register($current);

        $deprecatedFuncs = $this->registry->deprecated();

        $this->assertCount(1, $deprecatedFuncs);
        $this->assertArrayHasKey('old-function', $deprecatedFuncs);
    }

    /**
     * Test finding functions supporting an entity type
     *

     * @return void
     */
    public function testFunctionsSupportingEntity(): void
    {
        $customerFunc = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            supportedEntityTypes: ['Customer']
        );

        $invoiceFunc = new BusinessFunctionDefinition(
            id: 'approve-invoice',
            name: 'ApproveInvoice',
            displayName: 'Approve Invoice',
            supportedEntityTypes: ['Invoice']
        );

        $this->registry->register($customerFunc);
        $this->registry->register($invoiceFunc);

        $customerFuncs = $this->registry->supportingEntity('Customer');

        $this->assertCount(1, $customerFuncs);
        $this->assertArrayHasKey('create-customer', $customerFuncs);
    }

    /**
     * Test finding functions requiring a permission
     *

     * @return void
     */
    public function testFunctionsRequiringPermission(): void
    {
        $createFunc = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer',
            requiredPermissions: ['customer.create', 'customer.write']
        );

        $readFunc = new BusinessFunctionDefinition(
            id: 'list-customers',
            name: 'ListCustomers',
            displayName: 'List Customers',
            requiredPermissions: ['customer.read']
        );

        $this->registry->register($createFunc);
        $this->registry->register($readFunc);

        $createPerms = $this->registry->requiringPermission('customer.create');

        $this->assertCount(1, $createPerms);
        $this->assertArrayHasKey('create-customer', $createPerms);
    }

    /**
     * Test clearing all functions
     *

     * @return void
     */
    public function testClearAllFunctions(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);
        $this->registry->alias('create-customer', 'create-cust');

        $this->assertSame(1, $this->registry->count());

        $result = $this->registry->clear();

        $this->assertSame($this->registry, $result);
        $this->assertSame(0, $this->registry->count());
        $this->assertFalse($this->registry->aliasExists('create-cust'));
    }

    /**
     * Test empty registry
     *

     * @return void
     */
    public function testEmptyRegistry(): void
    {
        $this->assertSame(0, $this->registry->count());
        $this->assertCount(0, $this->registry->all());
        $this->assertCount(0, $this->registry->enabled());
    }

    /**
     * Test unregistering removes aliases
     *

     * @return void
     */
    public function testUnregisterRemovesAliases(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $this->registry->register($definition);
        $this->registry->alias('create-customer', 'create-cust');
        $this->registry->alias('create-customer', 'new-cust');

        $this->assertTrue($this->registry->aliasExists('create-cust'));
        $this->assertTrue($this->registry->aliasExists('new-cust'));

        $this->registry->unregister('create-customer');

        $this->assertFalse($this->registry->aliasExists('create-cust'));
        $this->assertFalse($this->registry->aliasExists('new-cust'));
    }

    /**
     * Test method chaining
     *

     * @return void
     */
    public function testMethodChaining(): void
    {
        $create = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $update = new BusinessFunctionDefinition(
            id: 'update-customer',
            name: 'UpdateCustomer',
            displayName: 'Update Customer'
        );

        $result = $this->registry
            ->register($create)
            ->register($update)
            ->alias('create-customer', 'create-cust');

        $this->assertSame($this->registry, $result);
        $this->assertSame(2, $this->registry->count());
        $this->assertTrue($this->registry->aliasExists('create-cust'));
    }

    /**
     * Test extension hooks are called
     *

     * @return void
     */
    public function testExtensionHooksAreCalled(): void
    {
        $registry = new TestBusinessFunctionRegistry();

        $definition = new BusinessFunctionDefinition(
            id: 'create-customer',
            name: 'CreateCustomer',
            displayName: 'Create Customer'
        );

        $registry->register($definition);

        $this->assertTrue($registry->beforeRegisterCalled);
        $this->assertTrue($registry->afterRegisterCalled);

        $registry->unregister('create-customer');

        $this->assertTrue($registry->beforeUnregisterCalled);
        $this->assertTrue($registry->afterUnregisterCalled);
    }

    /**
     * Test function without module
     *

     * @return void
     */
    public function testFunctionWithoutModule(): void
    {
        $definition = new BusinessFunctionDefinition(
            id: 'standalone',
            name: 'Standalone',
            displayName: 'Standalone Function'
        );

        $result = $this->registry->register($definition);

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->exists('standalone'));
        $this->assertCount(0, $this->registry->findByModule(''));
    }
}

/**
 * TestBusinessFunctionRegistry
 *
 * Test implementation of BusinessFunctionRegistry that tracks hook calls.
 *

 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class TestBusinessFunctionRegistry extends BusinessFunctionRegistry
{
    /**
     * Track if beforeRegister was called
     *

     * @var bool
     */
    public bool $beforeRegisterCalled = false;

    /**
     * Track if afterRegister was called
     *

     * @var bool
     */
    public bool $afterRegisterCalled = false;

    /**
     * Track if beforeUnregister was called
     *

     * @var bool
     */
    public bool $beforeUnregisterCalled = false;

    /**
     * Track if afterUnregister was called
     *

     * @var bool
     */
    public bool $afterUnregisterCalled = false;

    /**
     * Override beforeRegister to track calls
     *

     * @param BusinessFunctionDefinition $definition The function definition
     *

     * @return void
     */
    protected function beforeRegister(BusinessFunctionDefinition $definition): void
    {
        $this->beforeRegisterCalled = true;
    }

    /**
     * Override afterRegister to track calls
     *

     * @param BusinessFunctionDefinition $definition The function definition
     *

     * @return void
     */
    protected function afterRegister(BusinessFunctionDefinition $definition): void
    {
        $this->afterRegisterCalled = true;
    }

    /**
     * Override beforeUnregister to track calls
     *

     * @param BusinessFunctionDefinition $definition The function definition
     *

     * @return void
     */
    protected function beforeUnregister(BusinessFunctionDefinition $definition): void
    {
        $this->beforeUnregisterCalled = true;
    }

    /**
     * Override afterUnregister to track calls
     *

     * @param BusinessFunctionDefinition $definition The function definition
     *

     * @return void
     */
    protected function afterUnregister(BusinessFunctionDefinition $definition): void
    {
        $this->afterUnregisterCalled = true;
    }
}
