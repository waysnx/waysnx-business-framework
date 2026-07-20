# WBF-DOC-0017 – Command Specification

---
documentId: WBF-DOC-0017
title: Command Specification
version: 1.0.0
status: Draft
owner: WaysNX Business Framework
category: Business Architecture
lastUpdated: 2026-07-20
---

## Purpose

A Command is an explicit request to perform a business action that changes business state.

## Sections

1. Scope
2. Definition
3. Relationship Model
4. Characteristics
5. Principles
6. Lifecycle
7. Metadata
8. Ownership
9. Dependency Rules
10. Governance
11. Deliverables
12. Best Practices
13. Anti-Patterns
14. Examples
15. Related Documents
16. Version History

## Scope

Defines the identification, governance, validation and execution of business commands.

## Definition

A Command expresses business intent and is processed by a Business Service.

## Relationship Model

Requester → Command → Business Service → Business Rule → Business Entity → Business Event

## Characteristics

- Intent driven
- State changing
- Validated
- Traceable
- Versioned

## Principles

- Business focused
- Single responsibility
- Technology independent
- Idempotent where appropriate

## Lifecycle

Create → Validate → Authorize → Execute → Publish Events → Complete

## Metadata

- Command ID
- Name
- Description
- Initiator
- Target Entity
- Preconditions
- Version
- Status

## Ownership

Business Owner, Domain Owner, Enterprise Architect

## Dependency Rules

- Commands invoke Services
- Commands never perform queries
- Validation before execution
- May publish Business Events

## Governance

Versioning, audit logging, authorization review and documentation.

## Deliverables

- Command Specification
- Validation Rules
- Service Mapping
- Event Mapping

## Best Practices

- Use imperative names
- Validate early
- Keep commands atomic

## Anti-Patterns

- Mixed read/write operations
- Hidden side effects
- UI-centric naming

## Examples

- CreateCandidate
- ApproveLeave
- GenerateInvoice

## Related Documents

- WBF-DOC-0010 Business Service Specification
- WBF-DOC-0016 Business Event Specification
- WBF-DOC-0018 Query Specification

## Version History

|Version|Date|Description|
|---|---|---|
|1.0.0|2026-07-20|Initial draft|
