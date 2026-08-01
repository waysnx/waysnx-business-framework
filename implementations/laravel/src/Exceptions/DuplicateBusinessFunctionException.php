<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * DuplicateBusinessFunctionException
 *
 * Thrown when attempting to register a duplicate business function.
 *
 * Occurs when:
 * - Function ID already registered
 * - Function name already registered
 * - Function alias already registered
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class DuplicateBusinessFunctionException extends BusinessFunctionRegistryException
{
}
