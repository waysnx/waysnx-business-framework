<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Generators;

class ModuleGenerator extends ArtifactGenerator
{
    public function generate(): GenerationResult
    {
        try {
            $className = $this->getDefinitionClassName();
            $filePath = $this->outputPath . '/' . $className . '.php';

            $code = "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$this->namespace};\n\nuse WaysNX\\BusinessFramework\\Registry\\ModuleDefinition;\n\nclass {$className}\n{\n    public static function create(): ModuleDefinition\n    {\n        return new ModuleDefinition(\n            id: '{$this->toSnakeCase($this->name)}',\n            name: '{$this->getClassName()}',\n            displayName: '{$this->getClassName()}',\n            namespace: '{$this->namespace}',\n            version: '1.0.0',\n            enabled: true\n        );\n    }\n}\n";

            if ($this->writeFile($filePath, $code)) {
                return GenerationResult::success([$filePath]);
            }

            return GenerationResult::failure('Failed to write file', 'io_error');
        } catch (\Throwable $e) {
            return GenerationResult::failure("Generation failed: {$e->getMessage()}", 'system_error');
        }
    }
}
