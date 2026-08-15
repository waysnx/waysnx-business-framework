<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Types;

/**
 * BusinessFunctionInterface
 *
 * Framework-independent contract for Business Functions in WBF.
 *
 * A Business Function represents a single, well-defined business operation.
 * Every Business Function SHALL conform to this specification (WBF-DOC-0005).
 *
 * Responsibilities:
 * - Define a stable Function Identity (ID, Name, Version, Module, Domain, Capability)
 * - Declare Business Metadata (Owner, Status, Classification, Tags, etc.)
 * - Publish Business Contracts (Request, Response, Errors, Events)
 * - Support versioning and evolution
 * - Remain technology-independent
 *
 * Design Pattern:
 * - This is a framework-independent contract
 * - Implementations in Laravel: WaysNX\BusinessFramework\Contracts\BusinessFunctionInterface
 * - Implementations in other frameworks should implement this interface
 *
 * Conformance:
 * - WBF-DOC-0005: Business Function Specification
 * - Section 8: Function Identity
 * - Section 9: Function Metadata
 * - Section 10-12: Business Contracts
 *
 * @package WaysNX\BusinessFramework\Types
 */
interface BusinessFunctionInterface extends EntityInterface
{
    // ========================================
    // FUNCTION IDENTITY
    // ========================================

    /**
     * Get the unique Function Identifier
     *
     * Recommended format: MODULE.DOMAIN.CAPABILITY.FUNCTION
     * Example: HR.LEAVE.APPLY.APPLY_LEAVE
     *
     * Requirement: BF-ID-01 - Every Business Function SHALL define a globally unique Function Identifier
     *
     * @return string The unique Function Identifier
     */
    public function getFunctionId(): string;

    /**
     * Get the human-readable Function Name
     *
     * Requirement: BF-ID-02 - Every Business Function SHALL define a human-readable Name
     *
     * @return string The human-readable function name
     */
    public function getFunctionName(): string;

    /**
     * Get the Function Version
     *
     * Requirement: BF-ID-03 - Every Business Function SHALL define a Version
     *
     * Semantic Versioning: MAJOR.MINOR.PATCH
     * - MAJOR: Breaking changes
     * - MINOR: Backward-compatible enhancements
     * - PATCH: Bug fixes
     *
     * @return string The semantic version string
     */
    public function getFunctionVersion(): string;

    /**
     * Get the Module
     *
     * Requirement: BF-ID-04 - Every Business Function SHALL define an owning Module
     *
     * @return string The module name (e.g., "HR")
     */
    public function getModule(): string;

    /**
     * Get the Domain
     *
     * Requirement: BF-ID-05 - Every Business Function SHALL define its owning Domain
     *
     * @return string The domain name (e.g., "LEAVE")
     */
    public function getDomain(): string;

    /**
     * Get the Business Capability
     *
     * Requirement: BF-ID-06 - Every Business Function SHALL define its owning Business Capability
     *
     * @return string The business capability (e.g., "APPLY")
     */
    public function getCapability(): string;

    /**
     * Get the Lifecycle Status
     *
     * Requirement: BF-ID-07 - Every Business Function SHALL define its Lifecycle Status
     *
     * Recommended states: Draft, Defined, Approved, Implemented, Released, Deprecated, Retired
     *
     * @return string The lifecycle status
     */
    public function getLifecycleStatus(): string;

    // ========================================
    // FUNCTION METADATA
    // ========================================

    /**
     * Get the Function Description
     *
     * Requirement: BF-META-01 - Every Business Function SHALL include a Description
     *
     * @return string The function description
     */
    public function getDescription(): string;

    /**
     * Get the Business Owner
     *
     * Requirement: BF-META-02 - Every Business Function SHALL declare its Business Owner
     *
     * @return string The business owner (typically name or identifier)
     */
    public function getBusinessOwner(): string;

    /**
     * Get the Technical Owner
     *
     * Requirement: BF-META-03 - Every Business Function SHALL declare its Technical Owner
     *
     * @return string The technical owner (typically name or identifier)
     */
    public function getTechnicalOwner(): string;

    /**
     * Get Tags
     *
     * Requirement: BF-META-04 - Every Business Function SHALL define zero or more Tags
     *
     * @return string[] Array of tags for discoverability and classification
     */
    public function getTags(): array;

    /**
     * Get the Classification
     *
     * Requirement: BF-META-05 - Every Business Function SHALL define its Classification
     *
     * Examples: Core, Supporting, Administrative, Reporting, Integration
     *
     * @return string The classification
     */
    public function getClassification(): string;

    /**
     * Get the Visibility
     *
     * Requirement: BF-META-06 - Every Business Function SHALL define its Visibility
     *
     * Recommended values: Public, Internal, Private
     *
     * @return string The visibility level
     */
    public function getVisibility(): string;

    /**
     * Get Function Dependencies
     *
     * Requirement: BF-META-07 - Every Business Function SHALL declare its Dependencies
     *
     * @return string[] Array of Function IDs that this function depends on
     */
    public function getDependencies(): array;

    /**
     * Get Events Published
     *
     * Requirement: BF-META-08 - Every Business Function SHALL declare Events Published
     *
     * @return string[] Array of event names/types published by this function
     */
    public function getEventsPublished(): array;

    /**
     * Get Events Consumed
     *
     * Requirement: BF-META-09 - Every Business Function SHALL declare Events Consumed
     *
     * @return string[] Array of event names/types consumed by this function
     */
    public function getEventsConsumed(): array;

    /**
     * Get Keywords
     *
     * Requirement: BF-META-10 - Every Business Function SHOULD define Keywords for discoverability
     *
     * @return string[] Array of keywords for improved discoverability
     */
    public function getKeywords(): array;

    // ========================================
    // BUSINESS CONTRACT
    // ========================================

    /**
     * Get the Request Contract
     *
     * Requirement: BF-REQ-01 - Every Business Function SHALL define exactly one Request Contract
     * Requirement: BF-CON-01 - Every Business Function SHALL publish its complete business contract
     *
     * The Request Contract defines all information required to execute the Business Function.
     *
     * @return array The request contract definition with structure:
     *         [
     *             'required' => ['field1', 'field2', ...],  // BF-REQ-02: Required business inputs
     *             'optional' => ['field3', 'field4', ...],  // BF-REQ-03: Optional inputs
     *             'fields' => [                              // Field definitions
     *                 'field1' => ['type' => 'string', 'description' => '...'],
     *                 ...
     *             ],
     *             'validation' => [...]                     // BF-REQ-04: Validation requirements
     *         ]
     */
    public function getRequestContract(): array;

    /**
     * Get the Response Contract
     *
     * Requirement: BF-RESP-01 - Every Business Function SHALL define exactly one Response Contract
     * Requirement: BF-RESP-02 - Responses SHALL represent the business outcome
     *
     * @return array The response contract definition with structure:
     *         [
     *             'fields' => [                              // Output fields
     *                 'field1' => ['type' => 'string', 'description' => '...'],
     *                 ...
     *             ],
     *             'success_fields' => ['field1', 'field2'],  // Fields present on success
     *             'description' => '...'                     // Response description
     *         ]
     */
    public function getResponseContract(): array;

    /**
     * Get Error Categories
     *
     * Requirement: BF-ERR-01 - Every unsuccessful execution SHALL produce a defined business error
     *
     * Standard Error Categories:
     * - ValidationError
     * - AuthorizationError
     * - BusinessRuleViolation
     * - ResourceNotFound
     * - Conflict
     * - ConcurrencyError
     * - ExternalDependencyFailure
     * - InternalSystemError
     *
     * @return array Error categories supported by this function
     */
    public function getErrorCategories(): array;

    // ========================================
    // PROCESSING DEFINITION
    // ========================================

    /**
     * Get Validation Requirements
     *
     * Defines validation rules for Request Contract compliance.
     *
     * Requirement: BF-REQ-04 - The Request Contract SHALL define validation requirements
     * Requirement: BF-VAL-01 - Validation SHALL execute before Authorization
     *
     * @return array Validation definition (format implementation-specific, but must be representable)
     */
    public function getValidationRules(): array;

    /**
     * Get Authorization Requirements
     *
     * Defines authorization policies for this function.
     *
     * Requirement: BF-SEC-01 - Authorization SHALL occur after successful validation
     * Requirement: BF-SEC-05 - Authorization policies SHALL remain external to business logic
     *
     * @return array Authorization requirements/policies
     */
    public function getAuthorizationRequirements(): array;

    /**
     * Get Business Rules
     *
     * Defines business policy that governs execution.
     *
     * Requirement: BF-BR-01 - Business Rules SHALL execute after Validation and Authorization
     * Requirement: BF-BR-02 - Business Rules MAY prevent execution
     *
     * @return array Business rules definition
     */
    public function getBusinessRules(): array;

    // ========================================
    // OPERATIONAL DEFINITION
    // ========================================

    /**
     * Get Observability Requirements
     *
     * Requirement: BF-OBS-01 - Every Business Function SHALL expose execution metadata
     * Requirement: BF-OBS-02 - Execution SHALL support correlation between Requests and Responses
     *
     * @return array Observability configuration/requirements
     */
    public function getObservabilityRequirements(): array;

    /**
     * Get Audit Requirements
     *
     * Requirement: BF-OBS-04 - Business Function execution SHALL support auditing
     *
     * @return array Audit configuration/requirements
     */
    public function getAuditRequirements(): array;

    // ========================================
    // COMPLETE CONTRACT DEFINITION
    // ========================================

    /**
     * Get Complete Business Function Contract
     *
     * Returns the canonical Business Function Definition (BFD) combining all components.
     *
     * Requirement: BF-CON-01 - Every Business Function SHALL publish its complete business contract
     * Requirement: Section 25 - Business Function Definition (BFD)
     *
     * This method returns the complete definition that enables:
     * - Documentation generation
     * - Code generation
     * - API generation
     * - Test generation
     * - Architecture validation
     * - Conformance verification
     *
     * @return array Complete Business Function Definition containing:
     *         [
     *             'identity' => [...],
     *             'metadata' => [...],
     *             'contract' => [...],
     *             'processing' => [...],
     *             'operational' => [...]
     *         ]
     */
    public function getCompleteContractDefinition(): array;
}
