<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * BaseWbfCommand
 *
 * Base class for all WBF CLI commands.
 *
 * Provides:
 * - Shared option handling (--json, --no-interaction)
 * - Exit code conventions
 * - JSON output formatting
 * - Error handling
 * - Result formatting
 *
 * Exit Codes:
 * - 0: Success
 * - 1: General error
 * - 2: Invalid argument/input
 * - 3: Resource not found
 * - 4: Permission/authorization
 * - 5: Conflict
 * - 6: System/framework failure
 *
 * @package WaysNX\BusinessFramework\Console
 */
abstract class BaseWbfCommand extends Command
{
    /**
     * Exit code constants
     */
    const EXIT_SUCCESS = 0;
    const EXIT_ERROR = 1;
    const EXIT_INVALID_ARGUMENT = 2;
    const EXIT_NOT_FOUND = 3;
    const EXIT_PERMISSION_DENIED = 4;
    const EXIT_CONFLICT = 5;
    const EXIT_SYSTEM_FAILURE = 6;

    /**
     * Whether to output as JSON
     *
     * @var bool
     */
    protected bool $jsonOutput = false;

    /**
     * Whether running in non-interactive mode
     *
     * @var bool
     */
    protected bool $nonInteractive = false;

    /**
     * Initialize command state from options
     *
     * Called before handle() to set up command flags.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->jsonOutput = $this->option('json') === true;
        $this->nonInteractive = $this->option('no-interaction') === true;
    }

    /**
     * Output a success result
     *
     * @param string|array $data Success data or message
     * @param array $meta Optional metadata
     * @return int Exit code (0)
     */
    protected function successOutput($data = null, array $meta = []): int
    {
        if ($this->jsonOutput) {
            return $this->outputJson('success', $data, $meta);
        }

        if (is_string($data)) {
            $this->info($data);
        } elseif (is_array($data)) {
            $this->outputTable($data);
        }

        return self::EXIT_SUCCESS;
    }

    /**
     * Output an error result
     *
     * @param string $message Error message
     * @param int $code Exit code
     * @param string $type Error type (e.g., 'validation_error', 'not_found')
     * @param array $details Additional error details
     * @return int Exit code
     */
    protected function errorOutput(string $message, int $code = self::EXIT_ERROR, string $type = 'error', array $details = []): int
    {
        if ($this->jsonOutput) {
            return $this->outputJsonError($message, $code, $type, $details);
        }

        $this->line("<error>Error:</error> $message", 'error');
        if (!empty($details)) {
            $this->line('<comment>Details:</comment>');
            foreach ($details as $key => $value) {
                $this->line("  $key: " . json_encode($value));
            }
        }

        return $code;
    }

    /**
     * Output a warning
     *
     * @param string $message Warning message
     * @return void
     */
    protected function warnOutput(string $message): void
    {
        if (!$this->jsonOutput) {
            $this->line("<comment>Warning:</comment> {$message}");
        }
    }

    /**
     * Output JSON response
     *
     * @param string $status Status (success, error)
     * @param mixed $data Response data
     * @param array $meta Metadata
     * @return int Exit code
     */
    protected function outputJson(string $status, $data = null, array $meta = []): int
    {
        $response = [
            'status' => $status,
            'command' => $this->getName(),
            'data' => $data,
            'meta' => $meta,
        ];

        $this->line(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return self::EXIT_SUCCESS;
    }

    /**
     * Output JSON error response
     *
     * @param string $message Error message
     * @param int $code Exit code
     * @param string $type Error type
     * @param array $details Error details
     * @return int Exit code
     */
    protected function outputJsonError(string $message, int $code = self::EXIT_ERROR, string $type = 'error', array $details = []): int
    {
        $response = [
            'status' => 'error',
            'code' => $code,
            'command' => $this->getName(),
            'message' => $message,
            'type' => $type,
            'details' => $details,
        ];

        $this->line(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $code;
    }

    /**
     * Output a table
     *
     * @param array $data Table data (array of rows)
     * @param array $headers Table headers
     * @return void
     */
    protected function outputTable(array $data, array $headers = []): void
    {
        if (empty($data)) {
            $this->info('No data to display.');
            return;
        }

        // If headers not provided, infer from first row
        if (empty($headers) && is_array($data[0])) {
            $headers = array_keys($data[0]);
        }

        $this->table($headers, $data);
    }

    /**
     * Resolve a service from the container
     *
     * @template T
     * @param class-string<T> $class
     * @return T
     * @throws \Exception If service cannot be resolved
     */
    protected function resolve(string $class)
    {
        try {
            return app($class);
        } catch (Throwable $e) {
            throw new \Exception("Failed to resolve {$class}: {$e->getMessage()}");
        }
    }

    /**
     * Check if running in non-interactive mode
     *
     * @return bool
     */
    protected function isNonInteractive(): bool
    {
        return $this->nonInteractive;
    }
}
