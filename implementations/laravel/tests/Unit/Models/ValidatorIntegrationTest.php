<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Models\BusinessFunction;

/**
 * Validator Integration Tests for Canonical BFD
 *
 * This test suite verifies that the canonical BFD representation
 * can be validated for conformance to WBF-DOC-0005 requirements.
 *
 * Tests cover:
 * - Valid BFD accepts all validation
 * - Invalid BFDs are rejected with specific error categories
 * - Validator integrates cleanly with existing framework
 *
 * Requirement: BF-CON-01 through BF-CON-05 (Contract requirements)
 * WBF-DOC-0005 Section 24: Conformance
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Models
 */
class ValidatorIntegrationTest extends TestCase
{
    /**
     * Canonical BFD validator
     *
     * This is a simple, non-invasive validator that checks BFD structure
     * without modifying the BusinessFunction or creating new dependencies.
     */
    private function validateBFD(array $bfd): array
    {
        $errors = [];

        // Validate identity section
        if (!isset($bfd['identity']) || !is_array($bfd['identity'])) {
            $errors[] = 'missing_identity_section';
        } else {
            $identity = $bfd['identity'];
            if (empty($identity['functionId'])) {
                $errors[] = 'missing_function_id';
            }
            if (empty($identity['functionName'])) {
                $errors[] = 'missing_function_name';
            }
            if (empty($identity['functionVersion'])) {
                $errors[] = 'missing_function_version';
            }
            if (empty($identity['module'])) {
                $errors[] = 'missing_module';
            }
            if (empty($identity['domain'])) {
                $errors[] = 'missing_domain';
            }
            if (empty($identity['capability'])) {
                $errors[] = 'missing_capability';
            }
            if (empty($identity['lifecycleStatus'])) {
                $errors[] = 'missing_lifecycle_status';
            }
        }

        // Validate metadata section
        if (!isset($bfd['metadata']) || !is_array($bfd['metadata'])) {
            $errors[] = 'missing_metadata_section';
        } else {
            $metadata = $bfd['metadata'];
            if (empty($metadata['description'])) {
                $errors[] = 'missing_description';
            }
            if (empty($metadata['businessOwner'])) {
                $errors[] = 'missing_business_owner';
            }
            if (empty($metadata['technicalOwner'])) {
                $errors[] = 'missing_technical_owner';
            }
            if (!isset($metadata['tags'])) {
                $errors[] = 'missing_tags';
            }
            if (!isset($metadata['classification'])) {
                $errors[] = 'missing_classification';
            }
            if (!isset($metadata['visibility'])) {
                $errors[] = 'missing_visibility';
            }
        }

        // Validate contract section
        if (!isset($bfd['contract']) || !is_array($bfd['contract'])) {
            $errors[] = 'missing_contract_section';
        } else {
            $contract = $bfd['contract'];
            if (!isset($contract['request']) || !is_array($contract['request'])) {
                $errors[] = 'missing_request_contract';
            }
            if (!isset($contract['response']) || !is_array($contract['response'])) {
                $errors[] = 'missing_response_contract';
            }
            if (!isset($contract['errors']) || !is_array($contract['errors'])) {
                $errors[] = 'missing_error_categories';
            }
        }

        // Validate processing section
        if (!isset($bfd['processing']) || !is_array($bfd['processing'])) {
            $errors[] = 'missing_processing_section';
        } else {
            $processing = $bfd['processing'];
            if (!isset($processing['validation'])) {
                $errors[] = 'missing_validation_rules';
            }
            if (!isset($processing['authorization'])) {
                $errors[] = 'missing_authorization_requirements';
            }
            if (!isset($processing['businessRules'])) {
                $errors[] = 'missing_business_rules';
            }
        }

        // Validate operational section
        if (!isset($bfd['operational']) || !is_array($bfd['operational'])) {
            $errors[] = 'missing_operational_section';
        } else {
            $operational = $bfd['operational'];
            if (!isset($operational['observability'])) {
                $errors[] = 'missing_observability_requirements';
            }
            if (!isset($operational['audit'])) {
                $errors[] = 'missing_audit_requirements';
            }
        }

        return $errors;
    }

    /**
     * Test 1: Valid BFD passes validation
     */
    public function test_valid_bfd_passes_validation(): void
    {
        $bf = new class extends BusinessFunction {
            public function __construct()
            {
                $this->functionId = 'TEST.MODULE.DOMAIN.FUNCTION';
                $this->functionName = 'Test Function';
                $this->functionVersion = '1.0.0';
                $this->module = 'TEST';
                $this->domain = 'MODULE';
                $this->capability = 'DOMAIN';
                $this->lifecycleStatus = 'Draft';

                $this->description = 'A test function';
                $this->businessOwner = 'Owner';
                $this->technicalOwner = 'Tech Owner';
                $this->tags = ['test'];
                $this->classification = 'Core';
                $this->visibility = 'Public';
                $this->dependencies = [];
                $this->eventsPublished = [];
                $this->eventsConsumed = [];
                $this->keywords = [];

                $this->requestContract = [
                    'required' => ['field1'],
                    'fields' => ['field1' => ['type' => 'string']],
                ];
                $this->responseContract = ['description' => 'Response', 'fields' => []];
                $this->errorCategories = ['ValidationError'];
                $this->validationRules = ['field1' => 'required'];
                $this->authorizationRequirements = [];
                $this->businessRules = [];
                $this->observabilityRequirements = [];
                $this->auditRequirements = [];

                $this->initializeBusinessFunction();
            }

            protected function executeBusiness(array $request): array
            {
                return [];
            }
        };

        $bfd = $bf->getCompleteContractDefinition();
        $errors = $this->validateBFD($bfd);

        $this->assertEmpty($errors, 'Valid BFD should not have validation errors. Errors: ' . json_encode($errors));
    }

    /**
     * Test 2: BFD missing functionId fails validation
     */
    public function test_bfd_missing_function_id_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => '',
                'functionName' => 'Test',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'contract' => ['request' => [], 'response' => [], 'errors' => []],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_function_id', $errors);
    }

    /**
     * Test 3: BFD missing functionName fails validation
     */
    public function test_bfd_missing_function_name_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => '',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'contract' => ['request' => [], 'response' => [], 'errors' => []],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_function_name', $errors);
    }

    /**
     * Test 4: BFD missing version fails validation
     */
    public function test_bfd_missing_version_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => 'Test',
                'functionVersion' => '',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'contract' => ['request' => [], 'response' => [], 'errors' => []],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_function_version', $errors);
    }

    /**
     * Test 5: BFD missing contract section fails validation
     */
    public function test_bfd_missing_contract_section_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => 'Test',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_contract_section', $errors);
    }

    /**
     * Test 6: BFD missing request contract fails validation
     */
    public function test_bfd_missing_request_contract_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => 'Test',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'contract' => [
                'response' => [],
                'errors' => [],
            ],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_request_contract', $errors);
    }

    /**
     * Test 7: BFD missing response contract fails validation
     */
    public function test_bfd_missing_response_contract_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => 'Test',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'contract' => [
                'request' => [],
                'errors' => [],
            ],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_response_contract', $errors);
    }

    /**
     * Test 8: BFD missing processing section fails validation
     */
    public function test_bfd_missing_processing_section_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => 'Test',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'contract' => ['request' => [], 'response' => [], 'errors' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_processing_section', $errors);
    }

    /**
     * Test 9: BFD missing operational section fails validation
     */
    public function test_bfd_missing_operational_section_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => 'Test',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => ['description' => 'Test', 'businessOwner' => 'Owner', 'technicalOwner' => 'Tech', 'tags' => [], 'classification' => 'Core', 'visibility' => 'Public'],
            'contract' => ['request' => [], 'response' => [], 'errors' => []],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_operational_section', $errors);
    }

    /**
     * Test 10: BFD missing metadata description fails validation
     */
    public function test_bfd_missing_metadata_description_fails_validation(): void
    {
        $bfd = [
            'identity' => [
                'functionId' => 'TEST.MODULE.DOMAIN.FUNCTION',
                'functionName' => 'Test',
                'functionVersion' => '1.0.0',
                'module' => 'TEST',
                'domain' => 'MODULE',
                'capability' => 'DOMAIN',
                'lifecycleStatus' => 'Draft',
            ],
            'metadata' => [
                'description' => '',
                'businessOwner' => 'Owner',
                'technicalOwner' => 'Tech',
                'tags' => [],
                'classification' => 'Core',
                'visibility' => 'Public',
            ],
            'contract' => ['request' => [], 'response' => [], 'errors' => []],
            'processing' => ['validation' => [], 'authorization' => [], 'businessRules' => []],
            'operational' => ['observability' => [], 'audit' => []],
        ];

        $errors = $this->validateBFD($bfd);
        $this->assertContains('missing_description', $errors);
    }

    /**
     * Test 11: Round-trip - BF → BFD → Validator validates
     *
     * This is the core round-trip test showing:
     * BusinessFunction → getCompleteContractDefinition() → BFD → Validator → VALID
     */
    public function test_business_function_round_trip_validation(): void
    {
        // Create a real BusinessFunction
        $bf = new class extends BusinessFunction {
            public function __construct()
            {
                $this->functionId = 'TEST.MODULE.DOMAIN.FUNCTION';
                $this->functionName = 'Test Function';
                $this->functionVersion = '1.0.0';
                $this->module = 'TEST';
                $this->domain = 'MODULE';
                $this->capability = 'DOMAIN';
                $this->lifecycleStatus = 'Draft';

                $this->description = 'A test function';
                $this->businessOwner = 'Owner';
                $this->technicalOwner = 'Tech Owner';
                $this->tags = ['test'];
                $this->classification = 'Core';
                $this->visibility = 'Public';
                $this->dependencies = [];
                $this->eventsPublished = [];
                $this->eventsConsumed = [];
                $this->keywords = [];

                $this->requestContract = [
                    'required' => ['field1'],
                    'fields' => ['field1' => ['type' => 'string']],
                ];
                $this->responseContract = ['description' => 'Response', 'fields' => []];
                $this->errorCategories = ['ValidationError'];
                $this->validationRules = ['field1' => 'required'];
                $this->authorizationRequirements = [];
                $this->businessRules = [];
                $this->observabilityRequirements = [];
                $this->auditRequirements = [];

                $this->initializeBusinessFunction();
            }

            protected function executeBusiness(array $request): array
            {
                return [];
            }
        };

        // Round-trip: BF → BFD → Validation
        $bfd = $bf->getCompleteContractDefinition();
        $errors = $this->validateBFD($bfd);

        // BFD must validate successfully
        $this->assertEmpty($errors, 'BusinessFunction round-trip should produce valid BFD');
    }
}
