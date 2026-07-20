# WBF-DOC-0013 – Business Entity Specification

---
documentId: WBF-DOC-0013
title: Business Entity Specification
version: 1.0.0
status: Draft
owner: WaysNX Business Framework
category: Business Architecture
lastUpdated: 2026-07-20
---

## Purpose

A Business Entity is a uniquely identifiable, persistent business record representing a real business instance.

## Sections

1. Scope
2. Definition
3. Business Object vs Business Entity
4. Principles
5. Characteristics
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

Defines identity, lifecycle, ownership and governance of persistent business entities.

## Definition

A Business Entity is the persistent realization of a Business Object and has identity, lifecycle and business state.

## Business Object vs Business Entity

| Business Object | Business Entity |
|---|---|
|Concept|Instance|
|Meaning|Record|
|No identity|Unique identifier|
|Reusable|Persistent|

Example:

Business Object: Candidate

Business Entity:
- Candidate ID: CAND-2026-0001
- Status: Selected

## Principles

- Unique identity
- Persistent state
- Business ownership
- Auditability
- Traceability

## Characteristics

- Business identifier
- Lifecycle
- Relationships
- State transitions
- Validation

## Lifecycle

Create → Validate → Active → Update → Archive → Retire

## Metadata

- Entity ID
- Name
- Business Object
- Business Key
- Owner
- Status
- Version
- Relationships

## Ownership

Business Owner, Data Steward, Domain Owner, Enterprise Architect

## Dependency Rules

- Immutable identifiers
- No duplicate business meaning
- Controlled state transitions
- Referential integrity

## Governance

Identity management, versioning, audit logging, retention and data quality.

## Deliverables

- Entity Specification
- Lifecycle Model
- Attribute Dictionary
- Relationship Diagram

## Best Practices

- Stable IDs
- Business terminology
- Complete audit trail
- Explicit lifecycle

## Anti-Patterns

- Mutable identifiers
- Database-first modelling
- Duplicate entities
- Undefined lifecycle

## Examples

HR:
- Candidate
- Employee
- Offer

Sales:
- Customer
- Order
- Invoice

## Related Documents

- WBF-DOC-0012 Business Object Specification
- WBF-DOC-0014 Business Rule Specification
- WBF-DOC-0016 Business Event Specification

## Version History

|Version|Date|Description|
|---|---|---|
|1.0.0|2026-07-20|Initial draft|
