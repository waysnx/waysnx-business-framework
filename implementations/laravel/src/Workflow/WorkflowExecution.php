<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Workflow;

/**
 * WorkflowExecution
 *
 * Immutable value object representing a workflow execution.
 *
 * Purpose:
 * Track overall workflow execution state and progress.
 *
 * Responsibilities:
 * - Store execution identification
 * - Store workflow reference
 * - Store current step
 * - Track completed, pending, skipped steps
 * - Store execution state (pending, running, completed, failed)
 * - Store execution timeline
 * - Store execution metadata
 * - Provide type-safe access to execution properties
 *
 * Usage:
 * ```php
 * $execution = new WorkflowExecution(
 *     id: 'exec-1',
 *     workflowId: 'employee-onboarding',
 *     currentStep: 'step-2',
 *     completedSteps: ['step-1'],
 *     pendingSteps: ['step-2', 'step-3'],
 *     state: 'running'
 * );
 *
 * echo $execution->currentStep;
 * ```
 *
 * @package WaysNX\BusinessFramework\Workflow
 */
class WorkflowExecution
{
    /**
     * The execution identifier
     *

     * @var string
     */
    public readonly string $id;

    /**
     * The workflow ID
     *

     * @var string
     */
    public readonly string $workflowId;

    /**
     * The current step ID
     *

     * @var string
     */
    public readonly string $currentStep;

    /**
     * Completed step IDs
     *

     * @var array<string>
     */
    public readonly array $completedSteps;

    /**
     * Pending step IDs
     *

     * @var array<string>
     */
    public readonly array $pendingSteps;

    /**
     * Skipped step IDs
     *

     * @var array<string>
     */
    public readonly array $skippedSteps;

    /**
     * The execution state (pending, running, completed, failed)
     *

     * @var string
     */
    public readonly string $state;

    /**
     * The execution start time
     *

     * @var int
     */
    public readonly int $startTime;

    /**
     * The execution end time
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
     * Initialize a new WorkflowExecution
     *

     * @param string $id The execution identifier
     * @param string $workflowId The workflow ID
     * @param string $currentStep The current step ID
     * @param array<string> $completedSteps Completed step IDs
     * @param array<string> $pendingSteps Pending step IDs
     * @param array<string> $skippedSteps Skipped step IDs
     * @param string $state The execution state
     * @param int $startTime The start time (Unix timestamp)
     * @param int $endTime The end time (Unix timestamp)
     * @param array $metadata Execution metadata
     */
    public function __construct(
        string $id,
        string $workflowId,
        string $currentStep = '',
        array $completedSteps = [],
        array $pendingSteps = [],
        array $skippedSteps = [],
        string $state = 'pending',
        int $startTime = 0,
        int $endTime = 0,
        array $metadata = []
    ) {
        $this->id = $id;
        $this->workflowId = $workflowId;
        $this->currentStep = $currentStep;
        $this->completedSteps = $completedSteps;
        $this->pendingSteps = $pendingSteps;
        $this->skippedSteps = $skippedSteps;
        $this->state = $state;
        $this->startTime = $startTime ?: time();
        $this->endTime = $endTime ?: $this->startTime;
        $this->metadata = $metadata;
    }

    /**
     * Check if execution is pending
     *

     * @return bool True if pending
     */
    public function isPending(): bool
    {
        return $this->state === 'pending';
    }

    /**
     * Check if execution is running
     *

     * @return bool True if running
     */
    public function isRunning(): bool
    {
        return $this->state === 'running';
    }

    /**
     * Check if execution is completed
     *

     * @return bool True if completed
     */
    public function isCompleted(): bool
    {
        return $this->state === 'completed';
    }

    /**
     * Check if execution failed
     *

     * @return bool True if failed
     */
    public function isFailed(): bool
    {
        return $this->state === 'failed';
    }

    /**
     * Get total steps
     *

     * @return int Total steps executed
     */
    public function totalSteps(): int
    {
        return count($this->completedSteps) + count($this->pendingSteps) + count($this->skippedSteps);
    }

    /**
     * Get progress percentage
     *

     * @return float Progress percentage (0-100)
     */
    public function progress(): float
    {
        $total = $this->totalSteps();
        if ($total === 0) {
            return 0.0;
        }

        return (count($this->completedSteps) / $total) * 100;
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
     * Convert execution to array
     *

     * @return array The execution as array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'workflowId' => $this->workflowId,
            'currentStep' => $this->currentStep,
            'completedSteps' => $this->completedSteps,
            'pendingSteps' => $this->pendingSteps,
            'skippedSteps' => $this->skippedSteps,
            'state' => $this->state,
            'totalSteps' => $this->totalSteps(),
            'progress' => $this->progress(),
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'duration' => $this->duration(),
            'metadata' => $this->metadata,
        ];
    }
}
