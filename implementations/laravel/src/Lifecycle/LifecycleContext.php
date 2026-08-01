<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Lifecycle;

/**
 * LifecycleContext
 *
 * Immutable value object carrying lifecycle event context.
 *
 * Purpose:
 * Carry contextual information during lifecycle event processing.
 *
 * Responsibilities:
 * - Store entity reference
 * - Store module, function, workflow, validation context
 * - Store execution metadata
 * - Maintain correlation ID
 * - Track timestamp
 *
 * Usage:
 * ```php
 * $context = new LifecycleContext(
 *     entityId: 'customer-1',
 *     entityType: 'Customer',
 *     moduleId: 'crm',
 *     businessFunctionId: 'create-customer',
 *     correlationId: 'req-123',
 *     metadata: ['userId' => 456]
 * );
 *
 * echo $context->entityId;
 * ```
 *
 * @package WaysNX\BusinessFramework\Lifecycle
 */
class LifecycleContext
{
    /**
     * Entity ID being processed
     *

     * @var string
     */
    public readonly string $entityId;

    /**
     * Entity type
     *

     * @var string
     */
    public readonly string $entityType;

    /**
     * Module ID context
     *

     * @var string
     */
    public readonly string $moduleId;

    /**
     * Business function ID context
     *

     * @var string
     */
    public readonly string $businessFunctionId;

    /**
     * Workflow ID context
     *

     * @var string
     */
    public readonly string $workflowId;

    /**
     * Validation ID context
     *

     * @var string
     */
    public readonly string $validationId;

    /**
     * Correlation ID for tracing
     *

     * @var string
     */
    public readonly string $correlationId;

    /**
     * Event timestamp
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
     * Initialize a new LifecycleContext
     *

     * @param string $entityId Entity ID
     * @param string $entityType Entity type
     * @param string $moduleId Module ID
     * @param string $businessFunctionId Business function ID
     * @param string $workflowId Workflow ID
     * @param string $validationId Validation ID
     * @param string $correlationId Correlation ID
     * @param int $timestamp Event timestamp
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $entityId = '',
        string $entityType = '',
        string $moduleId = '',
        string $businessFunctionId = '',
        string $workflowId = '',
        string $validationId = '',
        string $correlationId = '',
        int $timestamp = 0,
        array $metadata = []
    ) {
        $this->entityId = $entityId;
        $this->entityType = $entityType;
        $this->moduleId = $moduleId;
        $this->businessFunctionId = $businessFunctionId;
        $this->workflowId = $workflowId;
        $this->validationId = $validationId;
        $this->correlationId = $correlationId;
        $this->timestamp = $timestamp ?: time();
        $this->metadata = $metadata;
    }

    /**
     * Convert context to array
     *

     * @return array The context as array
     */
    public function toArray(): array
    {
        return [
            'entityId' => $this->entityId,
            'entityType' => $this->entityType,
            'moduleId' => $this->moduleId,
            'businessFunctionId' => $this->businessFunctionId,
            'workflowId' => $this->workflowId,
            'validationId' => $this->validationId,
            'correlationId' => $this->correlationId,
            'timestamp' => $this->timestamp,
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
