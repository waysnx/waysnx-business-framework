<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * WorkflowExecutionException
 *
 * Thrown when workflow execution fails.
 *
 * Occurs when:
 * - Workflow definition not found
 * - Execution context is invalid
 * - Execution error occurs
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class WorkflowExecutionException extends WorkflowException
{
}
