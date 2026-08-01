<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

use Exception;

/**
 * LifecycleException
 *
 * Base exception for all lifecycle manager-related errors.
 *
 * Thrown when a lifecycle operation fails for any reason.
 * Provides a common base for catching and handling lifecycle errors.
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class LifecycleException extends Exception
{
}
