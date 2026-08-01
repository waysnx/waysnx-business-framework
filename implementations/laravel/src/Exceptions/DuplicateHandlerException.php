<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * DuplicateHandlerException
 *
 * Thrown when attempting to register a duplicate handler.
 *
 * Occurs when:
 * - Handler ID already registered
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class DuplicateHandlerException extends LifecycleException
{
}
