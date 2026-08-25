# WaysNX Business Framework — Laravel Implementation

Official Laravel 13 implementation of the WaysNX Business Framework (WBF).

**WBF** is an enterprise-grade architecture framework for building business applications with structured design patterns, governance, and standardized practices.

## What This Package Provides

This Laravel package provides:

- **Business Framework foundation** — Structured architecture for enterprise applications
- **Service container integration** — Auto-discovery with Laravel's service provider pattern
- **CLI commands** — Commands for managing workflows, business functions, validations, modules, and entities
- **Registry system** — Central registries for workflows, business functions, validations, modules, and entities
- **Workflow engine** — Execute defined business workflows with lifecycle management
- **Validation framework** — Declarative validation engine with rules and issues reporting
- **Lifecycle management** — Hooks for before/after execution, custom logic injection
- **UUID support** — Built-in UUID generation for all entities

## Requirements

- **PHP:** ^8.3
- **Laravel:** ^13.0

## Installation

```bash
composer require waysnx/business-framework
```

The package uses Laravel's auto-discovery. No manual service provider registration is required.

## Quick Start

### Verify Installation

```bash
php artisan wbf:doctor
```

This command checks that the WBF package is correctly installed and all components are available.

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

The package provides the following Artisan commands:

### wbf:doctor — Verify Installation

Check WBF system health and component availability.

```bash
php artisan wbf:doctor               # Show health summary
php artisan wbf:doctor --detail      # Show detailed diagnostics
php artisan wbf:doctor --json        # Output as JSON
php artisan wbf:doctor --component WorkflowEngine   # Check specific component
```

### wbf:list — List Resources

List registered WBF resources (workflows, business functions, validations, modules, entities).

```bash
php artisan wbf:list                          # List available resource types
php artisan wbf:list workflows                # List all workflows
php artisan wbf:list business-functions       # List all business functions
php artisan wbf:list validations              # List all validations
php artisan wbf:list modules                  # List all modules
php artisan wbf:list entities                 # List all entities

# Options:
php artisan wbf:list workflows --detailed     # Show detailed information
php artisan wbf:list workflows --filter=hr    # Filter by name
php artisan wbf:list workflows --module=hr    # Filter by module
php artisan wbf:list workflows --json         # Output as JSON
```

### wbf:show — Show Resource Details

Display detailed information about a specific resource.

```bash
php artisan wbf:show workflow employee-onboarding
php artisan wbf:show business-function apply-leave
php artisan wbf:show validation check-eligibility
php artisan wbf:show module hr
php artisan wbf:show entity Employee

# Options:
php artisan wbf:show workflow employee-onboarding --json   # Output as JSON
```

### wbf:make — Generate Artifacts

Create new WBF artifacts (workflows, business functions, validations, modules, entities).

```bash
php artisan wbf:make workflow OnboardingWorkflow
php artisan wbf:make business-function ApplyLeave
php artisan wbf:make validation CheckEligibility
php artisan wbf:make module HumanResources
php artisan wbf:make entity Employee

# Options:
php artisan wbf:make workflow OnboardingWorkflow --module=hr
php artisan wbf:make workflow OnboardingWorkflow --description="Employee onboarding workflow"
php artisan wbf:make business-function ApplyLeave --namespace="App\\Hr\\Functions"
php artisan wbf:make business-function ApplyLeave --force   # Overwrite if exists
php artisan wbf:make workflow OnboardingWorkflow --json     # Output as JSON
```

### wbf:register — Verify Registration

Verify that a resource is registered in its registry.

```bash
php artisan wbf:register workflow employee-onboarding
php artisan wbf:register business-function apply-leave
php artisan wbf:register validation check-eligibility

# Options:
php artisan wbf:register workflow employee-onboarding --fail-if-not-found  # Exit with error if not found
php artisan wbf:register workflow employee-onboarding --json               # Output as JSON
```

## Example: Create a Business Function

```bash
php artisan wbf:make business-function SmokeTestFunction
```

This creates a new business function with scaffolded code ready for implementation.

## Laravel Integration

The WBF Laravel package integrates seamlessly with Laravel's service container and auto-discovery:

- **Auto-discovery:** The `LaravelServiceProvider` is automatically discovered and registered by Laravel
- **Service container:** All WBF components (registries, workflow engine, validation framework, lifecycle manager) are bound to Laravel's container and injectable into your classes
- **Configuration:** Configuration is published to `config/business-framework.php` and managed through Laravel's config system
- **Artisan commands:** All WBF commands are available through `php artisan`

## Documentation

For comprehensive documentation on the WaysNX Business Framework architecture, design patterns, specifications, and guidance, visit the main repository:

**[WaysNX Business Framework](https://github.com/waysnx/waysnx-business-framework)**

That repository contains:
- Full architecture documentation
- Framework specifications
- Design decisions (ADRs)
- Multi-technology implementation guidance
- Broader ecosystem information

## License

Apache License 2.0

See the `LICENSE` file for full license text.

---

**Built with WaysNX Business Framework** — Enterprise Architecture for Modern Applications
