<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * ValidationRuleException
 *
 * Thrown when a validation rule execution fails.
 *
 * Occurs when:
 * - Rule evaluation fails
 * - Rule validator not found
 * - Rule configuration invalid
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class ValidationRuleException extends ValidationFrameworkException
{
}
