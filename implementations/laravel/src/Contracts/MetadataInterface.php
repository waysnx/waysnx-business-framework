<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

use WaysNX\BusinessFramework\Types\MetadataInterface as FrameworkIndependentMetadataInterface;

/**
 * MetadataInterface
 *
 * Laravel-specific adapter for the framework-independent MetadataInterface.
 *
 * This interface extends the framework-independent contract defined in
 * WaysNX\BusinessFramework\Types\MetadataInterface, providing Laravel-specific
 * implementations and documentation.
 *
 * Framework Compliance:
 * - Implements: WaysNX\BusinessFramework\Types\MetadataInterface
 * - Used by: Laravel BaseModel implementation
 * - Allows: Future framework implementations to use packages/types directly
 *
 * @extends FrameworkIndependentMetadataInterface
 * @package WaysNX\BusinessFramework\Contracts
 */
interface MetadataInterface extends FrameworkIndependentMetadataInterface
{
    // Laravel-specific extensions would go here if needed
}
