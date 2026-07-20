---
documentId: WBF-DOC-0008
title: Business Capability Specification
version: 1.0.0
status: Draft
owner: WaysNX Business Framework
category: Business Architecture
lastUpdated: 2026-07-20
---

# WBF-DOC-0008 – Business Capability Specification

## Purpose
A Business Capability defines **what** an organization must be able to do to deliver business value. It is technology-independent and represents a stable unit of business functionality.

## Table of Contents
1. Purpose
2. Scope
3. Definition
4. Relationship to Domains
5. Guiding Principles
6. Characteristics
7. Responsibilities
8. Boundaries
9. Hierarchy
10. Lifecycle
11. Metadata
12. Ownership
13. Dependency Rules
14. Governance
15. Deliverables
16. Best Practices
17. Anti-Patterns
18. Examples
19. Related WBF Documents
20. Version History

---

# 1. Scope

This specification defines Business Capabilities, their responsibilities, ownership, governance, relationships with Domains, Workflows and Services.

# 2. Definition

A **Business Capability** is a stable, business-centric function that describes what the enterprise must be capable of doing, regardless of organizational structure or technology.

# 3. Relationship to Domains

A Domain contains one or more Business Capabilities.

```text
Human Resources
└── Recruitment
    ├── Publish Job Opening
    ├── Evaluate Candidate
    ├── Schedule Interview
    ├── Issue Offer
    └── Onboard Employee
```

# 4. Guiding Principles

- Business-first
- Technology independent
- Outcome driven
- Measurable
- Reusable
- Clearly owned

# 5. Characteristics

| Characteristic | Description |
|---|---|
| Stable | Independent of implementation |
| Valuable | Delivers measurable value |
| Reusable | Supports multiple workflows |
| Cohesive | Focused responsibility |
| Governed | Clear ownership |

# 6. Responsibilities

- Deliver business outcomes
- Support workflows
- Apply business rules
- Enforce policies
- Publish/consume business events
- Expose business services

# 7. Boundaries

Inside:
- Business logic
- Rules
- Policies
- Workflows
- Services

Outside:
- UI
- Infrastructure
- Technical implementation

# 8. Hierarchy

```text
Module
└── Domain
    └── Business Capability
        └── Workflow
            └── Business Service
```

# 9. Lifecycle

Identify → Analyze → Design → Review → Approve → Implement → Operate → Improve → Retire

# 10. Metadata

- Capability ID
- Name
- Description
- Parent Domain
- Business Outcome
- Owner
- KPIs
- Dependencies
- Workflows
- Services

# 11. Ownership

Recommended:
- Capability Owner
- Product Owner
- Business Analyst
- Enterprise Architect
- Technical Lead

# 12. Dependency Rules

- Avoid cyclic dependencies
- Communicate through services/events
- Do not duplicate responsibilities

# 13. Governance

Each Capability should define:
- Versioning
- Change management
- KPI review
- Documentation ownership
- Security responsibilities

# 14. Deliverables

- Capability Specification
- Workflow Specifications
- Service Specifications
- Business Rules
- KPI Definitions
- Event Definitions

# 15. Best Practices

- One primary outcome
- High cohesion
- Clear ownership
- Measurable success
- Stable boundaries

# 16. Anti-Patterns

- Technology-based naming
- Large monolithic capabilities
- Duplicate responsibilities
- Process-centric capabilities

# 17. Examples

Recruitment:
- Publish Job Opening
- Evaluate Candidate
- Conduct Interview
- Onboard Employee

Sales:
- Manage Lead
- Qualify Opportunity
- Generate Quote
- Process Order

# 18. Related WBF Documents

- WBF-DOC-0006 Module Specification
- WBF-DOC-0007 Domain Specification
- WBF-DOC-0009 Workflow Specification
- WBF-DOC-0010 Service Specification

# 19. Version History

| Version | Date | Description |
|---|---|---|
|1.0.0|2026-07-20|Initial draft|
