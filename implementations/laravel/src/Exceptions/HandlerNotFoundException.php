<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * HandlerNotFoundException
 *
 * Thrown when a lifecycle handler cannot be found.
 *
 * Occurs when:
 * - Handler ID not found
 * - Attempting to unregister non-existent handler
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class HandlerNotFoundException extends LifecycleException
{
}
