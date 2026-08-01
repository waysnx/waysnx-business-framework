<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

/**
 * ValidationRuleDefinition
 *
 * Immutable value object representing a validation rule definition.
 *
 * Encapsulates a single validation rule within a validation definition.
 * Each rule defines what should be validated and how failures should be reported.
 *
 * Purpose:
 * Represent a single validation rule with its properties and metadata.
 *
 * Responsibilities:
 * - Store rule identification (id, name)
 * - Store rule type and target
 * - Store rule expression (metadata only, not executed)
 * - Store error information (message, code)
 * - Store rule metadata (severity, priority)
 * - Provide type-safe access to rule properties
 *
 * Usage:
 * ```php
 * $rule = new ValidationRuleDefinition(
 *     id: 'email-format',
 *     name: 'EmailFormat',
 *     ruleType: 'format',
 *     target: 'email',
 *     message: 'Invalid email format',
 *     errorCode: 'VAL_EMAIL_001',
 *     severity: 'error',
 *     priority: 10,
 *     metadata: ['pattern' => '/^[^@]+@[^@]+\.[^@]+$/']
 * );
 * ```
 *
 * Immutability:
 * Once created, ValidationRuleDefinition cannot be modified.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class ValidationRuleDefinition
{
    /**
     * The rule identifier
     *

     * @var string
     */
    public readonly string $id;

    /**
     * The rule name
     *

     * @var string
     */
    public readonly string $name;

    /**
     * The rule type (format, range, required, custom, etc.)
     *

     * @var string
     */
    public readonly string $ruleType;

    /**
     * The target being validated
     *

     * @var string
     */
    public readonly string $target;

    /**
     * Error message for validation failure
     *

     * @var string
     */
    public readonly string $message;

    /**
     * Error code for validation failure
     *

     * @var string
     */
    public readonly string $errorCode;

    /**
     * Severity level (error, warning, info)
     *

     * @var string
     */
    public readonly string $severity;

    /**
     * Rule priority
     *

     * @var int
     */
    public readonly int $priority;

    /**
     * Rule metadata (expression, pattern, conditions, etc.)
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new ValidationRuleDefinition
     *

     * @param string $id The rule identifier
     * @param string $name The rule name
     * @param string $ruleType The rule type
     * @param string $target The target being validated
     * @param string $message Error message
     * @param string $errorCode Error code
     * @param string $severity Severity level
     * @param int $priority Rule priority
     * @param array $metadata Rule metadata
     */
    public function __construct(
        string $id,
        string $name,
        string $ruleType = 'custom',
        string $target = '',
        string $message = '',
        string $errorCode = '',
        string $severity = 'error',
        int $priority = 0,
        array $metadata = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->ruleType = $ruleType;
        $this->target = $target;
        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->severity = $severity;
        $this->priority = $priority;
        $this->metadata = $metadata;
    }

    /**
     * Convert rule to array
     *

     * @return array The rule as array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ruleType' => $this->ruleType,
            'target' => $this->target,
            'message' => $this->message,
            'errorCode' => $this->errorCode,
            'severity' => $this->severity,
            'priority' => $this->priority,
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

    /**
     * Check if rule severity is error
     *

     * @return bool True if error severity
     */
    public function isError(): bool
    {
        return $this->severity === 'error';
    }

    /**
     * Check if rule severity is warning
     *

     * @return bool True if warning severity
     */
    public function isWarning(): bool
    {
        return $this->severity === 'warning';
    }

    /**
     * Check if rule severity is info
     *

     * @return bool True if info severity
     */
    public function isInfo(): bool
    {
        return $this->severity === 'info';
    }
}
