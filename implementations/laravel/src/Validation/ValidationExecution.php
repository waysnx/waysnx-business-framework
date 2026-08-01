<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Validation;

/**
 * ValidationExecution
 *
 * Immutable value object representing a validation execution.
 *
 * Purpose:
 * Track validation execution metadata.
 *
 * Responsibilities:
 * - Store execution identification
 * - Store execution timeline
 * - Track rule execution count
 * - Store execution status
 *
 * Usage:
 * ```php
 * $execution = new ValidationExecution(
 *     id: 'exec-1',
 *     validationId: 'customer-validation',
 *     startTime: time(),
 *     endTime: time() + 10,
 *     rulesExecuted: 5
 * );
 *
 * echo $execution->duration();
 * ```
 *
 * @package WaysNX\BusinessFramework\Validation
 */
class ValidationExecution
{
    /**
     * The execution identifier
     *

     * @var string
     */
    public readonly string $id;

    /**
     * The validation ID
     *

     * @var string
     */
    public readonly string $validationId;

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
     * Number of rules executed
     *

     * @var int
     */
    public readonly int $rulesExecuted;

    /**
     * Initialize a new ValidationExecution
     *

     * @param string $id The execution identifier
     * @param string $validationId The validation ID
     * @param int $startTime The start time (Unix timestamp)
     * @param int $endTime The end time (Unix timestamp)
     * @param int $rulesExecuted Number of rules executed
     */
    public function __construct(
        string $id,
        string $validationId,
        int $startTime = 0,
        int $endTime = 0,
        int $rulesExecuted = 0
    ) {
        $this->id = $id;
        $this->validationId = $validationId;
        $this->startTime = $startTime ?: time();
        $this->endTime = $endTime ?: $this->startTime;
        $this->rulesExecuted = $rulesExecuted;
    }

    /**
     * Get execution duration in milliseconds
     *

     * @return int Duration in milliseconds
     */
    public function duration(): int
    {
        return ($this->endTime - $this->startTime) * 1000;
    }

    /**
     * Get execution duration in seconds
     *

     * @return float Duration in seconds
     */
    public function durationSeconds(): float
    {
        return $this->endTime - $this->startTime;
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
            'validationId' => $this->validationId,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'rulesExecuted' => $this->rulesExecuted,
            'duration' => $this->duration(),
        ];
    }
}
