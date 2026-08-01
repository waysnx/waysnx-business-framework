<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * WorkflowStepException
 *
 * Thrown when a workflow step execution fails.
 *
 * Occurs when:
 * - Step not found
 * - Business function invocation fails
 * - Step validation fails
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class WorkflowStepException extends WorkflowException
{
}
