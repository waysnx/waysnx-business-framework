<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * DuplicateWorkflowException
 *
 * Thrown when attempting to register a duplicate workflow.
 *
 * Occurs when:
 * - Workflow ID already registered
 * - Workflow name already registered
 * - Workflow alias already registered
 * - Workflow step ID already exists
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class DuplicateWorkflowException extends WorkflowRegistryException
{
}
