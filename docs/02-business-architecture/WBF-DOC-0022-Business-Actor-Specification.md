---
documentId: WBF-DOC-0022
title: Actor Specification
version: 1.0.0
status: Draft
owner: WaysNX Business Framework
category: Business Architecture
lastUpdated: 2026-07-20
---

# WBF-DOC-0022 – Actor Specification

## Purpose

An **Actor** is any person, organization, external system, device, or automated agent that interacts with Business Capabilities, Workflows, Services, Commands, Queries, or Events.

## Table of Contents

1. Scope
2. Definition
3. Relationship Model
4. Actor Categories
5. Principles
6. Characteristics
7. Actor Lifecycle
8. Metadata
9. Ownership
10. Dependency Rules
11. Governance
12. Deliverables
13. Best Practices
14. Anti-Patterns
15. Examples
16. Related WBF Documents
17. Version History

## 1. Scope

Defines identification, classification and governance of actors interacting with business processes.

## 2. Definition

An Actor represents an entity that initiates, participates in, or receives the outcome of business interactions.

## 3. Relationship Model

Actor → Command / Query → Workflow → Business Service → Business Event

## 4. Actor Categories

- Employee
- Customer
- Supplier
- Partner
- Administrator
- External System
- Automated Agent
- Device

## 5. Principles

- Business-centric
- Clearly identified
- Independent of implementation
- Traceable
- Governed

## 6. Characteristics

- Unique identity
- Responsibilities
- Interaction boundaries
- Permissions via Roles
- Auditability

## 7. Actor Lifecycle

Identify → Register → Authorize → Participate → Update → Retire

## 8. Metadata

- Actor ID
- Name
- Category
- Description
- Organization
- Contact (optional)
- Related Roles
- Status
- Version

## 9. Ownership

- Business Owner
- Domain Owner
- Identity Administrator

## 10. Dependency Rules

- Actors perform Roles
- Actors initiate Commands and Queries
- Actors may publish or consume Business Events
- Permissions are assigned through Roles, not directly to actors

## 11. Governance

- Actor registry
- Identity management
- Access reviews
- Audit logging

## 12. Deliverables

- Actor Specification
- Actor Catalog
- Interaction Matrix
- Responsibility Mapping

## 13. Best Practices

- Separate actors from roles
- Use business terminology
- Maintain clear ownership
- Review access regularly

## 14. Anti-Patterns

- Assigning permissions directly
- Mixing actors and organizations
- Undefined responsibilities

## 15. Examples

- HR Manager
- Employee
- Customer
- Payroll System
- Payment Gateway
- AI Assistant

## 16. Related WBF Documents

- WBF-DOC-0017 Command Specification
- WBF-DOC-0018 Query Specification
- WBF-DOC-0023 Role Specification

## 17. Version History

|Version|Date|Description|
|---|---|---|
|1.0.0|2026-07-20|Initial draft|
