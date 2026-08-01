<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Workflow;

/**
 * WorkflowContext
 *
 * Immutable value object carrying workflow execution context.
 *
 * Purpose:
 * Carry contextual information during workflow execution.
 *
 * Responsibilities:
 * - Store workflow reference
 * - Store execution identification
 * - Store entity and module context
 * - Store input data
 * - Store execution options and metadata
 * - Maintain correlation ID
 * - Track timestamp
 *
 * Usage:
 * ```php
 * $context = new WorkflowContext(
 *     workflowId: 'employee-onboarding',
 *     executionId: 'exec-1',
 *     entity: $employee,
 *     moduleId: 'hr',
 *     correlationId: 'req-123',
 *     inputData: ['firstName' => 'John'],
 *     options: ['async' => false]
 * );
 *
 * echo $context->workflowId;
 * ```
 *
 * @package WaysNX\BusinessFramework\Workflow
 */
class WorkflowContext
{
    /**
     * The workflow ID
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
     * The entity being processed
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
     * The business function context
     *

     * @var string
     */
    public readonly string $businessFunctionId;

    /**
     * The input data for workflow
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
     * Execution options
     *

     * @var array
     */
    public readonly array $options;

    /**
     * Initialize a new WorkflowContext
     *

     * @param string $workflowId The workflow ID
     * @param string $executionId The execution ID
     * @param string $correlationId The correlation ID
     * @param mixed $entity The entity being processed
     * @param string $moduleId The module ID
     * @param string $businessFunctionId The business function context
     * @param array $inputData The input data
     * @param int $timestamp The execution timestamp
     * @param array $metadata Additional metadata
     * @param array $options Execution options
     */
    public function __construct(
        string $workflowId,
        string $executionId,
        string $correlationId = '',
        mixed $entity = null,
        string $moduleId = '',
        string $businessFunctionId = '',
        array $inputData = [],
        int $timestamp = 0,
        array $metadata = [],
        array $options = []
    ) {
        $this->workflowId = $workflowId;
        $this->executionId = $executionId;
        $this->correlationId = $correlationId;
        $this->entity = $entity;
        $this->moduleId = $moduleId;
        $this->businessFunctionId = $businessFunctionId;
        $this->inputData = $inputData;
        $this->timestamp = $timestamp ?: time();
        $this->metadata = $metadata;
        $this->options = $options;
    }

    /**
     * Get execution option value
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
     * Check if option exists
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
            'workflowId' => $this->workflowId,
            'executionId' => $this->executionId,
            'correlationId' => $this->correlationId,
            'moduleId' => $this->moduleId,
            'businessFunctionId' => $this->businessFunctionId,
            'timestamp' => $this->timestamp,
            'metadata' => $this->metadata,
            'options' => $this->options,
        ];
    }
}
