<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * DuplicateEntityException
 *
 * Thrown when attempting to register a duplicate entity.
 *
 * Occurs when:
 * - Entity ID already registered
 * - Entity class already registered
 * - Entity name already registered
 * - Entity alias already registered
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class DuplicateEntityException extends RegistryException
{
}
