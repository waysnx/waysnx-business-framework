<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

use WaysNX\BusinessFramework\Types\EntityInterface as FrameworkIndependentEntityInterface;

/**
 * EntityInterface
 *
 * Laravel-specific adapter for the framework-independent EntityInterface.
 *
 * This interface extends the framework-independent contract defined in
 * WaysNX\BusinessFramework\Types\EntityInterface, providing Laravel-specific
 * implementations and documentation.
 *
 * Framework Compliance:
 * - Implements: WaysNX\BusinessFramework\Types\EntityInterface
 * - Used by: Laravel BaseModel implementation
 * - Allows: Future framework implementations to use packages/types directly
 *
 * @extends FrameworkIndependentEntityInterface
 * @package WaysNX\BusinessFramework\Contracts
 */
interface EntityInterface extends FrameworkIndependentEntityInterface
{
    // Laravel-specific extensions would go here if needed
}
