<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Workflow;

/**
 * WorkflowStepExecution
 *
 * Immutable value object representing a workflow step execution.
 *
 * Purpose:
 * Track execution of a single workflow step.
 *
 * Responsibilities:
 * - Store step identification
 * - Store step status (pending, running, completed, failed, skipped)
 * - Store execution timeline
 * - Store result reference
 * - Store execution metadata
 * - Provide type-safe access to step execution properties
 *
 * Usage:
 * ```php
 * $stepExecution = new WorkflowStepExecution(
 *     stepId: 'step-1',
 *     businessFunctionId: 'create-customer',
 *     status: 'completed',
 *     startTime: time(),
 *     endTime: time() + 5,
 *     metadata: ['result' => 'success']
 * );
 *
 * echo $stepExecution->status;
 * ```
 *
 * @package WaysNX\BusinessFramework\Workflow
 */
class WorkflowStepExecution
{
    /**
     * The step identifier
     *

     * @var string
     */
    public readonly string $stepId;

    /**
     * The business function ID
     *

     * @var string
     */
    public readonly string $businessFunctionId;

    /**
     * The step status (pending, running, completed, failed, skipped)
     *

     * @var string
     */
    public readonly string $status;

    /**
     * The step start time
     *

     * @var int
     */
    public readonly int $startTime;

    /**
     * The step end time
     *

     * @var int
     */
    public readonly int $endTime;

    /**
     * Execution metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new WorkflowStepExecution
     *

     * @param string $stepId The step identifier
     * @param string $businessFunctionId The business function ID
     * @param string $status The step status
     * @param int $startTime The start time (Unix timestamp)
     * @param int $endTime The end time (Unix timestamp)
     * @param array $metadata Execution metadata
     */
    public function __construct(
        string $stepId,
        string $businessFunctionId,
        string $status = 'pending',
        int $startTime = 0,
        int $endTime = 0,
        array $metadata = []
    ) {
        $this->stepId = $stepId;
        $this->businessFunctionId = $businessFunctionId;
        $this->status = $status;
        $this->startTime = $startTime ?: time();
        $this->endTime = $endTime ?: $this->startTime;
        $this->metadata = $metadata;
    }

    /**
     * Check if step is pending
     *

     * @return bool True if pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if step is running
     *

     * @return bool True if running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if step is completed
     *

     * @return bool True if completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if step failed
     *

     * @return bool True if failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if step was skipped
     *

     * @return bool True if skipped
     */
    public function isSkipped(): bool
    {
        return $this->status === 'skipped';
    }

    /**
     * Get duration in milliseconds
     *

     * @return int Duration in milliseconds
     */
    public function duration(): int
    {
        return ($this->endTime - $this->startTime) * 1000;
    }

    /**
     * Get duration in seconds
     *

     * @return float Duration in seconds
     */
    public function durationSeconds(): float
    {
        return $this->endTime - $this->startTime;
    }

    /**
     * Get metadata value
     *

     * @param string $key The metadata key
     * @param mixed $default Default value
     *

     * @return mixed The metadata value
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Check if metadata key exists
     *

     * @param string $key The metadata key
     *

     * @return bool True if key exists
     */
    public function hasMetadata(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    /**
     * Convert step execution to array
     *

     * @return array The step execution as array
     */
    public function toArray(): array
    {
        return [
            'stepId' => $this->stepId,
            'businessFunctionId' => $this->businessFunctionId,
            'status' => $this->status,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'duration' => $this->duration(),
            'metadata' => $this->metadata,
        ];
    }
}
