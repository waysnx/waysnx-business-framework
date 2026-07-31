<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

/**
 * EntityNotFoundException
 *
 * Thrown when an entity cannot be found in the registry.
 *
 * Occurs when:
 * - Entity ID not found
 * - Entity class not found
 * - Entity name not found
 * - Entity alias not found
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class EntityNotFoundException extends RegistryException
{
}
