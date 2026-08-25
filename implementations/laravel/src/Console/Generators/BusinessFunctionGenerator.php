<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Generators;

/**
 * BusinessFunctionGenerator
 *
 * Generates a BusinessFunction implementation class.
 *
 * @package WaysNX\BusinessFramework\Console\Generators
 */
class BusinessFunctionGenerator extends ArtifactGenerator
{
    /**
     * Optional description
     *
     * @var string|null
     */
    private ?string $description;

    /**
     * Optional domain
     *
     * @var string|null
     */
    private ?string $domain;

    /**
     * Optional capability
     *
     * @var string|null
     */
    private ?string $capability;

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return self
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Set capability
     *
     * @param string $capability
     * @return self
     */
    public function setCapability(string $capability): self
    {
        $this->capability = $capability;
        return $this;
    }

    /**
     * Generate the business function
     *
     * @return GenerationResult
     */
    public function generate(): GenerationResult
    {
        try {
            $className = $this->getClassName();
            $filePath = $this->outputPath . '/' . $className . '.php';

            $functionId = $this->toSnakeCase($this->name);
            $functionName = $this->toPascalCase($this->name);
            $domain = $this->domain ?? 'default';
            $capability = $this->capability ?? 'default';
            $description = $this->description ?? "Business function: {$this->name}";

            $code = "<?php\n\n"
                . "declare(strict_types=1);\n\n"
                . "namespace " . $this->namespace . ";\n\n"
                . "use WaysNX\\BusinessFramework\\Models\\BusinessFunction;\n\n"
                . "/**\n"
                . " * " . $className . "\n"
                . " *\n"
                . " * Generated business function implementation for " . $this->name . ".\n"
                . " */\n"
                . "class " . $className . " extends BusinessFunction\n"
                . "{\n"
                . "    public function __construct()\n"
                . "    {\n"
                . "        \$this->functionId = '" . $functionId . "';\n"
                . "        \$this->functionName = '" . $functionName . "';\n"
                . "        \$this->functionVersion = '1.0.0';\n"
                . "        \$this->module = '" . $this->module . "';\n"
                . "        \$this->domain = '" . $domain . "';\n"
                . "        \$this->capability = '" . $capability . "';\n"
                . "        \$this->lifecycleStatus = 'Draft';\n"
                . "        \$this->description = '" . addslashes($description) . "';\n"
                . "        \$this->businessOwner = 'to-be-assigned';\n"
                . "        \$this->technicalOwner = 'to-be-assigned';\n"
                . "        \$this->classification = 'Core';\n"
                . "        \$this->visibility = 'Internal';\n"
                . "        \$this->tags = [];\n"
                . "        \$this->requestContract = ['required' => [], 'optional' => [], 'fields' => []];\n"
                . "        \$this->responseContract = ['fields' => [], 'success_fields' => []];\n"
                . "        \$this->errorCategories = ['validation_error', 'authorization_error', 'execution_error'];\n"
                . "        \$this->validationRules = [];\n"
                . "        \$this->authorizationRequirements = [];\n"
                . "        \$this->businessRules = [];\n"
                . "        \$this->observabilityRequirements = [];\n"
                . "        \$this->auditRequirements = [];\n"
                . "        \$this->initializeBusinessFunction();\n"
                . "    }\n\n"
                . "    protected function executeBusiness(array \$request): array\n"
                . "    {\n"
                . "        return ['status' => 'success', 'message' => 'Business function executed'];\n"
                . "    }\n"
                . "}\n";

            if ($this->writeFile($filePath, $code)) {
                return GenerationResult::success([$filePath], [
                    'type' => 'business-function',
                    'name' => $this->name,
                    'module' => $this->module,
                ]);
            }

            return GenerationResult::failure('Failed to write file', 'io_error');
        } catch (\Throwable $e) {
            return GenerationResult::failure(
                "Generation failed: {$e->getMessage()}",
                'system_error'
            );
        }
    }
}
