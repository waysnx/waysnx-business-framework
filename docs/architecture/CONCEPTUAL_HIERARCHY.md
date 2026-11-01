---
documentId: WBF-ARCH-HIER-0001
title: Canonical Conceptual Hierarchy – Module, Domain, Capability, Business Function, Entity
status: Draft – containment rules are normative (WBF-SPEC-0004); the Entity placement is a proposal (decision D7)
version: 0.1.0
date: 2026-10-31
---

# Canonical Conceptual Hierarchy

This document resolves finding PRI-04. The normative specification and the descriptive documents appeared to give opposite orderings of Module, Domain and Capability. They were in fact describing two **different relationships**, and neither document said which one it meant.

## 1. Two relationships, not one

| Relationship | Question it answers | Direction | Source |
|---|---|---|---|
| **Containment (ownership)** | "What does X belong to?" Used for identity, governance, dependencies and runtime identifiers | Module ⊃ Domain ⊃ Capability ⊃ Business Function | **Normative**: WBF-SPEC-0004 §5.1–5.4, OR-01…OR-03. WBF-SPEC-0005 BF-ID-04…06 and the identifier format `MODULE.DOMAIN.CAPABILITY.FUNCTION` |
| **Derivation (design flow)** | "In what order are things discovered when modelling a business?" | Strategy → Goals → Capabilities → Domains → Modules | Descriptive: WBF-DOC-0004 "Core Building Blocks", WBF-DOC-0005 "Meta Model" |

The derivation flow starts from capabilities because analysts usually identify *what the business must be able to do* before deciding how to group and package it. That is a modelling sequence. It does not make a Module part of a Capability. Once modelled, each capability belongs to exactly one domain, and each domain to exactly one module. The normative containment rules always govern identity and ownership.

## 2. Canonical containment hierarchy

```text
Module                      e.g. HR              (highest functional boundary; deployable/organizational unit)
└── Domain                  e.g. LEAVE           (cohesive business area; owns its vocabulary and business state)
    └── Business Capability e.g. APPLY           (what the business can do within the domain)
        └── Business Function  e.g. APPLY_LEAVE  (one executable business operation; owns its contracts and rules)

Business Function ID: MODULE.DOMAIN.CAPABILITY.FUNCTION   →   HR.LEAVE.APPLY.APPLY_LEAVE
```

| Rule | Normative source |
|---|---|
| A Module contains one or more Domains | WBF-SPEC-0004 §5.1 |
| A Domain belongs to exactly one Module and is not shared across Modules | §5.2, OR-01 |
| A Business Capability belongs to exactly one Domain | §5.3, OR-02 |
| A Business Function belongs to exactly one Business Capability | §5.4, OR-03 |
| A Business Function owns its request/response contracts, validation rules, business rules, execution logic, published events and metadata | OR-04 |
| A Workflow belongs to exactly one Module and may orchestrate Business Functions of several Modules through published interfaces. It contains no business rules | OR-06, DR-06 |
| Domains do not depend on each other's internals. Cross-domain interaction goes through Business Functions or Services | DR-04 |

## 3. Where Entities belong (proposal – D7, not yet normative)

No normative specification places Business Objects or Entities in the hierarchy. The descriptive documents list them (WBF-DOC-0012, WBF-DOC-0013) without an owner. The following placement **follows from** existing normative rules. It is a **proposal pending approval**:

| Proposed rule | Justification |
|---|---|
| **E1.** Every Business Entity is owned by exactly one Domain | A Domain "represents a cohesive business area" and "maintains clear ownership boundaries" (§5.2). DR-04 forbids depending on another domain's implementation details, and an entity's persistent state is such a detail |
| **E2.** An Entity's state is changed only by Business Functions of its owning Domain. Other Domains change it only by invoking those functions | DR-04 (cross-domain interaction through Business Functions). OR-04 (business rules and execution logic are owned by a Business Function). WBF-SPEC-0005: "Business Rules SHALL NOT modify unrelated business entities" |
| **E3.** Entities are **not** owned by a Capability or a Business Function | One entity (e.g. `LeaveRequest`) is operated on by several capabilities of the same domain (APPLY, APPROVE, CANCEL). Ownership by a single capability would violate E2 for the others |
| **E4.** Entities may reference entities of other Domains **by identifier only** | Consistent with E2 and with ENTITY_ARCHITECTURE §3.5 |

```text
Module HR
└── Domain LEAVE ──owns──► Entity LeaveRequest, Entity LeaveBalance
    ├── Capability APPLY   └── BF HR.LEAVE.APPLY.APPLY_LEAVE      ─┐
    ├── Capability APPROVE └── BF HR.LEAVE.APPROVE.APPROVE_LEAVE  ─┼─ change LeaveRequest state
    └── Capability CANCEL  └── BF HR.LEAVE.CANCEL.CANCEL_LEAVE    ─┘
```

## 4. Runtime representation (decision D7)

Domain and Capability are **metadata** for now:

- They are carried as fields of the Business Function definition (`module`, `domain`, `capability`) and encoded in the function identifier.
- **No runtime registries** for Domains or Capabilities will be introduced until a reference application demonstrates the need.
- Existing Laravel classes `Models\Domain` and `Models\BusinessCapability` are left unchanged. They are runtime-specific artifact models, not registries.
- Node has no Domain/Capability classes and needs none.

## 5. Documents updated for consistency

| Document | Change |
|---|---|
| `docs/02-business-architecture/WBF-DOC-0004-Business-Architecture.md` | Labelled "Core Building Blocks" as the derivation flow. Added the containment hierarchy and a link here |
| `docs/02-business-architecture/WBF-DOC-0005-Meta-Model.md` | Same clarification. Business Function added to the meta model (it was missing). "Capabilities are organized into Domains and Modules" made precise |
| Runtime examples | No change needed. Laravel `ApplyLeave` (`HR.LEAVE.APPLY.APPLY_LEAVE`), the Laravel and Node demos (`AUDIT.VALIDATE.AUDIT.VALIDATE_AUDIT`) and the conformance fixtures already follow `MODULE.DOMAIN.CAPABILITY.FUNCTION` |
