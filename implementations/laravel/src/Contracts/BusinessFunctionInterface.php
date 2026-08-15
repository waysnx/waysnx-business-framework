<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

use WaysNX\BusinessFramework\Types\BusinessFunctionInterface as FrameworkIndependentBusinessFunctionInterface;

/**
 * BusinessFunctionInterface
 *
 * Laravel-specific adapter for the framework-independent BusinessFunctionInterface.
 *
 * This interface extends the framework-independent contract defined in
 * WaysNX\BusinessFramework\Types\BusinessFunctionInterface.
 *
 * Framework Compliance:
 * - Implements: WaysNX\BusinessFramework\Types\BusinessFunctionInterface
 * - Used by: Laravel BusinessFunction implementation
 * - Allows: Future framework implementations to use packages/types directly
 *
 * This adapter interface enables Laravel code to type-hint against Laravel-specific
 * interfaces while maintaining compatibility with the framework-independent contract.
 *
 * @extends FrameworkIndependentBusinessFunctionInterface
 * @package WaysNX\BusinessFramework\Contracts
 */
interface BusinessFunctionInterface extends FrameworkIndependentBusinessFunctionInterface
{
    // Laravel-specific extensions would go here if needed
}
