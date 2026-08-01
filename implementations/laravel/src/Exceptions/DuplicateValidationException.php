<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * DuplicateValidationException
 *
 * Thrown when attempting to register a duplicate validation definition.
 *
 * Occurs when:
 * - Validation ID already registered
 * - Validation name already registered
 * - Validation alias already registered
 * - Validation rule ID already exists
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class DuplicateValidationException extends ValidationRegistryException
{
}
