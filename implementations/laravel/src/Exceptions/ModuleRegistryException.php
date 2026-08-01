<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Exceptions;

use Exception;

/**
 * ModuleRegistryException
 *
 * Base exception for all module registry-related errors.
 *
 * Thrown when a module registry operation fails for any reason.
 * Provides a common base for catching and handling registry errors.
 *
 * @package WaysNX\BusinessFramework\Exceptions
 */
class ModuleRegistryException extends Exception
{
}
