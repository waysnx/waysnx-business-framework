<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Validation;

/**
 * ValidationIssue
 *
 * Immutable value object representing a validation issue.
 *
 * Purpose:
 * Represent a single validation failure or warning.
 *
 * Responsibilities:
 * - Store issue identification
 * - Store issue severity and category
 * - Store error information
 * - Store issue metadata
 * - Provide type-safe access to issue properties
 *
 * Usage:
 * ```php
 * $issue = new ValidationIssue(
 *     id: 'issue-1',
 *     ruleId: 'email-format',
 *     severity: 'error',
 *     message: 'Invalid email format',
 *     target: 'email',
 *     errorCode: 'VAL_EMAIL_001',
 *     category: 'format'
 * );
 *
 * echo $issue->message;
 * ```
 *
 * @package WaysNX\BusinessFramework\Validation
 */
class ValidationIssue
{
    /**
     * The issue identifier
     *

     * @var string
     */
    public readonly string $id;

    /**
     * The rule ID that triggered this issue
     *

     * @var string
     */
    public readonly string $ruleId;

    /**
     * The severity level (error, warning, info)
     *

     * @var string
     */
    public readonly string $severity;

    /**
     * The error message
     *

     * @var string
     */
    public readonly string $message;

    /**
     * The target being validated
     *

     * @var string
     */
    public readonly string $target;

    /**
     * The error code
     *

     * @var string
     */
    public readonly string $errorCode;

    /**
     * The issue category
     *

     * @var string
     */
    public readonly string $category;

    /**
     * Additional metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new ValidationIssue
     *

     * @param string $id The issue identifier
     * @param string $ruleId The rule ID
     * @param string $severity The severity level
     * @param string $message The error message
     * @param string $target The target being validated
     * @param string $errorCode The error code
     * @param string $category The issue category
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $id,
        string $ruleId,
        string $severity = 'error',
        string $message = '',
        string $target = '',
        string $errorCode = '',
        string $category = '',
        array $metadata = []
    ) {
        $this->id = $id;
        $this->ruleId = $ruleId;
        $this->severity = $severity;
        $this->message = $message;
        $this->target = $target;
        $this->errorCode = $errorCode;
        $this->category = $category;
        $this->metadata = $metadata;
    }

    /**
     * Check if issue is error
     *

     * @return bool True if error severity
     */
    public function isError(): bool
    {
        return $this->severity === 'error';
    }

    /**
     * Check if issue is warning
     *

     * @return bool True if warning severity
     */
    public function isWarning(): bool
    {
        return $this->severity === 'warning';
    }

    /**
     * Check if issue is info
     *

     * @return bool True if info severity
     */
    public function isInfo(): bool
    {
        return $this->severity === 'info';
    }

    /**
     * Convert issue to array
     *

     * @return array The issue as array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ruleId' => $this->ruleId,
            'severity' => $this->severity,
            'message' => $this->message,
            'target' => $this->target,
            'errorCode' => $this->errorCode,
            'category' => $this->category,
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
