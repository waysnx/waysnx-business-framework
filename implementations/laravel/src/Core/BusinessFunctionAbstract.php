<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core;

use WaysNX\BusinessFramework\Types\BusinessFunctionInterface;

/**
 * BusinessFunctionAbstract
 *
 * Framework-independent base implementation of Business Functions.
 *
 * This abstract class provides the canonical Business Function model as defined in WBF-DOC-0005.
 * It extends BaseModelAbstract to inherit identity, versioning, audit tracking, and metadata support.
 *
 * Subclasses (concrete Business Functions) SHALL:
 * - Implement the abstract methods
 * - Call initialize() during construction
 * - Define their business logic in the protected methods and hooks
 *
 * Architecture:
 * - This is framework-independent (no PHP framework dependencies)
 * - Uses abstract methods for areas where frameworks differ
 * - Provides template method hooks for customization
 *
 * Design Principles:
 * - BF-01: Every Business Function has exactly one business purpose
 * - BF-02: Business Rules execute only within a Business Function
 * - BF-03: Business Functions communicate using documented contracts
 * - BF-04: Business Functions do NOT expose internal implementation details
 * - BF-05: Business Functions remain independently executable
 * - BF-06: Business Functions are versioned
 * - BF-07: Business Functions are discoverable through metadata
 * - BF-08: Business Functions remain implementation-independent
 *
 * Conformance: WBF-DOC-0005 Section 24 - Conformance
 *
 * @implements BusinessFunctionInterface
 * @extends BaseModelAbstract
 * @package WaysNX\BusinessFramework\Core
 */
abstract class BusinessFunctionAbstract extends BaseModelAbstract implements BusinessFunctionInterface
{
    // ========================================
    // FUNCTION IDENTITY
    // ========================================

    /**
     * Function Identifier
     *
     * Recommended format: MODULE.DOMAIN.CAPABILITY.FUNCTION
     * Example: HR.LEAVE.APPLY.APPLY_LEAVE
     *
     * @var string
     */
    protected string $functionId;

    /**
     * Function Name
     *
     * Human-readable name for the function.
     *
     * @var string
     */
    protected string $functionName;

    /**
     * Function Version
     *
     * Semantic versioning: MAJOR.MINOR.PATCH
     *
     * @var string
     */
    protected string $functionVersion;

    /**
     * Module
     *
     * Part of function identity hierarchy.
     *
     * @var string
     */
    protected string $module;

    /**
     * Domain
     *
     * Part of function identity hierarchy.
     *
     * @var string
     */
    protected string $domain;

    /**
     * Capability
     *
     * Part of function identity hierarchy.
     *
     * @var string
     */
    protected string $capability;

    /**
     * Lifecycle Status
     *
     * Recommended: Draft, Defined, Approved, Implemented, Released, Deprecated, Retired
     *
     * @var string
     */
    protected string $lifecycleStatus;

    // ========================================
    // FUNCTION METADATA
    // ========================================

    /**
     * Description
     *
     * What business operation this function performs.
     *
     * @var string
     */
    protected string $description;

    /**
     * Business Owner
     *
     * Who owns this function from business perspective.
     *
     * @var string
     */
    protected string $businessOwner;

    /**
     * Technical Owner
     *
     * Who owns this function from technical perspective.
     *
     * @var string
     */
    protected string $technicalOwner;

    /**
     * Tags
     *
     * For classification and discoverability.
     *
     * @var string[]
     */
    protected array $tags = [];

    /**
     * Classification
     *
     * Examples: Core, Supporting, Administrative, Reporting, Integration
     *
     * @var string
     */
    protected string $classification;

    /**
     * Visibility
     *
     * Examples: Public, Internal, Private
     *
     * @var string
     */
    protected string $visibility;

    /**
     * Dependencies
     *
     * Function IDs that this function depends on.
     *
     * @var string[]
     */
    protected array $dependencies = [];

    /**
     * Events Published
     *
     * Event types published by this function.
     *
     * @var string[]
     */
    protected array $eventsPublished = [];

    /**
     * Events Consumed
     *
     * Event types consumed by this function.
     *
     * @var string[]
     */
    protected array $eventsConsumed = [];

    /**
     * Keywords
     *
     * For discoverability and search.
     *
     * @var string[]
     */
    protected array $keywords = [];

    // ========================================
    // BUSINESS CONTRACT
    // ========================================

    /**
     * Request Contract
     *
     * Defines what inputs are required.
     *
     * Structure:
     * [
     *     'required' => ['field1', 'field2'],
     *     'optional' => ['field3'],
     *     'fields' => [
     *         'field1' => ['type' => 'string', 'description' => '...'],
     *     ]
     * ]
     *
     * @var array
     */
    protected array $requestContract = [];

    /**
     * Response Contract
     *
     * Defines what outputs are produced.
     *
     * Structure:
     * [
     *     'fields' => [
     *         'field1' => ['type' => 'string', 'description' => '...'],
     *     ],
     *     'success_fields' => ['field1']
     * ]
     *
     * @var array
     */
    protected array $responseContract = [];

    /**
     * Error Categories
     *
     * Categories of errors this function can produce.
     *
     * @var string[]
     */
    protected array $errorCategories = [];

    // ========================================
    // PROCESSING DEFINITION
    // ========================================

    /**
     * Validation Rules
     *
     * Defines validation for Request Contract.
     *
     * @var array
     */
    protected array $validationRules = [];

    /**
     * Authorization Requirements
     *
     * Defines authorization policies.
     *
     * @var array
     */
    protected array $authorizationRequirements = [];

    /**
     * Business Rules
     *
     * Defines business policy governing execution.
     *
     * @var array
     */
    protected array $businessRules = [];

    // ========================================
    // OPERATIONAL DEFINITION
    // ========================================

    /**
     * Observability Requirements
     *
     * @var array
     */
    protected array $observabilityRequirements = [];

    /**
     * Audit Requirements
     *
     * @var array
     */
    protected array $auditRequirements = [];

    // ========================================
    // INITIALIZATION
    // ========================================

    /**
     * Initialize Business Function Definition
     *
     * Subclasses MUST call this method during construction after setting all properties.
     * This ensures the function is properly registered and validates the definition.
     *
     * This method:
     * - Validates that all mandatory fields are set
     * - Initializes audit timestamps
     * - Triggers lifecycle hooks
     *
     * @throws \InvalidArgumentException if required fields are missing
     * @return void
     */
    protected function initializeBusinessFunction(): void
    {
        // Validate mandatory identity fields
        $this->validateMandatoryFields();

        // Initialize base model properties
        if (!isset($this->entityId)) {
            $this->entityId = $this->generateUuid();
        }

        if (!isset($this->entityType)) {
            $this->entityType = static::class;
        }

        if (!isset($this->entityVersion)) {
            $this->entityVersion = 1;
        }

        // Initialize audit timestamps
        $this->initializeAuditTimestamps();

        // Trigger lifecycle hook
        $this->beforeCreate();
    }

    /**
     * Validate that all mandatory fields are set
     *
     * @throws \InvalidArgumentException if required fields are missing
     * @return void
     */
    protected function validateMandatoryFields(): void
    {
        $required = [
            'functionId',
            'functionName',
            'functionVersion',
            'module',
            'domain',
            'capability',
            'lifecycleStatus',
            'description',
            'businessOwner',
            'technicalOwner',
            'classification',
            'visibility',
        ];

        foreach ($required as $field) {
            if (!isset($this->{$field}) || (is_string($this->{$field}) && trim($this->{$field}) === '')) {
                throw new \InvalidArgumentException(
                    "Business Function definition is incomplete: required field '{$field}' is missing"
                );
            }
        }

        // Validate contract fields
        if (empty($this->requestContract)) {
            throw new \InvalidArgumentException(
                'Business Function definition is incomplete: requestContract is empty'
            );
        }

        if (empty($this->responseContract)) {
            throw new \InvalidArgumentException(
                'Business Function definition is incomplete: responseContract is empty'
            );
        }
    }

    // ========================================
    // FUNCTION IDENTITY - INTERFACE IMPLEMENTATION
    // ========================================

    public function getFunctionId(): string
    {
        return $this->functionId;
    }

    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    public function getFunctionVersion(): string
    {
        return $this->functionVersion;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getCapability(): string
    {
        return $this->capability;
    }

    public function getLifecycleStatus(): string
    {
        return $this->lifecycleStatus;
    }

    // ========================================
    // FUNCTION METADATA - INTERFACE IMPLEMENTATION
    // ========================================

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getBusinessOwner(): string
    {
        return $this->businessOwner;
    }

    public function getTechnicalOwner(): string
    {
        return $this->technicalOwner;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getClassification(): string
    {
        return $this->classification;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getEventsPublished(): array
    {
        return $this->eventsPublished;
    }

    public function getEventsConsumed(): array
    {
        return $this->eventsConsumed;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    // ========================================
    // BUSINESS CONTRACT - INTERFACE IMPLEMENTATION
    // ========================================

    public function getRequestContract(): array
    {
        return $this->requestContract;
    }

    public function getResponseContract(): array
    {
        return $this->responseContract;
    }

    public function getErrorCategories(): array
    {
        return $this->errorCategories;
    }

    // ========================================
    // PROCESSING DEFINITION - INTERFACE IMPLEMENTATION
    // ========================================

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getAuthorizationRequirements(): array
    {
        return $this->authorizationRequirements;
    }

    public function getBusinessRules(): array
    {
        return $this->businessRules;
    }

    // ========================================
    // OPERATIONAL DEFINITION - INTERFACE IMPLEMENTATION
    // ========================================

    public function getObservabilityRequirements(): array
    {
        return $this->observabilityRequirements;
    }

    public function getAuditRequirements(): array
    {
        return $this->auditRequirements;
    }

    // ========================================
    // COMPLETE CONTRACT DEFINITION
    // ========================================

    public function getCompleteContractDefinition(): array
    {
        return [
            'identity' => [
                'functionId' => $this->getFunctionId(),
                'functionName' => $this->getFunctionName(),
                'functionVersion' => $this->getFunctionVersion(),
                'module' => $this->getModule(),
                'domain' => $this->getDomain(),
                'capability' => $this->getCapability(),
                'lifecycleStatus' => $this->getLifecycleStatus(),
            ],
            'metadata' => [
                'description' => $this->getDescription(),
                'businessOwner' => $this->getBusinessOwner(),
                'technicalOwner' => $this->getTechnicalOwner(),
                'tags' => $this->getTags(),
                'classification' => $this->getClassification(),
                'visibility' => $this->getVisibility(),
                'dependencies' => $this->getDependencies(),
                'eventsPublished' => $this->getEventsPublished(),
                'eventsConsumed' => $this->getEventsConsumed(),
                'keywords' => $this->getKeywords(),
            ],
            'contract' => [
                'request' => $this->getRequestContract(),
                'response' => $this->getResponseContract(),
                'errors' => $this->getErrorCategories(),
            ],
            'processing' => [
                'validation' => $this->getValidationRules(),
                'authorization' => $this->getAuthorizationRequirements(),
                'businessRules' => $this->getBusinessRules(),
            ],
            'operational' => [
                'observability' => $this->getObservabilityRequirements(),
                'audit' => $this->getAuditRequirements(),
            ],
        ];
    }

    // ========================================
    // PUBLIC EXECUTION CONTRACT - FOR RUNTIMES
    // ========================================

    /**
     * Execute this BusinessFunction through the canonical pipeline.
     *
     * This is the public execution contract that runtimes use to invoke business functions.
     * It orchestrates the template methods in the order specified by WBF-DOC-0005:
     * 1. validateRequest() - BF-VAL-01
     * 2. checkAuthorization() - BF-SEC-01
     * 3. evaluateBusinessRules() - BF-BR-01
     * 4. executeBusiness() - BF-EXE-01
     * 5. publishEvents() - BF-EVT-01
     * 6. transformToResponse()
     *
     * Requirement: BF-EXE-01 - Business Execution SHALL follow canonical pipeline
     *
     * @param array $request The incoming request
     * @param mixed $caller Optional caller/user context (framework-specific)
     * @return array The response matching the Response Contract
     *
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If authorization fails or business rules fail
     */
    public function execute(array $request, mixed $caller = null): array
    {
        // Phase 1: Validation
        $this->validateRequest($request);

        // Phase 2: Authorization
        $this->checkAuthorization($request, $caller);

        // Phase 3: Business Rules
        $this->evaluateBusinessRules($request);

        // Phase 4: Execution
        $result = $this->executeBusiness($request);

        // Phase 5: Event Publication
        $this->publishEvents($request, $result);

        // Phase 6: Response Transformation
        return $this->transformToResponse($result);
    }

    // ========================================
    // TEMPLATE METHODS - FOR SUBCLASS CUSTOMIZATION
    // ========================================

    /**
     * Validate the Request Contract compliance
     *
     * Override this method to provide request validation logic.
     * This will be called before authorization and business rule evaluation.
     *
     * Requirement: BF-VAL-01 - Validation SHALL execute before Authorization
     *
     * @param array $request The incoming request data
     * @throws \InvalidArgumentException if validation fails
     * @return void
     */
    protected function validateRequest(array $request): void
    {
        // Subclasses override to implement validation
    }

    /**
     * Check authorization
     *
     * Override this method to provide authorization logic.
     * This will be called after validation and before business rule evaluation.
     *
     * Requirement: BF-SEC-01 - Authorization SHALL occur after successful validation
     *
     * @param array $request The request data
     * @param mixed $caller The caller/user context (framework-specific)
     * @throws \RuntimeException if authorization fails
     * @return void
     */
    protected function checkAuthorization(array $request, mixed $caller = null): void
    {
        // Subclasses override to implement authorization
    }

    /**
     * Evaluate Business Rules
     *
     * Override this method to implement business rule checks.
     * This will be called after authorization and before execution.
     *
     * Requirement: BF-BR-01 - Business Rules SHALL execute after Validation and Authorization
     * Requirement: BF-BR-02 - Business Rules MAY prevent execution
     *
     * @param array $request The request data
     * @throws \RuntimeException if business rules fail
     * @return void
     */
    protected function evaluateBusinessRules(array $request): void
    {
        // Subclasses override to implement business rules
    }

    /**
     * Execute Business Logic
     *
     * This is the core business logic of the function.
     * Subclasses MUST override this method.
     *
     * Requirement: BF-EXE-02 - Execution SHALL perform exactly one business operation
     * Requirement: BF-EXE-03 - Execution SHALL produce exactly one Response
     *
     * @param array $request The validated request
     * @return array The business result (will be transformed into Response Contract)
     */
    abstract protected function executeBusiness(array $request): array;

    /**
     * Publish Events
     *
     * Override this method to publish domain events after successful execution.
     *
     * Requirement: BF-EVT-01 - Events SHALL be published only after successful Business Execution
     *
     * @param array $request The original request
     * @param array $result The execution result
     * @return void
     */
    protected function publishEvents(array $request, array $result): void
    {
        // Subclasses override to implement event publishing
    }

    /**
     * Transform execution result into response
     *
     * Ensures the result matches the Response Contract.
     *
     * Requirement: BF-RESP-02 - Responses SHALL represent the business outcome
     *
     * @param array $result The raw execution result
     * @return array The response matching Response Contract
     */
    protected function transformToResponse(array $result): array
    {
        return $result;
    }
}
