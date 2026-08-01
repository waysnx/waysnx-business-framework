<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * BusinessFunctionNotFoundException
 *
 * Thrown when a business function cannot be found in the registry.
 *
 * Occurs when:
 * - Function ID not found
 * - Function name not found
 * - Function alias not found
 * - Function by module not found
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class BusinessFunctionNotFoundException extends BusinessFunctionRegistryException
{
}
