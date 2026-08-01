<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Validation;

/**
 * ValidationContext
 *
 * Immutable value object carrying validation execution context.
 *
 * Purpose:
 * Carry contextual information during validation execution.
 *
 * Responsibilities:
 * - Store entity reference
 * - Store module, function, workflow context
 * - Store input data being validated
 * - Store execution metadata
 * - Maintain correlation ID
 * - Track timestamp
 * - Store validation options
 *
 * Usage:
 * ```php
 * $context = new ValidationContext(
 *     entity: $customer,
 *     moduleId: 'crm',
 *     businessFunctionId: 'create-customer',
 *     correlationId: 'req-123',
 *     inputData: ['email' => 'test@example.com'],
 *     options: ['stopOnError' => false]
 * );
 *
 * echo $context->entity->id;
 * ```
 *
 * @package WaysNX\BusinessFramework\Validation
 */
class ValidationContext
{
    /**
     * The entity being validated
     *

     * @var mixed
     */
    public readonly mixed $entity;

    /**
     * The module ID context
     *

     * @var string
     */
    public readonly string $moduleId;

    /**
     * The business function ID context
     *

     * @var string
     */
    public readonly string $businessFunctionId;

    /**
     * The workflow ID context
     *

     * @var string
     */
    public readonly string $workflowId;

    /**
     * The execution ID
     *

     * @var string
     */
    public readonly string $executionId;

    /**
     * The correlation ID for tracing
     *

     * @var string
     */
    public readonly string $correlationId;

    /**
     * The input data being validated
     *

     * @var array
     */
    public readonly array $inputData;

    /**
     * The execution timestamp
     *

     * @var int
     */
    public readonly int $timestamp;

    /**
     * Additional metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Validation execution options
     *

     * @var array
     */
    public readonly array $options;

    /**
     * Initialize a new ValidationContext
     *

     * @param mixed $entity The entity being validated
     * @param string $moduleId Module ID
     * @param string $businessFunctionId Business function ID
     * @param string $workflowId Workflow ID
     * @param string $executionId Execution ID
     * @param string $correlationId Correlation ID
     * @param array $inputData Input data being validated
     * @param int $timestamp Execution timestamp
     * @param array $metadata Additional metadata
     * @param array $options Validation options
     */
    public function __construct(
        mixed $entity = null,
        string $moduleId = '',
        string $businessFunctionId = '',
        string $workflowId = '',
        string $executionId = '',
        string $correlationId = '',
        array $inputData = [],
        int $timestamp = 0,
        array $metadata = [],
        array $options = []
    ) {
        $this->entity = $entity;
        $this->moduleId = $moduleId;
        $this->businessFunctionId = $businessFunctionId;
        $this->workflowId = $workflowId;
        $this->executionId = $executionId;
        $this->correlationId = $correlationId;
        $this->inputData = $inputData;
        $this->timestamp = $timestamp ?: time();
        $this->metadata = $metadata;
        $this->options = $options;
    }

    /**
     * Get validation option value
     *

     * @param string $key The option key
     * @param mixed $default Default value
     *

     * @return mixed The option value
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Check if validation option exists
     *

     * @param string $key The option key
     *

     * @return bool True if option exists
     */
    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * Check if stop on error option is enabled
     *

     * @return bool True if stop on error enabled
     */
    public function stopOnError(): bool
    {
        return $this->getOption('stopOnError', false) === true;
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
     * Convert context to array
     *

     * @return array The context as array
     */
    public function toArray(): array
    {
        return [
            'moduleId' => $this->moduleId,
            'businessFunctionId' => $this->businessFunctionId,
            'workflowId' => $this->workflowId,
            'executionId' => $this->executionId,
            'correlationId' => $this->correlationId,
            'timestamp' => $this->timestamp,
            'metadata' => $this->metadata,
            'options' => $this->options,
        ];
    }
}
