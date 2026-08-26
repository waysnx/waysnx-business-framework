# WaysNX Business Framework — Laravel Implementation

Official Laravel 13 implementation of the WaysNX Business Framework (WBF).

## What is WBF?

WBF is an enterprise-grade architecture framework for building business applications with structured design patterns, governance, and standardized practices. It enables teams to:

- **Align code with business structure** — Organize applications into business domains using modules
- **Model business concepts** — Define entities, operations, and rules as first-class framework objects
- **Enforce business logic** — Apply business validations consistently across operations and processes
- **Orchestrate operations** — Coordinate multi-step business processes using workflows
- **Automate workflows** — Execute defined business processes with lifecycle management and event handling

## Why WBF?

Traditional applications often scatter business logic across controllers, services, and validation layers, making business intent difficult to extract and maintain. WBF inverts this approach: **business structure comes first**.

With WBF you define:
- **Modules** — business domains that organize related capabilities
- **Entities** — business objects with identity and structure
- **Business Functions** — discrete operations the business performs
- **Validations** — business rules that protect data and operations
- **Workflows** — multi-step processes that coordinate operations and manage state

Then you write the implementation code. WBF provides registries, lifecycle management, and orchestration infrastructure that your application can use to register and execute its business artifacts.

## What This Package Provides

This Laravel package provides:

- **Business Framework foundation** — Structured architecture for enterprise applications built on verified design patterns
- **Core concepts implemented** — Module, Entity, Business Function, Validation, and Workflow with registries and execution engines
- **Service container integration** — Auto-discovery with Laravel's service provider pattern
- **CLI commands** — Commands for managing, verifying, and generating business framework artifacts
- **Registry system** — Central registries for workflows, business functions, validations, modules, and entities
- **Workflow engine** — Execute defined business workflows with lifecycle management and state tracking
- **Validation framework** — Declarative validation engine with rules evaluation and issue reporting
- **Lifecycle management** — Hooks for before/after execution, custom logic injection, and error handling
- **UUID support** — Built-in UUID generation for all entities

## Requirements

- **PHP:** ^8.3
- **Laravel:** ^13.0

## Installation

```bash
composer require waysnx/business-framework
```

The package uses Laravel's auto-discovery. No manual service provider registration is required.

## Verify Installation

After installation, verify that WBF is correctly installed and all components are available:

```bash
php artisan wbf:doctor
```

This command checks system health and component availability.

## Core Concepts

WBF defines five core concepts that work together to structure business applications:

### Module

A **Module** is a logical business area that groups related capabilities, entities, operations, and rules.

- **For business:** A business domain that reflects organizational structure (e.g., Human Resources, Finance, Audit)
- **For developers:** A container with namespace, versioning, and dependencies that organizes entities, business functions, validations, and workflows
- **For automation:** A grouping construct with metadata enabling programmatic inspection and orchestration

### Entity

An **Entity** is a business object with identity that the application manages.

- **For business:** A concrete business concept with stable identity (e.g., Employee, Invoice, Audit, Product)
- **For developers:** A domain object or model representing a business concept. Entities may be backed by database tables, Eloquent models, or other persistence mechanisms depending on your application design
- **For automation:** A defined structure with properties enabling validation, operation, and workflow participation

### Business Function

A **Business Function** is a discrete business operation that performs or initiates a meaningful business action.

- **For business:** A capability the business performs (e.g., ApplyLeave, CreateInvoice, RecordFinding)
- **For developers:** An invokable operation with defined inputs, outputs, business logic, and permission requirements
- **For automation:** An executable unit that can be orchestrated and coordinated across workflows

### Validation

A **Validation** is a business rule or condition that determines whether data, an operation, or a situation satisfies a requirement.

- **For business:** A rule that protects data integrity and enforces business policies (e.g., "Critical findings must have evidence")
- **For developers:** A declarative specification of rules evaluated separately from business logic
- **For automation:** A set of conditions that gates operations and workflow transitions

### Workflow

A **Workflow** is a defined business process that coordinates activities, decisions, states, and transitions.

- **For business:** A multi-step process that manages complex business outcomes (e.g., Employee Onboarding, Audit Review)
- **For developers:** An orchestration specification that sequences business functions, manages state, and invokes lifecycle hooks
- **For automation:** A process definition enabling programmatic execution and monitoring

## How They Work Together

Concepts are organized in layers within a module:

```
Module (Container Layer)
├── Entities (Data Layer)          — Business objects managed
├── Business Functions (Operation Layer) — Discrete operations
├── Validations (Rule Layer)       — Business rule enforcement
└── Workflows (Orchestration Layer)  — Multi-step processes
```

Entities are operated on by business functions. Validations protect both entities and business functions. Workflows orchestrate business functions, evaluate validations at steps, and manage entity state throughout execution.

For example, in an Employee Onboarding workflow:
- **Module:** HumanResources
- **Entities:** Employee, Manager, Department
- **Business Functions:** CreateEmployee, AssignDepartment, SendWelcome
- **Validations:** ManagerMustExist, EmailMustBeValid, DepartmentMustHaveCapacity
- **Workflow:** OnboardingProcess (orchestrates functions → evaluates validations → manages employee state)

## Illustrative Example

Consider an audit management system (this is an illustrative example of how WBF concepts work together):

**Module:** AuditManagement
- Organizes audit-related business logic

**Entities:** Audit, Finding, Evidence, Auditor, Department
- Represent concrete business objects managed during audits

**Business Functions:** CreateAudit, StartAudit, RecordFinding, AttachEvidence, ReviewFinding, ResolveFinding, CloseAudit
- Each performs a discrete auditing operation

**Validations:** AuditMustHaveAssignedAuditor, FindingMustHaveSeverity, CriticalFindingRequiresEvidence, AuditCannotBeClosedWithUnresolvedCritical
- Each enforces business rules protecting audit integrity

**Workflow:** AuditReview
- Orchestrates the complete audit process: CreateAudit → StartAudit → ConductAudit → ReviewFindings → ResolveFindings → CloseAudit
- Evaluates validations at each step
- Manages audit state through transitions

> **Note:** This is an illustrative example showing how WBF concepts work together. It is not a claim that WaysNX Business Framework contains audit-specific functionality. Developers implement their own domain-specific modules and entities.

## Quick Start

### List Available Resources

```bash
php artisan wbf:list
```

Lists all available resource types. Use a specific resource type to see registered instances:

```bash
php artisan wbf:list workflows
php artisan wbf:list business-functions
php artisan wbf:list validations
php artisan wbf:list modules
php artisan wbf:list entities
```

## CLI Commands

The WBF Laravel package provides Artisan commands for managing, verifying, and generating business framework artifacts.

### wbf:doctor — Verify System Health

Check WBF system health and component availability.

```bash
php artisan wbf:doctor               # Show health summary
php artisan wbf:doctor --detail      # Show detailed diagnostics
php artisan wbf:doctor --json        # Output as JSON
php artisan wbf:doctor --component WorkflowEngine   # Check specific component
```

### wbf:list — List Resources

List registered WBF resources (modules, entities, business functions, validations, workflows).

```bash
php artisan wbf:list                          # List available resource types
php artisan wbf:list modules                  # List all modules
php artisan wbf:list entities                 # List all entities
php artisan wbf:list business-functions       # List all business functions
php artisan wbf:list validations              # List all validations
php artisan wbf:list workflows                # List all workflows

# Options:
php artisan wbf:list workflows --detailed     # Show detailed information
php artisan wbf:list workflows --filter=hr    # Filter by name
php artisan wbf:list workflows --module=hr    # Filter by module
php artisan wbf:list workflows --json         # Output as JSON
```

### wbf:show — Show Resource Details

Display detailed information about a specific resource.

```bash
php artisan wbf:show module HumanResources
php artisan wbf:show entity Employee
php artisan wbf:show business-function ApplyLeave
php artisan wbf:show validation CheckEligibility
php artisan wbf:show workflow OnboardingWorkflow

# Options:
php artisan wbf:show workflow OnboardingWorkflow --json   # Output as JSON
```

### wbf:make — Generate Artifacts

Create new WBF artifacts with scaffolded code ready for implementation.

```bash
php artisan wbf:make module HumanResources
php artisan wbf:make entity Employee
php artisan wbf:make business-function ApplyLeave
php artisan wbf:make validation CheckEligibility
php artisan wbf:make workflow OnboardingWorkflow

# Options:
php artisan wbf:make business-function ApplyLeave --module=hr
php artisan wbf:make business-function ApplyLeave --namespace="App\\Hr\\Functions"
php artisan wbf:make business-function ApplyLeave --description="Process leave requests"
php artisan wbf:make business-function ApplyLeave --force   # Overwrite if exists
php artisan wbf:make business-function ApplyLeave --json    # Output as JSON
```

### wbf:register — Verify Registration

Verify that a resource is registered and discoverable by the framework.

```bash
php artisan wbf:register module HumanResources
php artisan wbf:register entity Employee
php artisan wbf:register business-function ApplyLeave
php artisan wbf:register validation CheckEligibility
php artisan wbf:register workflow OnboardingWorkflow

# Options:
php artisan wbf:register workflow OnboardingWorkflow --fail-if-not-found  # Exit with error if not found
php artisan wbf:register workflow OnboardingWorkflow --json               # Output as JSON
```

## Generated Artifacts

When you run `wbf:make`, the command generates scaffolded code for the specified artifact type. Output paths and namespaces are configurable via command options but default to Laravel application structure.

### Generated Module

**Files generated:** 1 file
- **Path:** `app/Modules/{ModuleName}Definition.php`
- **Namespace:** `App\Modules` (configurable via `--namespace`)

Factory class with static `create()` method returns immutable `ModuleDefinition`.

### Generated Entity

**Files generated:** 1 file
- **Path:** `app/Models/{EntityName}Definition.php`
- **Namespace:** `App\Models` (configurable via `--namespace`)

Factory class with static `create()` method returns immutable `EntityDefinition`.

### Generated Business Function

**Files generated:** 1 file
- **Path:** `app/Business/Functions/{FunctionName}.php`
- **Namespace:** `App\Business\Functions` (configurable via `--namespace`)

Concrete class extending `WaysNX\BusinessFramework\Models\BusinessFunction` with `executeBusiness()` method for developer implementation.

### Generated Validation

**Files generated:** 1 file
- **Path:** `app/Business/Validations/{ValidationName}Definition.php`
- **Namespace:** `App\Business\Validations` (configurable via `--namespace`)

Factory class with static `create()` method returns immutable `ValidationDefinition`.

### Generated Workflow

**Files generated:** 2 files (atomic generation — either both created or neither)

**File 1 — Technical Specification:**
- **Path:** `app/Business/Workflows/{WorkflowName}Definition.php`
- **Namespace:** `App\Business\Workflows` (configurable via `--namespace`)
- **Content:** Factory class with static `create()` method returning immutable `WorkflowDefinition`
- **Purpose:** Technical execution specification for workflow engine

**File 2 — Governance Model:**
- **Path:** `app/Business/Workflows/Workflow{WorkflowName}.php`
- **Namespace:** `App\Business\Workflows` (configurable via `--namespace`)
- **Content:** Concrete class extending `WaysNX\BusinessFramework\Models\Workflow`
- **Purpose:** Business ownership and lifecycle management

Both files coexist to separate technical execution concerns from business governance.

### Manual Registration Required

Generated artifacts are **not automatically registered** to the registries. Application must manually register during bootstrap in a service provider:

```php
// In a service provider's boot() method:
$workflowRegistry = app(WorkflowRegistry::class);
$workflowRegistry->register(OnboardingDefinition::create());

$entityRegistry = app(EntityRegistry::class);
$entityRegistry->register(EmployeeDefinition::create());
```

## Registry System

The WBF framework uses registries to discover and lookup business framework artifacts at runtime:

- **ModuleRegistry** — Manages module definitions and lookup
- **EntityRegistry** — Manages entity definitions and lookup
- **BusinessFunctionRegistry** — Manages business function definitions and lookup
- **ValidationRegistry** — Manages validation definitions and lookup
- **WorkflowRegistry** — Manages workflow definitions and lookup

### How Registration Works

**Artifact generation (wbf:make) does NOT automatically register artifacts.**

1. **Generation:** `wbf:make` generates PHP factory classes (Definition classes) in application directories
2. **Definition:** Each generated artifact has a Definition class with a static `create()` method that returns an immutable definition object
3. **Registration:** Application code must manually register definitions during bootstrap:

```php
// In a service provider's boot() method:
$workflowRegistry = app(WorkflowRegistry::class);
$workflowRegistry->register(OnboardingDefinition::create());
```

4. **Discovery:** Once registered, the registry provides lookup methods (`findById()`, `findByName()`, `findByModule()`, etc.)
5. **Execution:** The WorkflowEngine and ValidationFramework use registries to find and execute artifacts

Use `wbf:list` to view all currently registered resources in a running application.

## Workflow Execution

The **WorkflowEngine** executes workflow definitions by coordinating business functions:

```php
$engine = new WorkflowEngine($workflowRegistry, $lifecycleManager);
$context = new WorkflowContext(
    workflowId: 'employee-onboarding',
    executionId: 'exec-123',
    entity: $employee,
    moduleId: 'hr',
    inputData: ['department' => 'engineering']
);
$result = $engine->execute('employee-onboarding', $context);
```

### WorkflowContext

Immutable value object carrying execution context:
- **workflowId** — Workflow identifier
- **executionId** — Unique execution instance
- **entity** — Business object being processed (e.g., Employee instance)
- **moduleId** — Module context
- **inputData** — Input parameters for workflow
- **correlationId** — Tracing/correlation ID
- **metadata** — Additional contextual data

### Workflow Execution Lifecycle

1. **Setup** — WorkflowContext carries execution parameters
2. **Step Execution** — Each workflow step executes in sequence
3. **Step Function Invocation** — WorkflowEngine invokes business functions associated with each step
4. **Business Logic** — Business functions (developer-implemented) perform domain operations
5. **State Management** — Business functions handle entity state changes (not automatic)
6. **Step Completion** — Engine tracks completed/failed steps
7. **Completion** — WorkflowResult reports outcome and timing

### WorkflowResult

Contains execution outcome:
- **status** — 'completed' or 'failed'
- **completedSteps** — Array of successful step IDs
- **failedSteps** — Array of failed step IDs
- **errors** — Error messages if any
- **warnings** — Warnings if any
- **duration** — Execution time in milliseconds
- **metadata** — Execution metadata (workflow name, step counts, etc.)

Methods available:
- `succeeded()` — True if no failed steps
- `failed()` — True if any steps failed
- `errorCount()` — Number of errors
- `summary()` — Human-readable summary

### Extension Points

Subclass WorkflowEngine to customize behavior:

- `beforeExecute()` — Called before workflow starts
- `afterExecute()` — Called after workflow completes
- `beforeStep()` — Called before each step
- `afterStep()` — Called after each step
- `executeBusinessFunction()` — Called to invoke step's function
- `determineNextStep()` — Called to determine next step to execute

## Validation Framework

The **ValidationFramework** executes validation rules:

1. **Rule Evaluation** — Rules are evaluated against entities or operations
2. **Issue Reporting** — Violations produce validation issues
3. **Severity Classification** — Issues are classified as errors or warnings
4. **Scope Application** — Validations apply to specific entity types or operation scopes
5. **Declarative Specification** — Validation rules are defined separate from business logic

## Laravel Integration

The WBF Laravel package integrates seamlessly with Laravel's service container and auto-discovery:

- **Auto-discovery:** The `LaravelServiceProvider` is automatically discovered and registered by Laravel during bootstrap
- **Service container:** All WBF components (registries, workflow engine, validation framework, lifecycle manager) are bound to Laravel's container as singletons and injectable into your classes
- **Configuration:** Configuration is published to `config/business-framework.php` and managed through Laravel's config system
- **Artisan commands:** All WBF CLI commands are available through `php artisan`

### Using Components in Your Code

Inject WBF components into your classes via constructor injection:

```php
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Workflow\WorkflowEngine;
use WaysNX\BusinessFramework\Workflow\WorkflowContext;

class AuditController {
    public function __construct(
        private WorkflowRegistry $workflows,
        private WorkflowEngine $engine
    ) {}
    
    public function startAudit() {
        // Retrieve workflow definition from registry
        $workflow = $this->workflows->findById('audit-review');
        
        // Create execution context
        $context = new WorkflowContext(
            workflowId: 'audit-review',
            executionId: 'audit-' . uniqid(),
            entity: $audit,
            inputData: ['department' => $audit->department]
        );
        
        // Execute workflow
        $result = $this->engine->execute('audit-review', $context);
        
        if ($result->succeeded()) {
            // Handle success
        } else {
            // Handle failure
            foreach ($result->errors as $error) {
                logger()->error($error);
            }
        }
    }
}
```

### Service Provider Binding

The `BusinessFrameworkServiceProvider` registers all WBF components:

```php
// All components are bound as singletons:
$this->app->singleton(WorkflowRegistry::class, ...);
$this->app->singleton(WorkflowEngine::class, ...);
$this->app->singleton(ValidationFramework::class, ...);
// ... and others
```

This means:
- All components are available via Laravel's container
- You can inject them into controllers, models, jobs, listeners, etc.
- They are singleton instances (one per request lifecycle)

## Technology-Neutral Design

WBF is designed so its core business concepts can be implemented across different technology stacks. The fundamental concepts (Module, Entity, Business Function, Validation, Workflow) are technology-agnostic.

- **Laravel** — This package (PHP/Laravel)
- **Other platforms** — WBF can be implemented in other frameworks and languages. Future implementations may be provided as the framework evolves

The business concepts remain consistent; implementation details adapt to each platform's conventions and capabilities.

## For AI Agents and Automation

WBF is designed to enable machine readability and programmatic inspection of business structure. The explicit, unambiguous concepts make it suitable for tooling and automation:

### Definition vs Runtime Distinction

WBF clearly separates metadata from execution:

- **Definition classes** (metadata): `ModuleDefinition`, `EntityDefinition`, `BusinessFunctionDefinition`, `ValidationDefinition`, `WorkflowDefinition`
  - Immutable value objects containing specification and metadata
  - Registered in discoverable registries
  - Suitable for inspection and analysis
  
- **Runtime/Execution classes** (behavior): `WorkflowEngine`, `ValidationFramework`, `BusinessFunctionRuntime`
  - Execute definitions
  - Manage state and lifecycle
  - Invoke business logic

This separation enables:
- Schema extraction and analysis
- Automated workflow generation (future capability)
- AI-driven development assistance (future capability)
- Business process visualization (future capability)
- Enterprise governance tools (future capability)

### Inspectable Metadata

Each concept provides machine-readable metadata:

- **Module:** id, name, displayName, namespace, version, dependencies, enabled, tags
- **Entity:** id, name, displayName, className, namespace, version, tags
- **Business Function:** id, name, displayName, moduleId, category, version, inputDefinitions, outputDefinitions, supportedEntityTypes
- **Validation:** id, name, displayName, moduleId, scope, severity, rules
- **Workflow:** id, name, displayName, moduleId, steps, entryFunction, exitFunction, triggerType, triggerEvent

Applications can query registries to discover structure:

```php
$allWorkflows = $registry->all();
$hrWorkflows = $registry->findByModule('hr');
$eventTriggeredWorkflows = $registry->findByTrigger('event');
```

This enables automated analysis, documentation generation, and future AI capabilities.

## System Requirements

- **PHP 8.3 or higher** — For syntax features (attributes, typed properties, etc.)
- **Laravel 13** — For service provider, auto-discovery, and Artisan integration
- **Ramsey UUID** — For built-in UUID generation

## File Structure

The WBF Laravel package structure:

```
.
├── src/
│   ├── Registry/              # Central registries for artifact lookup
│   ├── Workflow/              # Workflow engine and execution
│   ├── Validation/            # Validation framework
│   ├── Runtime/               # Runtime execution components
│   ├── Console/               # Artisan CLI commands and generators
│   ├── Providers/             # Service providers
│   ├── Models/                # Base model classes
│   ├── Lifecycle/             # Lifecycle event management
│   ├── Contracts/             # Interfaces and contracts
│   ├── Exceptions/            # Exception types
│   └── ...
├── config/
│   └── business-framework.php # Package configuration
├── tests/
│   └── ...                    # Test suite (817 tests)
├── composer.json              # Package metadata
├── composer.lock              # Dependency lock file
├── phpunit.xml                # Test configuration
├── README.md                  # This file
├── LICENSE                    # Apache 2.0 license
├── NOTICE                     # Attribution notice
└── .gitignore                 # Git ignore rules
```

## Documentation

For comprehensive documentation on the WaysNX Business Framework architecture, design patterns, specifications, and multi-technology implementation guidance, visit the main repository:

**[WaysNX Business Framework](https://github.com/waysnx/waysnx-business-framework)**

That repository contains:
- Full architecture documentation
- Framework specifications and design documents
- Architectural Decision Records (ADRs)
- Multi-technology implementation guidance
- Broader ecosystem information
- Contributing guidelines

## Contributing

Contributions are welcome. Please refer to the contribution guidelines in the main WaysNX Business Framework repository:

**[WaysNX Business Framework - Contributing](https://github.com/waysnx/waysnx-business-framework/blob/main/CONTRIBUTING.md)**

## License

Apache License 2.0

See the `LICENSE` file for full license text.

---

**Built with WaysNX Business Framework** — Enterprise Architecture for Modern Applications
