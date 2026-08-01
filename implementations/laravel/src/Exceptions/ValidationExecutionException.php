<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * ValidationExecutionException
 *
 * Thrown when validation execution fails.
 *
 * Occurs when:
 * - Validation definition not found
 * - Context is invalid
 * - Execution error occurs
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class ValidationExecutionException extends ValidationFrameworkException
{
}
