<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

use Exception;

/**
 * RepositoryException
 *
 * Base exception for repository-related errors.
 *
 * This exception is thrown when a generic repository operation fails.
 * More specific exceptions (like EntityNotFoundException) should extend this.
 *
 * Responsibilities:
 * - Provide a base exception class for repository errors
 * - Allow catching all repository-related exceptions
 * - Support proper exception hierarchy
 *
 * Usage:
 * ```php
 * try {
 *     $entity = $repository->findOrFail($id);
 * } catch (RepositoryException $e) {
 *     // Handle repository error
 * }
 * ```
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class RepositoryException extends Exception
{
    /**
     * Create a new RepositoryException instance
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $message = 'Repository operation failed',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
