<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * ValidationNotFoundException
 *
 * Thrown when a validation definition cannot be found in the registry.
 *
 * Occurs when:
 * - Validation ID not found
 * - Validation name not found
 * - Validation alias not found
 * - Validation by module not found
 * - Validation by scope not found
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class ValidationNotFoundException extends ValidationRegistryException
{
}
