<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Workflow;

/**
 * WorkflowResult
 *
 * Immutable value object representing a workflow execution result.
 *
 * Purpose:
 * Report workflow execution outcomes and statistics.
 *
 * Responsibilities:
 * - Store execution identification
 * - Store workflow reference
 * - Store execution status
 * - Store completed/failed steps
 * - Store duration and metadata
 * - Aggregate result statistics
 * - Provide type-safe access to results
 *
 * Usage:
 * ```php
 * $result = new WorkflowResult(
 *     executionId: 'exec-1',
 *     workflowId: 'employee-onboarding',
 *     status: 'completed',
 *     completedSteps: ['step-1', 'step-2'],
 *     failedSteps: [],
 *     duration: 5000
 * );
 *
 * if ($result->succeeded()) {
 *     echo "Workflow completed";
 * }
 * ```
 *
 * @package WaysNX\BusinessFramework\Workflow
 */
class WorkflowResult
{
    /**
     * The execution identifier
     *

     * @var string
     */
    public readonly string $executionId;

    /**
     * The workflow identifier
     *

     * @var string
     */
    public readonly string $workflowId;

    /**
     * The result status (completed, failed)
     *

     * @var string
     */
    public readonly string $status;

    /**
     * Completed step IDs
     *

     * @var array<string>
     */
    public readonly array $completedSteps;

    /**
     * Failed step IDs
     *

     * @var array<string>
     */
    public readonly array $failedSteps;

    /**
     * Warnings
     *

     * @var array<string>
     */
    public readonly array $warnings;

    /**
     * Errors
     *

     * @var array<string>
     */
    public readonly array $errors;

    /**
     * Duration in milliseconds
     *

     * @var int
     */
    public readonly int $duration;

    /**
     * Additional metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new WorkflowResult
     *

     * @param string $executionId The execution ID
     * @param string $workflowId The workflow ID
     * @param string $status The result status
     * @param array<string> $completedSteps Completed step IDs
     * @param array<string> $failedSteps Failed step IDs
     * @param array<string> $warnings Warnings
     * @param array<string> $errors Errors
     * @param int $duration Duration in milliseconds
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $executionId,
        string $workflowId,
        string $status = 'completed',
        array $completedSteps = [],
        array $failedSteps = [],
        array $warnings = [],
        array $errors = [],
        int $duration = 0,
        array $metadata = []
    ) {
        $this->executionId = $executionId;
        $this->workflowId = $workflowId;
        $this->status = $status;
        $this->completedSteps = $completedSteps;
        $this->failedSteps = $failedSteps;
        $this->warnings = $warnings;
        $this->errors = $errors;
        $this->duration = $duration;
        $this->metadata = $metadata;
    }

    /**
     * Check if workflow succeeded
     *

     * @return bool True if succeeded
     */
    public function succeeded(): bool
    {
        return $this->status === 'completed' && empty($this->failedSteps);
    }

    /**
     * Check if workflow failed
     *

     * @return bool True if failed
     */
    public function failed(): bool
    {
        return $this->status === 'failed' || !empty($this->failedSteps);
    }

    /**
     * Get total steps executed
     *

     * @return int Total steps
     */
    public function totalSteps(): int
    {
        return count($this->completedSteps) + count($this->failedSteps);
    }

    /**
     * Get completion percentage
     *

     * @return float Completion percentage (0-100)
     */
    public function completionPercentage(): float
    {
        $total = $this->totalSteps();
        if ($total === 0) {
            return 0.0;
        }

        return (count($this->completedSteps) / $total) * 100;
    }

    /**
     * Get error count
     *

     * @return int Total errors
     */
    public function errorCount(): int
    {
        return count($this->errors);
    }

    /**
     * Get warning count
     *

     * @return int Total warnings
     */
    public function warningCount(): int
    {
        return count($this->warnings);
    }

    /**
     * Check if any errors exist
     *

     * @return bool True if errors exist
     */
    public function hasErrors(): bool
    {
        return $this->errorCount() > 0;
    }

    /**
     * Check if any warnings exist
     *

     * @return bool True if warnings exist
     */
    public function hasWarnings(): bool
    {
        return $this->warningCount() > 0;
    }

    /**
     * Get summary message
     *

     * @return string Summary of result
     */
    public function summary(): string
    {
        if ($this->succeeded()) {
            return "Workflow '{$this->workflowId}' completed successfully";
        }

        $parts = [];
        if ($this->hasErrors()) {
            $parts[] = $this->errorCount() . ' error' . ($this->errorCount() !== 1 ? 's' : '');
        }
        if ($this->hasWarnings()) {
            $parts[] = $this->warningCount() . ' warning' . ($this->warningCount() !== 1 ? 's' : '');
        }

        return "Workflow '{$this->workflowId}' failed: " . implode(', ', $parts);
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
     * Convert result to array
     *

     * @return array The result as array
     */
    public function toArray(): array
    {
        return [
            'executionId' => $this->executionId,
            'workflowId' => $this->workflowId,
            'status' => $this->status,
            'succeeded' => $this->succeeded(),
            'failed' => $this->failed(),
            'completedSteps' => $this->completedSteps,
            'failedSteps' => $this->failedSteps,
            'totalSteps' => $this->totalSteps(),
            'completionPercentage' => $this->completionPercentage(),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'errorCount' => $this->errorCount(),
            'warningCount' => $this->warningCount(),
            'duration' => $this->duration,
            'summary' => $this->summary(),
            'metadata' => $this->metadata,
        ];
    }
}
