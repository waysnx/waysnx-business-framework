<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Models;

use WaysNX\BusinessFramework\Core\WorkflowAbstract;
use JsonSerializable;

/**
 * Workflow
 *
 * Laravel-specific implementation of WBF Workflow business model.
 *
 * This class provides:
 * - Framework-independent workflow model (from WorkflowAbstract)
 * - Laravel-specific integration
 * - JSON serialization support for Laravel
 * - Concrete implementation of abstract methods
 *
 * IMPORTANT: Coexistence with WorkflowDefinition
 * - This Workflow model handles business governance (ownership, SLA, KPIs, lifecycle)
 * - WorkflowDefinition in Registry handles technical execution (immutable)
 * - Workflow references WorkflowDefinition via workflowDefinitionId
 * - Application integrates both for complete workflow management
 *
 * Architecture:
 * - Extends WorkflowAbstract (framework-independent)
 * - Implements JsonSerializable for Laravel
 * - Uses Laravel-compatible UUID generation
 * - DateTimeImmutable for timestamps
 * - Remains compatible with the WBF specification (WBF-DOC-0009)
 *
 * Usage:
 * ```php
 * $workflow = new Workflow();
 * $workflow->setWorkflowId('emp-onboarding');
 * $workflow->setWorkflowName('Employee Onboarding');
 * $workflow->setDescription('Complete employee onboarding process');
 * $workflow->setCapabilityId('employee-management');
 * $workflow->setBusinessOwner('hr-manager-001');
 * $workflow->setStatus(Workflow::ACTIVE);
 * $workflow->setWorkflowDefinitionId('onboarding-v1');
 * $workflow->setTrigger('new-employee-event');
 * $workflow->setSla('5 business days');
 * $workflow->addKpi('completion-rate');
 * $workflow->addKpi('time-to-completion');
 * $workflow->addService('email-service');
 * $workflow->addService('document-service');
 * $workflow->addStep('step-1-create-account');
 * $workflow->addStep('step-2-assign-equipment');
 * $workflow->addDependency('pre-hire-verification');
 *
 * echo $workflow->toJson();
 * ```
 *
 * @extends WorkflowAbstract
 * @implements JsonSerializable
 * @package WaysNX\BusinessFramework\Models
 */
class Workflow extends WorkflowAbstract implements JsonSerializable
{
    /**
     * Initialize the workflow after construction
     *
     * Called by subclass constructors to set up the workflow instance.
     * Initializes default values and audit timestamps.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->initializeWorkflow();
        $this->initializeAuditTimestamps();
    }

    /**
     * Generate a unique UUID for the entity
     *
     * Uses a Laravel-compatible UUID generation method.
     * In production, use Ramsey\Uuid or Laravel\Framework UUID helpers.
     *
     * @return string
     */
    protected function generateUuid(): string
    {
        // Placeholder for testing - in real Laravel app, use proper UUID generation
        return 'workflow-' . uniqid();
    }

    /**
     * Initialize audit timestamps
     *
     * Sets created, updated, and deleted timestamps to DateTimeImmutable.
     * Called during workflow initialization.
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
     * Called when the workflow is updated.
     * Sets the updatedAt timestamp to current time.
     *
     * @return void
     */
    protected function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now');
    }

    /**
     * Delete timestamp
     *
     * Sets the deletion timestamp for soft delete.
     *
     * @return void
     */
    protected function deleteTimestamp(): void
    {
        $this->deletedAt = new \DateTimeImmutable('now');
    }

    /**
     * Convert the Workflow to an array
     *
     * Returns all workflow properties plus BaseModel properties in array format.
     * Uses snake_case for Laravel conventions.
     * Suitable for JSON encoding and framework serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            // Workflow-specific fields (snake_case for Laravel)
            'workflow_id' => $this->getWorkflowId(),
            'workflow_name' => $this->getWorkflowName(),
            'description' => $this->getDescription(),
            'capability_id' => $this->getCapabilityId(),
            'business_owner' => $this->getBusinessOwner(),
            'status' => $this->getStatus(),
            'workflow_definition_id' => $this->getWorkflowDefinitionId(),
            'trigger' => $this->getTrigger(),
            'inputs' => $this->getInputs(),
            'outputs' => $this->getOutputs(),
            'services' => $this->getServices(),
            'steps' => $this->getSteps(),
            'kpis' => $this->getKpis(),
            'dependencies' => $this->getDependencies(),
            'sla' => $this->getSla(),

            // BaseModel fields (inherited)
            'entity_id' => $this->getEntityId(),
            'entity_type' => $this->getEntityType(),
            'entity_version' => $this->getEntityVersion(),
            'created_by' => $this->getCreatedBy(),
            'updated_by' => $this->getUpdatedBy(),
            'deleted_by' => $this->getDeletedBy(),
            'created_at' => $this->getCreatedAt() ? $this->getCreatedAt()->format('c') : null,
            'updated_at' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('c') : null,
            'deleted_at' => $this->getDeletedAt() ? $this->getDeletedAt()->format('c') : null,
            'metadata' => $this->getMetadata(),
        ];
    }

    /**
     * Convert the Workflow to JSON
     *
     * Returns a JSON-encoded representation of the workflow.
     * Uses JSON_UNESCAPED_UNICODE for better readability.
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
     * Convert the Workflow to a JSON-serializable array
     *
     * Enables direct JSON serialization in Laravel contexts.
     * Implements JsonSerializable interface.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Get a string representation of the Workflow
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'Workflow(%s | %s v%d)',
            $this->getWorkflowId(),
            $this->getWorkflowName(),
            $this->getEntityVersion()
        );
    }
}
