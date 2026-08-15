<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Runtime;

use WaysNX\BusinessFramework\Types\BusinessFunctionInterface;

/**
 * BusinessFunctionRuntime
 *
 * Minimal observability wrapper around the business function execution.
 *
 * Key Finding: BusinessFunctionAbstract now provides the public execute() method
 * that orchestrates the canonical pipeline. The Runtime is framework-specific code
 * that wraps execution for observability, metrics, and event tracking.
 *
 * This demonstrates that a Runtime doesn't need to be a complex Kernel.
 * It's simply observability and metrics collection around the existing
 * BusinessFunction::execute() method.
 *
 * Pipeline is implemented in BusinessFunctionAbstract::execute():
 * 1. validateRequest() - BF-VAL-01
 * 2. checkAuthorization() - BF-SEC-01
 * 3. evaluateBusinessRules() - BF-BR-01
 * 4. executeBusiness() - BF-EXE-01
 * 5. publishEvents() - BF-EVT-01
 * 6. transformToResponse()
 *
 * @package WaysNX\BusinessFramework\Runtime
 */
class BusinessFunctionRuntime
{
    private array $context = [];
    private array $events = [];
    private ?\Throwable $executionError = null;

    /**
     * Execute a BusinessFunction with observability
     *
     * @param BusinessFunctionInterface $businessFunction The BF to execute
     * @param array $request The incoming request
     * @param mixed $caller Optional caller context
     * @return array The response
     *
     * @throws \InvalidArgumentException Validation failure
     * @throws \RuntimeException Authorization or business rule failure
     */
    public function execute(
        BusinessFunctionInterface $businessFunction,
        array $request,
        mixed $caller = null
    ): array {
        $this->context = [];
        $this->events = [];
        $this->executionError = null;

        $this->context['functionId'] = $businessFunction->getFunctionId();
        $this->context['startTime'] = microtime(true);

        try {
            // BusinessFunctionAbstract::execute() orchestrates the pipeline
            $response = $businessFunction->execute($request, $caller);

            $this->context['endTime'] = microtime(true);
            $this->context['executionTime'] = $this->context['endTime'] - $this->context['startTime'];
            $this->context['status'] = 'success';

            // Collect published events
            foreach ($businessFunction->getEventsPublished() as $eventType) {
                $this->events[] = ['type' => $eventType, 'functionId' => $businessFunction->getFunctionId()];
            }

            return $response;
        } catch (\Throwable $e) {
            $this->executionError = $e;
            $this->context['endTime'] = microtime(true);
            $this->context['executionTime'] = $this->context['endTime'] - $this->context['startTime'];
            $this->context['status'] = 'failed';
            $this->context['error'] = $e->getMessage();
            $this->context['errorClass'] = $e::class;
            throw $e;
        }
    }

    public function getContext(): array { return $this->context; }
    public function getEvents(): array { return $this->events; }
    public function getExecutionError(): ?\Throwable { return $this->executionError; }
    public function wasSuccessful(): bool { return $this->executionError === null; }
}
