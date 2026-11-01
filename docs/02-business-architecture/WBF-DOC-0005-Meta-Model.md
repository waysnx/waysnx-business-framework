---
documentId: WBF-DOC-0005
title: Meta Model
version: 1.0.0
status: Draft
owner: WaysNX Business Framework
---

# WBF-DOC-0005 – Meta Model

## Purpose

The Meta Model defines the core business concepts of WBF and the relationships between them. It provides the common vocabulary used throughout the framework.

## Meta Model

> **Clarification (Phase 0, 2026-10-31).** The chain below is a **derivation (design) flow**, not an ownership hierarchy. Ownership is normative: **Module ⊃ Domain ⊃ Business Capability ⊃ Business Function** (WBF-SPEC-0004 §5, OR-01…OR-03). Business Functions are identified as `MODULE.DOMAIN.CAPABILITY.FUNCTION` (WBF-SPEC-0005 §8.3). Business Function, previously missing from this model, sits under Business Capability. See `docs/architecture/CONCEPTUAL_HIERARCHY.md`.

Derivation flow:

Business Strategy
→ Goal
→ Business Capability
→ Business Function
→ Domain
→ Module
→ Workflow
→ Business Service
→ Business Rule / Business Policy
→ Business Object
→ Business Entity
→ Command / Query
→ Decision
→ Business Event
→ Actor
→ Role
→ KPI

## Relationship Principles

- Strategy drives Goals
- Goals are realized through Capabilities
- Each Capability belongs to exactly one Domain; each Domain belongs to exactly one Module (WBF-SPEC-0004 OR-01, OR-02)
- Capabilities are realized by Business Functions; each Business Function belongs to exactly one Capability (OR-03)
- Workflows orchestrate Services
- Services operate on Business Objects and Entities
- Rules and Policies govern execution
- Decisions evaluate Rules and Policies
- Commands change state
- Queries retrieve information
- Events communicate business occurrences
- Actors perform Roles
- KPIs measure Goal achievement

## Artifact Metadata

Each artifact should define:
- Identifier
- Name
- Description
- Owner
- Version
- Status
- Lifecycle
- Relationships

## Benefits

- Consistent terminology
- Enterprise-wide traceability
- Standardized documentation
- Clear ownership
- Technology independence

## Related Documents

- WBF-DOC-0004 Business Architecture
- WBF-DOC-0006 Module Specification

## Version History

|Version|Date|Description|
|---|---|---|
|1.0.0|2026-07-20|Initial draft|
