<div align="center">

# WaysNX Business Framework (WBF)

### Business-First Application Architecture Framework for Building Modern, AI-Ready Software Systems

*Architecture First • Documentation Driven • AI Ready • Technology Agnostic • Enterprise Focused*

---

<!-- Replace with actual badges -->

![Status](https://img.shields.io/badge/status-active%20development-brightgreen)
![Version](https://img.shields.io/badge/version-1.0.0--preview-blue)
![License](https://img.shields.io/badge/license-Apache%202.0-orange)
![Architecture](https://img.shields.io/badge/architecture-business--first-8A2BE2)
![Platform](https://img.shields.io/badge/platform-technology%20agnostic-00A8E8)
![AI](https://img.shields.io/badge/AI-ready-success)
![Specifications](https://img.shields.io/badge/specifications-draft-yellow)

</div>

---

## Table of Contents

- [What is WBF?](#what-is-wbf)
- [Why WBF?](#why-wbf)
- [Vision](#vision)
- [Guiding Principles](#guiding-principles)
- [Framework Components](#framework-components)
- [WBF Ecosystem](#wbf-ecosystem)
- [Key Features](#key-features)
- [Reference Implementations](#reference-implementations)
- [Repository Structure](#repository-structure)
- [Reading Paths](#reading-paths)
- [Documentation Overview](#documentation-overview)
- [Design Philosophy](#design-philosophy)
- [Core Principles](#core-principles)
- [Getting Started](#getting-started)
- [Framework Lifecycle](#framework-lifecycle)
- [Current Status](#current-status)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [Community](#community)
- [License](#license)

---

## What is WBF?

**WaysNX Business Framework (WBF)** is an opinionated enterprise architecture framework that provides a structured approach for designing, developing, governing, and maintaining modern business applications.

Unlike traditional frameworks that primarily focus on implementation, WBF emphasizes **architecture, standards, governance, documentation, and AI-assisted engineering** as first-class concerns.

WBF is designed to help engineering teams build scalable, maintainable, and consistent software systems while reducing architectural drift across projects and teams.

---

## Why WBF?

Modern software development often suffers from:

- Inconsistent architecture across projects
- Lack of engineering standards
- Poor documentation
- Reinvented project structures
- Technology-specific lock-in
- Difficult onboarding
- Limited governance
- Growing complexity in AI-assisted development

WBF addresses these challenges by providing a technology-agnostic framework that standardizes architectural practices while remaining flexible enough to support different programming languages, frameworks, and deployment models.

---

## Vision

Our vision is to establish WBF as an open, vendor-neutral framework that enables organizations to design and build enterprise-grade software with consistency, quality, and long-term maintainability.

WBF is intended to serve as the architectural foundation for modern engineering teams adopting cloud-native development, event-driven systems, AI-assisted software engineering, and enterprise governance.

---

## Framework Goals

The WaysNX Business Framework aims to:

- Establish a consistent enterprise architecture foundation
- Standardize software engineering practices
- Enable AI-assisted software engineering
- Reduce architectural drift across projects
- Promote reusable engineering assets
- Improve documentation quality
- Accelerate project onboarding
- Support long-term maintainability and governance

## Guiding Principles

- 🏗 Architecture before implementation
- 📚 Documentation as a first-class artifact
- 🤖 AI-ready engineering practices
- 🔒 Security by design
- 🧩 Modular and extensible architecture
- 🌐 Technology-agnostic design
- 📈 Enterprise scalability
- ♻ Consistency through standards

---

## Who Should Use WBF?

WBF is designed for:

- Enterprise Architects
- Solution Architects
- Software Architects
- Technical Leads
- Engineering Managers
- CTOs and Technology Leaders
- Platform Engineering Teams
- Software Development Teams
- Consulting Organizations
- System Integrators

---

## Current Project Status

> **Preview – draft specifications, early reference implementations**

WBF is in **preview** (tag `v1.0.0-preview`). The normative foundation specifications (`specification/foundation/WBF-SPEC-0000…0005`) are **drafts**, and many descriptive documents, ADRs, templates and schemas are still placeholders. The Laravel and Node.js reference implementations are at an early (0.x) stage. Both pass the 33 fixtures of the shared conformance suite v0.1.0 (untagged). Those fixtures cover only narrow slices of five contract areas, so broader equivalence is not claimed. See [Current Status](#current-status).

The repository includes:

- Architecture specifications
- Engineering standards
- Governance model
- Documentation
- Reference designs
- Templates
- Schemas
- Best practices
- Shared cross-runtime conformance fixtures (`tests/conformance/`)
- Links to the reference implementations (Laravel, Node.js/TypeScript), which live in their own repositories

Supporting libraries and additional ecosystem components are under development and will be introduced as the framework ecosystem expands.

---

## Framework Components

WBF is organized into a collection of complementary building blocks that together define a complete engineering framework for designing, implementing, governing, and evolving enterprise software systems.

| Component | Purpose |
|-----------|---------|
| **Blueprint** | Defines the architectural vision, design principles, and framework philosophy. |
| **Specifications** | Functional and technical specifications describing framework capabilities and standards. |
| **Standards** | Engineering standards covering architecture, APIs, security, testing, documentation, versioning, and governance. |
| **Templates** | Reusable templates for services, documentation, APIs, repositories, and project structure. |
| **Schemas** | Standardized schemas for configuration, metadata, events, contracts, and validation. |
| **Examples** | Reference examples demonstrating recommended implementation patterns and best practices. |
| **Tools** | Utilities that support validation, documentation, code generation, and engineering workflows. |
| **Governance** | Policies, contribution guidelines, architectural decision records, and release management. |
| **AI Knowledge Base** | Documentation structured to support AI-assisted development, code generation, reviews, and engineering automation. |

---

## WBF Ecosystem

WBF is the architectural foundation of the broader WaysNX engineering ecosystem.

```text
                    WaysNX Business Framework
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
      WaysNX DQP          WaysNX UI Kit      WaysNX Studio
          │                    │                    │
          └────────────────────┼────────────────────┘
                               │
                   Enterprise Business Applications
```

Each ecosystem component serves a distinct purpose while following the architectural principles, engineering standards, and governance defined by WBF.

| Project | Purpose |
|----------|---------|
| **WaysNX Business Framework (WBF)** | Enterprise architecture framework and engineering standards. |
| **WaysNX DQP** | Development Quality Platform for architecture validation, documentation analysis, compliance checks, and engineering insights. |
| **WaysNX UI Kit** | Enterprise React component library implementing WBF design principles and UI standards. |
| **WaysNX Studio** | Low-code / application development platform built on WBF standards. |

---

## Key Features

WBF combines architectural guidance with engineering governance to provide a consistent approach for building modern business applications.

### Architecture

- Enterprise Architecture Principles
- Layered Architecture Guidelines
- Domain-Driven Design Support
- Modular Design
- Event-Driven Architecture
- API-First Design
- Cloud-Native Patterns

### Engineering Standards

- Repository Standards
- Coding Standards
- Documentation Standards
- API Standards
- Versioning Strategy
- Security Guidelines
- Testing Standards
- Review Process

### Governance

- Architecture Decision Records (ADR)
- Change Management
- Contribution Guidelines
- Review Process
- Release Lifecycle
- Framework Evolution

### AI-First Engineering

- AI-ready documentation
- AI-assisted development
- AI-generated implementation guidance
- AI review workflows
- AI knowledge organization
- Prompt-driven engineering

### Enterprise Ready

- Technology agnostic
- Vendor neutral
- Scalable architecture
- Extensible framework
- Multi-project consistency
- Long-term maintainability

## Reference Implementations

WaysNX Business Framework (WBF) is designed to be **technology agnostic**.

The framework defines the business architecture, standards, lifecycle, and execution model independently of any specific programming language or framework. Reference implementations demonstrate how WBF concepts and architectural semantics are realized within a particular technology stack while preserving the core architecture and design principles.

### Available Reference Implementations

| Implementation | Technology | Status | Repository |
|---|---|---|---|
| Laravel | PHP / Laravel | Early implementation (tag `v0.1.0`) | [business-framework-laravel](https://github.com/waysnx/business-framework-laravel) |
| Node.js | TypeScript / Node.js | Early implementation (package `0.4.0`, no release tag) | [business-framework-node](https://github.com/waysnx/business-framework-node) |

#### Laravel

The Laravel implementation provides WBF capabilities for PHP/Laravel applications, including definitions, registries, business-function runtime, validation, workflow orchestration, and CLI tooling.

**Repository:** [business-framework-laravel](https://github.com/waysnx/business-framework-laravel)

#### Node.js / TypeScript

The Node.js/TypeScript implementation provides the WBF business architecture for Node.js applications, including definitions, registries, runtime, workflow orchestration, validation, lifecycle management, and CLI tools.

**Repository:** [business-framework-node](https://github.com/waysnx/business-framework-node)

Both implementations **aim** to follow the same WBF concepts and semantics while using idiomatic technology-specific patterns. **Equivalence is not claimed.** It is established only for contract areas where both runtimes pass the shared conformance fixtures in `tests/conformance/fixtures/`. See [docs/architecture/IMPLEMENTATION_CAPABILITY_MATRIX.md](docs/architecture/IMPLEMENTATION_CAPABILITY_MATRIX.md) and [docs/audit/PHASE_0_1_COMPLETION_REPORT.md](docs/audit/PHASE_0_1_COMPLETION_REPORT.md).

### Potential Future Reference Implementations

- Spring Boot (Java)
- ASP.NET (C#)
- Go
- Python

As the project evolves, additional reference implementations may be developed to demonstrate how WBF can be adopted across multiple technology platforms while maintaining a consistent business-first architecture.

## Repository Structure

The repository is organized around the lifecycle of enterprise software architecture rather than a specific programming language or technology stack.

```text
waysnx-business-framework/
│
├── docs/               Documentation and guides
├── specification/      Functional and technical specifications
├── schemas/            Standard schemas and contracts
├── templates/          Reusable project and document templates
├── examples/           Reference examples and usage patterns
├── packages/           Legacy placeholders; PHP copies removed under ADR-0005 (see packages/README.md)
├── implementations/    Pointers only; runtimes live in their own repositories (ADR-0005)
├── decisions/          Architecture Decision Records (ADR)
├── tools/              Validation and engineering tools (planned)
├── tests/              Framework validation assets (tests/conformance: shared conformance fixtures)
├── review/             Review checklist, template and history
└── .github/            Repository automation and workflows
```

Each directory has a clearly defined responsibility, helping maintain a consistent repository structure across projects adopting WBF.

---

## What WBF Does Not Attempt

WBF is **not** intended to replace existing technologies or development frameworks.

Instead, it complements them by providing architectural guidance and engineering standards.

WBF is not:

- A programming language
- A web framework
- A UI component library
- A low-code platform
- A project management methodology
- A replacement for Spring, Laravel, .NET, React, Angular, or similar technologies

Instead, WBF provides the architectural foundation that enables these technologies to be adopted consistently across enterprise software projects.

---

## Reading Paths

Different readers have different goals. Choose the path that best matches your role.

| Role | Start Here | Estimated Time |
|------|------------|---------------:|
| Executive / CTO | Vision, Architecture Principles, Roadmap | 20–30 minutes |
| Enterprise Architect | Blueprint, Standards, Specifications | 1–2 hours |
| Solution Architect | Repository Structure, Templates, Schemas | 2–3 hours |
| Technical Lead | Standards, Examples, Governance | 2–4 hours |
| Developer | Examples, Templates, Specifications | 3–4 hours |
| Contributor | Contributing Guide, Governance, ADRs | 1–2 hours |

For a guided introduction, see **START_HERE.md**.

---

## Documentation Overview

WBF documentation is organized into logical categories to make navigation easier as the framework evolves.

| Category | Description |
|----------|-------------|
| Blueprint | Framework vision, philosophy and architectural direction |
| Specifications | Functional and technical specifications |
| Standards | Engineering standards and best practices |
| Governance | Policies, decision records and contribution process |
| Templates | Reusable templates for projects and documentation |
| Schemas | Common contracts and metadata definitions |
| Examples | Sample implementations and reference patterns |
| Tools | Validation and automation utilities |

Every document follows a consistent structure to improve readability, discoverability, and long-term maintainability.

---

## Design Philosophy

WBF is built around a simple principle:

> **Good software begins with good architecture. Great software is sustained through standards, governance, and continuous evolution.**

Rather than prescribing a single technology stack, WBF establishes architectural principles and engineering practices that can be applied consistently across different languages, frameworks, cloud platforms, and organizational environments.

The framework encourages teams to make deliberate architectural decisions, document those decisions, and evolve systems through repeatable engineering practices rather than ad hoc implementation.

---

## Core Principles

The following principles guide every component of WBF.

### Architecture First

Architecture should drive implementation—not the other way around.

---

### Documentation First

Documentation is a core engineering artifact, not an afterthought.

---

### Standards Over Convention

Consistency reduces complexity and improves collaboration.

---

### Technology Agnostic

Choose technologies based on business needs while maintaining consistent architectural principles.

---

### AI as a First-Class Citizen

Documentation and standards are designed to support AI-assisted engineering, automated analysis, and intelligent development workflows.

---

### Enterprise by Default

Design for maintainability, scalability, security, and governance from the beginning rather than introducing them later.

---

## Getting Started

The WaysNX Business Framework is designed to be explored progressively. Understanding the architectural philosophy and engineering standards forms the foundation of the framework.

## Recommended Reading Order

### 1. Understand the Vision

- README.md
- START_HERE.md

Understand the goals, philosophy, and intended audience of WBF.

---

### 2. Explore the Architecture

Study the framework blueprint and architectural principles.

Recommended topics:

- Architecture Principles
- Core Concepts
- Blueprint
- Repository Structure

---

### 3. Discover Reference Implementations

WBF has reference implementations available for Laravel and Node.js/TypeScript.

- [Laravel Implementation](https://github.com/waysnx/business-framework-laravel)
- [Node.js/TypeScript Implementation](https://github.com/waysnx/business-framework-node)

Each implementation includes detailed documentation and examples for its respective technology stack.

---

### 4. Review Engineering Standards

Learn how WBF standardizes software engineering.

Focus areas include:

- API Standards
- Documentation Standards
- Security Standards
- Testing Standards
- Versioning Strategy
- Coding Standards

---

### 5. Explore Specifications

Specifications describe the framework's capabilities, conventions, and engineering guidance.

---

### 6. Review Templates and Schemas

Templates and schemas provide reusable building blocks for projects adopting WBF.

---

### 7. Study Reference Examples

Reference examples demonstrate how architectural principles are applied in practice.

---

## Framework Lifecycle

WBF follows an iterative evolution model.

```text
Vision
   │
Blueprint
   │
Specifications
   │
Standards
   │
Templates
   │
Reference Implementation
   │
Validation
   │
Continuous Improvement
```

Each phase builds upon the previous one, ensuring architecture remains aligned with implementation throughout the project lifecycle.

---

## Current Status

Status vocabulary:

- **Specified**: a normative spec exists (WBF-SPEC)
- **Implemented**: code exists in a runtime
- **Tested**: covered by that runtime's own tests
- **Conformance verified**: passes the shared fixtures in `tests/conformance/fixtures`
- **Released**: in a tagged release

| Area | Status (verified 2026-11-01) |
|------|--------|
| Foundation specifications (WBF-SPEC-0000…0005) | 🚧 Specified as **Draft** (not yet approved) |
| Descriptive architecture documents (WBF-DOC-0000…0140) | 🚧 Draft; a number are placeholders (see `docs/00-governance/DOCUMENT_REGISTRY.md`) |
| Governance | 🚧 In progress: governance docs and ADR-0001…0004 are placeholders. ADR-0005, 0008, 0009, 0010 Accepted; ADR-0007 Accepted in part; ADR-0006 Proposed |
| JSON schemas | 🚧 Placeholders (`$schema` only) |
| Templates | 🚧 In progress (placeholders) |
| Laravel runtime | Implemented, tested (own suite green), tag `v0.1.0` |
| Node.js runtime | Implemented, tested (own suite green), package `0.4.0`, no release tag |
| Cross-runtime conformance | 🚧 Suite v0.1.0, 33 fixtures (validation, workflow, business function, authorization, lifecycle). **Laravel 33/33, Node 33/33**, no known differences, identical normalized outputs. Suite not tagged, CI not active. The fixtures cover narrow slices of each area, so this is **not** a general parity claim |
| Framework Libraries | 🚧 Planned |
| Development Quality Platform (DQP) | 🚧 Under development |

See [ROADMAP.md](ROADMAP.md) for the master roadmap and [docs/audit/PHASE_0_1_COMPLETION_REPORT.md](docs/audit/PHASE_0_1_COMPLETION_REPORT.md) for evidence.

---

## Roadmap

The evolution of WBF is planned in progressive phases.

## Phase 1 – Framework Foundation

- Architecture Blueprint
- Standards
- Specifications
- Documentation
- Governance
- Repository Structure

**Status:** In progress. Foundation specifications are drafts. See [ROADMAP.md](ROADMAP.md) milestones M0–M2.

---

## Phase 2 – Validation

- Community Review
- Architecture Review
- Documentation Improvements
- Reference Validation

**Status:** In progress. The repository audit, Phase 0 and Phase 0.1 are complete. Decisions D1, D2, D6, D8 and ADR-0005/0008 are approved. ADR-0006 (Entity) is still Proposed.

---

## Phase 3 – Reference Implementations

- Laravel implementation (early, 0.x)
- Node.js / TypeScript implementation (early, 0.x)
- Shared conformance suite (initial, v0.1.0)
- Sample Applications
- Integration Examples
- Best Practice Guides

**Status:** Early implementations exist. Both pass the 33 fixtures of the untagged suite v0.1.0. Conformance CI is prepared but not active (ROADMAP.md M3).

---

## Phase 4 – Ecosystem Expansion

- WaysNX DQP
- WaysNX UI Kit
- WaysNX Studio
- Additional Framework Extensions

---

## Contributing

We welcome contributions from architects, engineers, technical writers, and the broader software engineering community.

Contributions may include:

- Architecture improvements
- Documentation enhancements
- Engineering standards
- Reference implementations
- Templates
- Examples
- Framework tooling
- Quality reviews

Please review the following documents before contributing:

- CONTRIBUTING.md
- CODE_OF_CONDUCT.md
- GOVERNANCE.md
- REVIEW_GUIDE.md

---

## Community & Collaboration

WBF is developed as an open engineering initiative focused on improving enterprise software architecture and engineering practices.

We encourage:

- Architecture discussions
- Design reviews
- Standards proposals
- Documentation improvements
- Constructive feedback
- Community collaboration

---

## License

Licensed under the **Apache License 2.0**.

See the [LICENSE](LICENSE) file for details.

---

## About WaysNX

WaysNX Business Framework is an open engineering initiative developed and maintained by **WaysNX Technologies Pvt. Ltd.**

The framework incorporates proven practices from enterprise architecture, software engineering, cloud-native development, security engineering, DevOps, and AI-assisted software engineering while remaining vendor-neutral and technology agnostic.

Learn more about the WaysNX engineering ecosystem:

Website: [waysnx.tech](https://waysnx.tech)

---

Apache License 2.0

---

<div align="center">

**Architecture First • Standards Driven • AI Ready • Enterprise Focused**

*Building better software starts with better architecture.*

</div>

