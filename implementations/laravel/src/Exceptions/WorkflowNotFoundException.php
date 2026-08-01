<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * WorkflowNotFoundException
 *
 * Thrown when a workflow cannot be found in the registry.
 *
 * Occurs when:
 * - Workflow ID not found
 * - Workflow name not found
 * - Workflow alias not found
 * - Workflow by module not found
 * - Workflow by trigger not found
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class WorkflowNotFoundException extends WorkflowRegistryException
{
}
