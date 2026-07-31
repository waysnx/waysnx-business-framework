<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * EntityNotFoundException
 *
 * Exception thrown when an entity cannot be found in the repository.
 *
 * This exception is thrown by findOrFail() and similar methods when
 * an entity with the requested ID or criteria is not found.
 *
 * Responsibilities:
 * - Indicate that an entity was not found
 * - Provide entity ID and class information
 * - Allow targeted error handling for missing entities
 *
 * Usage:
 * ```php
 * try {
 *     $project = $repository->findOrFail('non-existent-id');
 * } catch (EntityNotFoundException $e) {
 *     // Entity not found - handle 404 or retry
 * }
 * ```
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class EntityNotFoundException extends RepositoryException
{
    /**
     * The entity ID that was not found
     *
     * @var string|int|null
     */
    protected string|int|null $entityId;

    /**
     * The entity class name
     *
     * @var string|null
     */
    protected ?string $entityClass;

    /**
     * Create a new EntityNotFoundException instance
     *
     * @param string|int|null $entityId The ID of the entity that was not found
     * @param string|null $entityClass The class of the entity
     * @param string|null $message Custom error message
     */
    public function __construct(
        string|int|null $entityId = null,
        ?string $entityClass = null,
        ?string $message = null
    ) {
        $this->entityId = $entityId;
        $this->entityClass = $entityClass;

        if ($message === null) {
            $class = $entityClass ? (new \ReflectionClass($entityClass))->getShortName() : 'Entity';
            $message = "No {$class} found with ID: {$entityId}";
        }

        parent::__construct($message, 0, null);
    }

    /**
     * Get the entity ID that was not found
     *
     * @return string|int|null The entity ID
     */
    public function getEntityId(): string|int|null
    {
        return $this->entityId;
    }

    /**
     * Get the entity class name
     *
     * @return string|null The entity class
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }
}
