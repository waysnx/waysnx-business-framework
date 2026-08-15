<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Models;

use WaysNX\BusinessFramework\Core\BusinessFunctionAbstract;
use WaysNX\BusinessFramework\Contracts\BusinessFunctionInterface;
use JsonSerializable;

/**
 * BusinessFunction
 *
 * Laravel-specific implementation of Business Functions.
 *
 * This class provides:
 * - Framework-independent business function model (from BusinessFunctionAbstract)
 * - Laravel-specific integration
 * - JSON serialization support for Laravel
 * - Laravel contract implementation
 *
 * Architecture:
 * - Extends BusinessFunctionAbstract (framework-independent)
 * - Implements Laravel contracts
 * - Uses Laravel-specific features (if needed)
 * - Remains compatible with the WBF specification
 *
 * Usage:
 * ```php
 * class ApplyLeave extends BusinessFunction {
 *     public function __construct() {
 *         // Set up business function definition
 *         $this->functionId = 'HR.LEAVE.APPLY.APPLY_LEAVE';
 *         $this->functionName = 'Apply Leave';
 *         $this->functionVersion = '1.0.0';
 *         // ... other properties ...
 *         
 *         // Initialize
 *         $this->initializeBusinessFunction();
 *     }
 *
 *     protected function executeBusiness(array $request): array {
 *         // Business logic here
 *         return ['leaveRequestId' => '...', 'status' => 'Pending'];
 *     }
 * }
 * ```
 *
 * @extends BusinessFunctionAbstract
 * @implements BusinessFunctionInterface
 * @implements JsonSerializable
 * @package WaysNX\BusinessFramework\Models
 */
class BusinessFunction extends BusinessFunctionAbstract implements BusinessFunctionInterface, JsonSerializable
{
    /**
     * Generate a unique UUID for the entity
     *
     * Uses Ramsey\Uuid for Laravel implementation.
     *
     * @return string
     */
    protected function generateUuid(): string
    {
        // In real Laravel applications, use Ramsey\Uuid directly
        // This is a placeholder for testing without requiring the library
        return 'business-function-' . uniqid();
    }

    /**
     * Initialize audit timestamps
     *
     * Sets created, updated, and deleted timestamps to DateTimeImmutable.
     *
     * @return void
     */
    protected function initializeAuditTimestamps(): void
    {
        $now = new \DateTimeImmutable('now');
        $this->createdAt = $now;
        $this->updatedAt = null;
        $this->deletedAt = null;
    }

    /**
     * Update a specific timestamp
     *
     * @return void
     */
    protected function updateTimestamp(): void
    {
        // Placeholder for testing - subclasses can override
    }

    /**
     * Delete timestamp
     *
     * Sets the deletion timestamp (soft delete).
     *
     * @return void
     */
    protected function deleteTimestamp(): void
    {
        $this->deletedAt = new \DateTimeImmutable('now');
    }

    /**
     * Execute Business Logic
     *
     * Subclasses of BusinessFunction must override this to implement their business logic.
     * This default implementation returns a placeholder.
     *
     * @param array $request The validated request
     * @return array The business result
     */
    protected function executeBusiness(array $request): array
    {
        return ['status' => 'success'];
    }

    /**
     * Convert the Business Function to a JSON-serializable array
     *
     * Enables direct JSON serialization in Laravel contexts.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Convert the Business Function Definition to an array
     *
     * Returns the complete contract definition in array format.
     * Inherits from BaseModelAbstract with extended information.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getCompleteContractDefinition();
    }

    /**
     * Convert the Business Function Definition to JSON
     *
     * @param int $options JSON encoding options
     * @return string
     * @throws \JsonException
     */
    public function toJson(int $options = 0): string
    {
        $defaultOptions = \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR;
        return json_encode($this->jsonSerialize(), $defaultOptions | $options);
    }

    /**
     * Get a string representation of the Business Function
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'BusinessFunction(%s | %s v%s)',
            $this->getFunctionId(),
            $this->getFunctionName(),
            $this->getFunctionVersion()
        );
    }
}
