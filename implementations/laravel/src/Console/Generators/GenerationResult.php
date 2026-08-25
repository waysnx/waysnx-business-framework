<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Generators;

/**
 * GenerationResult
 *
 * Result of an artifact generation operation.
 *
 * @package WaysNX\BusinessFramework\Console\Generators
 */
class GenerationResult
{
    /**
     * Whether generation was successful
     *
     * @var bool
     */
    private bool $success;

    /**
     * Generated file paths
     *
     * @var array<string>
     */
    private array $files;

    /**
     * Error message if generation failed
     *
     * @var string|null
     */
    private ?string $errorMessage;

    /**
     * Error type if generation failed
     *
     * @var string|null
     */
    private ?string $errorType;

    /**
     * Additional details
     *
     * @var array
     */
    private array $details;

    /**
     * Private constructor - use static factory methods
     */
    private function __construct()
    {
        $this->success = false;
        $this->files = [];
        $this->errorMessage = null;
        $this->errorType = null;
        $this->details = [];
    }

    /**
     * Create a successful result
     *
     * @param array<string> $files
     * @param array $details
     * @return self
     */
    public static function success(array $files, array $details = []): self
    {
        $result = new self();
        $result->success = true;
        $result->files = $files;
        $result->details = $details;
        return $result;
    }

    /**
     * Create a failed result
     *
     * @param string $message
     * @param string $type
     * @param array $details
     * @return self
     */
    public static function failure(string $message, string $type = 'error', array $details = []): self
    {
        $result = new self();
        $result->success = false;
        $result->errorMessage = $message;
        $result->errorType = $type;
        $result->details = $details;
        return $result;
    }

    /**
     * Whether generation was successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get generated files
     *
     * @return array<string>
     */
    public function getFiles(): array
    {
        return $this->files ?? [];
    }

    /**
     * Get error message
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Get error type
     *
     * @return string|null
     */
    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Convert to array for output
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'files' => $this->getFiles(),
            'message' => $this->errorMessage,
            'type' => $this->errorType,
            'details' => $this->details,
        ];
    }
}
