<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * WorkflowException
 *
 * Base exception for all workflow-related errors.
 *
 * Thrown when a workflow operation fails for any reason.
 * Provides a common base for catching and handling workflow errors.
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class WorkflowException extends LifecycleException
{
}
