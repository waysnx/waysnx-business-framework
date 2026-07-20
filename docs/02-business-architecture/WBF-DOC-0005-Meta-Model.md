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

Business Strategy
→ Goal
→ Business Capability
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
- Capabilities are organized into Domains and Modules
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
