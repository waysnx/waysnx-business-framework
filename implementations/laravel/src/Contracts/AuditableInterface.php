<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

use WaysNX\BusinessFramework\Types\AuditableInterface as FrameworkIndependentAuditableInterface;

/**
 * AuditableInterface
 *
 * Laravel-specific adapter for the framework-independent AuditableInterface.
 *
 * This interface extends the framework-independent contract defined in
 * WaysNX\BusinessFramework\Types\AuditableInterface.
 *
 * Framework Compliance:
 * - Extends: WaysNX\BusinessFramework\Types\AuditableInterface
 * - Used by: Laravel BaseModel implementation
 * - Return types: All methods return mixed per framework-independent contract
 * - Implementations: Laravel BaseModel provides DateTimeImmutable instances
 *
 * Note: This interface does NOT redeclare methods with more specific types,
 * as this would violate the Liskov Substitution Principle. The framework-independent
 * interface defines the contract; Laravel implementations fulfill it with
 * concrete types (DateTimeImmutable).
 *
 * @extends FrameworkIndependentAuditableInterface
 * @package WaysNX\BusinessFramework\Contracts
 */
interface AuditableInterface extends FrameworkIndependentAuditableInterface
{
}
