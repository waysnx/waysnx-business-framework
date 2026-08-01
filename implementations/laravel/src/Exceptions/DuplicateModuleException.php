<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * DuplicateModuleException
 *
 * Thrown when attempting to register a duplicate module.
 *
 * Occurs when:
 * - Module ID already registered
 * - Module name already registered
 * - Module namespace already registered
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class DuplicateModuleException extends ModuleRegistryException
{
}
