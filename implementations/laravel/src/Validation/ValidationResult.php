<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Validation;

/**
 * ValidationResult
 *
 * Immutable value object representing a validation result.
 *
 * Purpose:
 * Report validation execution outcomes and issues.
 *
 * Responsibilities:
 * - Store validation identification
 * - Store execution information
 * - Store validation status (passed/failed)
 * - Store validation issues
 * - Store execution time
 * - Aggregate result statistics
 * - Provide type-safe access to results
 *
 * Usage:
 * ```php
 * $result = new ValidationResult(
 *     validationId: 'customer-validation',
 *     executionId: 'exec-1',
 *     passed: false,
 *     issues: [$issue1, $issue2],
 *     executionTime: 15
 * );
 *
 * if (!$result->passed) {
 *     foreach ($result->errors() as $error) {
 *         echo $error->message;
 *     }
 * }
 * ```
 *
 * @package WaysNX\BusinessFramework\Validation
 */
class ValidationResult
{
    /**
     * The validation identifier
     *

     * @var string
     */
    public readonly string $validationId;

    /**
     * The execution identifier
     *

     * @var string
     */
    public readonly string $executionId;

    /**
     * Whether validation passed
     *

     * @var bool
     */
    public readonly bool $passed;

    /**
     * Validation issues
     *

     * @var array<ValidationIssue>
     */
    public readonly array $issues;

    /**
     * Execution time in milliseconds
     *

     * @var int
     */
    public readonly int $executionTime;

    /**
     * Additional metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new ValidationResult
     *

     * @param string $validationId The validation ID
     * @param string $executionId The execution ID
     * @param bool $passed Whether validation passed
     * @param array<ValidationIssue> $issues Validation issues
     * @param int $executionTime Execution time in milliseconds
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $validationId,
        string $executionId,
        bool $passed = true,
        array $issues = [],
        int $executionTime = 0,
        array $metadata = []
    ) {
        $this->validationId = $validationId;
        $this->executionId = $executionId;
        $this->passed = $passed;
        $this->issues = $issues;
        $this->executionTime = $executionTime;
        $this->metadata = $metadata;
    }

    /**
     * Check if validation failed
     *

     * @return bool True if validation failed
     */
    public function failed(): bool
    {
        return !$this->passed;
    }

    /**
     * Get validation errors
     *

     * @return array<ValidationIssue> Array of error issues
     */
    public function errors(): array
    {
        return array_filter(
            $this->issues,
            fn(ValidationIssue $issue) => $issue->isError()
        );
    }

    /**
     * Get validation warnings
     *

     * @return array<ValidationIssue> Array of warning issues
     */
    public function warnings(): array
    {
        return array_filter(
            $this->issues,
            fn(ValidationIssue $issue) => $issue->isWarning()
        );
    }

    /**
     * Get informational issues
     *

     * @return array<ValidationIssue> Array of info issues
     */
    public function info(): array
    {
        return array_filter(
            $this->issues,
            fn(ValidationIssue $issue) => $issue->isInfo()
        );
    }

    /**
     * Get total issue count
     *

     * @return int Total issues
     */
    public function issueCount(): int
    {
        return count($this->issues);
    }

    /**
     * Get error count
     *

     * @return int Total errors
     */
    public function errorCount(): int
    {
        return count($this->errors());
    }

    /**
     * Get warning count
     *

     * @return int Total warnings
     */
    public function warningCount(): int
    {
        return count($this->warnings());
    }

    /**
     * Get info count
     *

     * @return int Total info issues
     */
    public function infoCount(): int
    {
        return count($this->info());
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
     * Check if any issues exist
     *

     * @return bool True if issues exist
     */
    public function hasIssues(): bool
    {
        return $this->issueCount() > 0;
    }

    /**
     * Get summary message
     *

     * @return string Summary of result
     */
    public function summary(): string
    {
        if ($this->passed) {
            return 'Validation passed';
        }

        $parts = [];
        if ($this->hasErrors()) {
            $parts[] = $this->errorCount() . ' error' . ($this->errorCount() !== 1 ? 's' : '');
        }
        if ($this->hasWarnings()) {
            $parts[] = $this->warningCount() . ' warning' . ($this->warningCount() !== 1 ? 's' : '');
        }
        if ($this->infoCount() > 0) {
            $parts[] = $this->infoCount() . ' info message' . ($this->infoCount() !== 1 ? 's' : '');
        }

        return 'Validation failed: ' . implode(', ', $parts);
    }

    /**
     * Convert result to array
     *

     * @return array The result as array
     */
    public function toArray(): array
    {
        return [
            'validationId' => $this->validationId,
            'executionId' => $this->executionId,
            'passed' => $this->passed,
            'failed' => $this->failed(),
            'issues' => array_map(fn(ValidationIssue $issue) => $issue->toArray(), $this->issues),
            'issueCount' => $this->issueCount(),
            'errorCount' => $this->errorCount(),
            'warningCount' => $this->warningCount(),
            'infoCount' => $this->infoCount(),
            'executionTime' => $this->executionTime,
            'summary' => $this->summary(),
            'metadata' => $this->metadata,
        ];
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
}
