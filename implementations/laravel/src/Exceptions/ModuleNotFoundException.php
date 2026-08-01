<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * ModuleNotFoundException
 *
 * Thrown when a module cannot be found in the registry.
 *
 * Occurs when:
 * - Module ID not found
 * - Module name not found
 * - Module not found by lookup criteria
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class ModuleNotFoundException extends ModuleRegistryException
{
}
