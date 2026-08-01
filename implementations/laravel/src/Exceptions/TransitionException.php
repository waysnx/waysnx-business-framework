<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * TransitionException
 *
 * Thrown when a workflow transition fails.
 *
 * Occurs when:
 * - Transition not found
 * - Transition validation fails
 * - Transition metadata is invalid
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class TransitionException extends WorkflowException
{
}
