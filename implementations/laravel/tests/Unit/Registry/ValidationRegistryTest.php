<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Registry;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\DuplicateValidationException;
use WaysNX\BusinessFramework\Exceptions\ValidationNotFoundException;
use WaysNX\BusinessFramework\Registry\ValidationDefinition;
use WaysNX\BusinessFramework\Registry\ValidationRegistry;
use WaysNX\BusinessFramework\Registry\ValidationRuleDefinition;

/**
 * ValidationRegistryTest
 *
 * Comprehensive test suite for ValidationRegistry.
 *
 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class ValidationRegistryTest extends TestCase
{
    private ValidationRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ValidationRegistry();
    }

    private function createValidation(string $id, array $options = []): ValidationDefinition
    {
        return new ValidationDefinition(
            id: $id,
            name: $options['name'] ?? ucfirst($id),
            displayName: $options['displayName'] ?? ucfirst($id),
            description: $options['description'] ?? '',
            version: $options['version'] ?? '1.0.0',
            moduleId: $options['moduleId'] ?? '',
            category: $options['category'] ?? '',
            scope: $options['scope'] ?? 'entity',
            severity: $options['severity'] ?? 'error',
            rules: $options['rules'] ?? [],
            supportedEntityTypes: $options['supportedEntityTypes'] ?? [],
            businessFunctionIds: $options['businessFunctionIds'] ?? [],
            workflowIds: $options['workflowIds'] ?? [],
            priority: $options['priority'] ?? 0,
            enabled: $options['enabled'] ?? true,
            tags: $options['tags'] ?? [],
            metadata: $options['metadata'] ?? []
        );
    }

    public function testRegisterSingleValidation(): void
    {
        $validation = $this->createValidation('email-validation');
        $result = $this->registry->register($validation);

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->exists('email-validation'));
        $this->assertSame(1, $this->registry->count());
    }

    public function testRegisterMultipleValidations(): void
    {
        $this->registry->register($this->createValidation('email-validation'));
        $this->registry->register($this->createValidation('phone-validation'));

        $this->assertSame(2, $this->registry->count());
    }

    public function testRegisterDuplicateIdThrowsException(): void
    {
        $validation = $this->createValidation('email-validation');
        $this->registry->register($validation);

        $this->expectException(DuplicateValidationException::class);
        $this->registry->register($validation);
    }

    public function testRegisterDuplicateNameThrowsException(): void
    {
        $val1 = $this->createValidation('email', ['name' => 'EmailValidation']);
        $val2 = $this->createValidation('email-alt', ['name' => 'EmailValidation']);

        $this->registry->register($val1);

        $this->expectException(DuplicateValidationException::class);
        $this->registry->register($val2);
    }

    public function testRegisterDuplicateRuleIdThrowsException(): void
    {
        $rule1 = new ValidationRuleDefinition('rule-1', 'Rule1');
        $rule2 = new ValidationRuleDefinition('rule-1', 'Rule1Alt');

        $validation = $this->createValidation('test', ['rules' => [$rule1, $rule2]]);

        $this->expectException(DuplicateValidationException::class);
        $this->registry->register($validation);
    }

    public function testFindById(): void
    {
        $validation = $this->createValidation('email-validation');
        $this->registry->register($validation);

        $found = $this->registry->findById('email-validation');

        $this->assertSame('email-validation', $found->id);
    }

    public function testFindByIdNotFoundThrowsException(): void
    {
        $this->expectException(ValidationNotFoundException::class);
        $this->registry->findById('email-validation');
    }

    public function testFindByName(): void
    {
        $validation = $this->createValidation('email-validation', ['name' => 'EmailValidation']);
        $this->registry->register($validation);

        $found = $this->registry->findByName('EmailValidation');

        $this->assertSame('email-validation', $found->id);
    }

    public function testFindByModule(): void
    {
        $this->registry->register($this->createValidation('email', ['moduleId' => 'crm']));
        $this->registry->register($this->createValidation('phone', ['moduleId' => 'crm']));
        $this->registry->register($this->createValidation('invoice', ['moduleId' => 'accounting']));

        $crmValidations = $this->registry->findByModule('crm');

        $this->assertCount(2, $crmValidations);
    }

    public function testFindByScope(): void
    {
        $this->registry->register($this->createValidation('entity-val', ['scope' => 'entity']));
        $this->registry->register($this->createValidation('form-val', ['scope' => 'form']));
        $this->registry->register($this->createValidation('entity-val2', ['scope' => 'entity']));

        $entityValidations = $this->registry->findByScope('entity');

        $this->assertCount(2, $entityValidations);
    }

    public function testFindBySeverity(): void
    {
        $this->registry->register($this->createValidation('error-val', ['severity' => 'error']));
        $this->registry->register($this->createValidation('warning-val', ['severity' => 'warning']));
        $this->registry->register($this->createValidation('error-val2', ['severity' => 'error']));

        $errorValidations = $this->registry->findBySeverity('error');

        $this->assertCount(2, $errorValidations);
    }

    public function testFindByCategory(): void
    {
        $this->registry->register($this->createValidation('email', ['category' => 'contact']));
        $this->registry->register($this->createValidation('phone', ['category' => 'contact']));
        $this->registry->register($this->createValidation('address', ['category' => 'location']));

        $contactValidations = $this->registry->findByCategory('contact');

        $this->assertCount(2, $contactValidations);
    }

    public function testFindByTag(): void
    {
        $this->registry->register($this->createValidation('email', ['tags' => ['core', 'contact']]));
        $this->registry->register($this->createValidation('phone', ['tags' => ['core', 'contact']]));
        $this->registry->register($this->createValidation('address', ['tags' => ['location']]));

        $coreValidations = $this->registry->findByTag('core');

        $this->assertCount(2, $coreValidations);
    }

    public function testGetEnabledValidations(): void
    {
        $this->registry->register($this->createValidation('enabled', ['enabled' => true]));
        $this->registry->register($this->createValidation('disabled', ['enabled' => false]));

        $enabledValidations = $this->registry->enabled();

        $this->assertCount(1, $enabledValidations);
        $this->assertArrayHasKey('enabled', $enabledValidations);
    }

    public function testGetDisabledValidations(): void
    {
        $this->registry->register($this->createValidation('enabled', ['enabled' => true]));
        $this->registry->register($this->createValidation('disabled', ['enabled' => false]));

        $disabledValidations = $this->registry->disabled();

        $this->assertCount(1, $disabledValidations);
        $this->assertArrayHasKey('disabled', $disabledValidations);
    }

    public function testAddAlias(): void
    {
        $this->registry->register($this->createValidation('email-validation'));

        $result = $this->registry->alias('email-validation', 'email-val');

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->aliasExists('email-val'));
    }

    public function testFindByAlias(): void
    {
        $this->registry->register($this->createValidation('email-validation'));
        $this->registry->alias('email-validation', 'email-val');

        $found = $this->registry->findByAlias('email-val');

        $this->assertSame('email-validation', $found->id);
    }

    public function testRemoveAlias(): void
    {
        $this->registry->register($this->createValidation('email-validation'));
        $this->registry->alias('email-validation', 'email-val');

        $result = $this->registry->removeAlias('email-val');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->aliasExists('email-val'));
    }

    public function testGetAliasesForValidation(): void
    {
        $this->registry->register($this->createValidation('email-validation'));
        $this->registry->alias('email-validation', 'email-val');
        $this->registry->alias('email-validation', 'email-check');

        $aliases = $this->registry->getAliasesFor('email-validation');

        $this->assertCount(2, $aliases);
        $this->assertContains('email-val', $aliases);
        $this->assertContains('email-check', $aliases);
    }

    public function testUnregisterValidation(): void
    {
        $this->registry->register($this->createValidation('email-validation'));
        $this->assertTrue($this->registry->exists('email-validation'));

        $result = $this->registry->unregister('email-validation');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->exists('email-validation'));
    }

    public function testUnregisterRemovesAliases(): void
    {
        $this->registry->register($this->createValidation('email-validation'));
        $this->registry->alias('email-validation', 'email-val');

        $this->registry->unregister('email-validation');

        $this->assertFalse($this->registry->aliasExists('email-val'));
    }

    public function testGetExperimentalValidations(): void
    {
        $this->registry->register($this->createValidation('experimental', ['metadata' => ['experimental' => true]]));
        $this->registry->register($this->createValidation('stable'));

        $experimental = $this->registry->experimental();

        $this->assertCount(1, $experimental);
        $this->assertArrayHasKey('experimental', $experimental);
    }

    public function testGetDeprecatedValidations(): void
    {
        $this->registry->register($this->createValidation('deprecated', ['metadata' => ['deprecated' => true]]));
        $this->registry->register($this->createValidation('current'));

        $deprecated = $this->registry->deprecated();

        $this->assertCount(1, $deprecated);
        $this->assertArrayHasKey('deprecated', $deprecated);
    }

    public function testSupportingEntity(): void
    {
        $this->registry->register($this->createValidation('customer-val', ['supportedEntityTypes' => ['Customer']]));
        $this->registry->register($this->createValidation('invoice-val', ['supportedEntityTypes' => ['Invoice']]));

        $customerValidations = $this->registry->supportingEntity('Customer');

        $this->assertCount(1, $customerValidations);
        $this->assertArrayHasKey('customer-val', $customerValidations);
    }

    public function testSortedByPriority(): void
    {
        $this->registry->register($this->createValidation('low', ['priority' => 1]));
        $this->registry->register($this->createValidation('high', ['priority' => 100]));
        $this->registry->register($this->createValidation('medium', ['priority' => 50]));

        $sorted = $this->registry->sortedByPriority();

        $ids = array_keys($sorted);
        $this->assertSame('high', $ids[0]);
        $this->assertSame('medium', $ids[1]);
        $this->assertSame('low', $ids[2]);
    }

    public function testClearAllValidations(): void
    {
        $this->registry->register($this->createValidation('email-validation'));
        $this->registry->alias('email-validation', 'email-val');

        $this->assertSame(1, $this->registry->count());

        $result = $this->registry->clear();

        $this->assertSame($this->registry, $result);
        $this->assertSame(0, $this->registry->count());
    }

    public function testEmptyRegistry(): void
    {
        $this->assertSame(0, $this->registry->count());
        $this->assertCount(0, $this->registry->all());
    }

    public function testMethodChaining(): void
    {
        $result = $this->registry
            ->register($this->createValidation('email'))
            ->register($this->createValidation('phone'))
            ->alias('email', 'email-val');

        $this->assertSame($this->registry, $result);
        $this->assertSame(2, $this->registry->count());
    }

    public function testExtensionHooksAreCalled(): void
    {
        $registry = new TestValidationRegistry();
        $validation = $this->createValidation('email');

        $registry->register($validation);

        $this->assertTrue($registry->beforeRegisterCalled);
        $this->assertTrue($registry->afterRegisterCalled);

        $registry->unregister('email');

        $this->assertTrue($registry->beforeUnregisterCalled);
        $this->assertTrue($registry->afterUnregisterCalled);
    }

    public function testValidationWithRules(): void
    {
        $rule1 = new ValidationRuleDefinition('rule-1', 'Rule1');
        $rule2 = new ValidationRuleDefinition('rule-2', 'Rule2');

        $validation = $this->createValidation('test', ['rules' => [$rule1, $rule2]]);
        $this->registry->register($validation);

        $found = $this->registry->findById('test');

        $this->assertCount(2, $found->rules);
        $this->assertSame('rule-1', $found->rules[0]->id);
    }
}

class TestValidationRegistry extends ValidationRegistry
{
    public bool $beforeRegisterCalled = false;
    public bool $afterRegisterCalled = false;
    public bool $beforeUnregisterCalled = false;
    public bool $afterUnregisterCalled = false;

    protected function beforeRegister(ValidationDefinition $definition): void
    {
        $this->beforeRegisterCalled = true;
    }

    protected function afterRegister(ValidationDefinition $definition): void
    {
        $this->afterRegisterCalled = true;
    }

    protected function beforeUnregister(ValidationDefinition $definition): void
    {
        $this->beforeUnregisterCalled = true;
    }

    protected function afterUnregister(ValidationDefinition $definition): void
    {
        $this->afterUnregisterCalled = true;
    }
}
