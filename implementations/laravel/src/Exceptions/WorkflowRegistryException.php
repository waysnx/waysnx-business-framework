<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

use Exception;

/**
 * WorkflowRegistryException
 *
 * Base exception for all workflow registry-related errors.
 *
 * Thrown when a workflow registry operation fails for any reason.
 * Provides a common base for catching and handling registry errors.
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class WorkflowRegistryException extends Exception
{
}
