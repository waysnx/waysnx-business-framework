---
documentId: WBF-ARCH-CAP-0001
title: Implementation Capability Matrix – Laravel vs Node.js (WBF-005)
status: Draft – baseline from code inspection; Phase 0 / 0.1 rows backed by conformance suite 0.1.0
version: 0.2.0
date: 2026-11-01
baseline: laravel@8c20977 (tag v0.1.0), node@d2866ed (package 0.4.0)
---

# Implementation Capability Matrix

> **Baseline note.** In the audit baseline below, no row is backed by a shared conformance test. Only the rows in the Phase 0 and Phase 0.1 update tables are. Baseline classifications come from code inspection and from the targeted probes listed in [REPOSITORY_AUDIT.md §0](../audit/REPOSITORY_AUDIT.md#0-audit-baseline). Once the conformance suite exists, this matrix should be generated from its results (REPOSITORY_OWNERSHIP §S4.4).

## Phase 0 update (2026-10-31)

The matrix below is the **audit baseline**. Phase 0 changed the rows in this table. Where a conformance fixture exists, the new classification is backed by it (suite 0.1.0, see CROSS_RUNTIME_CONFORMANCE §7). Rows not listed are unchanged and still based on code inspection only.

| Row | Capability | Was | Now | Evidence |
|---|---|---|---|---|
| 21 | Invalid request classification | Partially equivalent (NOD-01 bug) | **Equivalent for null/non-object input** (both `validation_failure` after normalization) | BF-002 passes in both runtimes |
| 22 | `null` request | Partially equivalent | **Equivalent outcome** (Laravel: `TypeError` at the typed boundary; Node: structured result). Representation differs, pending ADR-0009 | BF-002 |
| 25 | Built-in rule evaluation | **P0 divergence** | **Equivalent** for `required`, fail-closed unknown/custom rules, warnings non-blocking, multiple errors | VAL-001…009 pass in both runtimes |
| 26 | Severities | Partially equivalent | Unchanged (`info` exists only in Laravel; not fixture-covered) | — |
| 27 | Warnings surfaced | Partially equivalent | **Equivalent at ValidationFramework level.** Node BF results still drop warnings (NOD-04, M4) | VAL-005/006 |
| 29 | Step ordering | Divergent (both defective) | **Equivalent** for explicit, unique sequences (declaration order ignored, duplicates rejected). **Different** for a missing sequence (Laravel defaults to 0, D8) | WF-001/002/004/006 pass in both runtimes. WF-003 fails in Laravel |
| 30 | Workflow invokes BF | Partially equivalent (Laravel never invoked) | **Equivalent** (both invoke registered functions through the BF pipeline; unregistered functions fail the step) | WF-005, WF-007 |
| 33 | Lifecycle handler enablement | Partially equivalent (NOD-07) | **Equivalent** (unspecified ⇒ enabled; `false` ⇒ disabled) | LC-001…003 |
| 40 | Error category coverage | Partially equivalent | Unchanged. **Behavioral difference confirmed:** Laravel cannot distinguish business-rule and execution failures | BF-004, BF-005 fail in Laravel |
| 46 | Authorization | Partially equivalent, fail-open | **Equivalent:** fail closed, explicit decision required, reasons `no_authorizer` / `invalid_decision` / `denied`. API names provisional (ADR-0010) | AUTH-001…004, BF-003 |
| 54 | Test suite status | Laravel 4 errors. Node branch coverage 60.99 % | Laravel 911 tests pass (3 incomplete = conformance known differences). Node 244 tests pass, branch coverage 63.96 % (still below 70 %) | Phase 0 completion report §7 |

## Phase 0.1 update (2026-11-01)

Suite 0.1.0 now has 33 fixtures. Both runtimes pass all of them and their normalized outcomes are identical (CROSS_RUNTIME_CONFORMANCE §7). "Equivalent" below means **equivalent for the fixture-covered cases only**. It is not a general parity claim. Rows not listed keep their Phase 0 or baseline classification.

| Row | Capability | Phase 0 | Phase 0.1 | Evidence |
|---|---|---|---|---|
| 21/22 | Invalid / `null` request | Equivalent outcome, representation differs | **Equivalent** (both return `validation_failure` natively; Laravel via `run()`) | BF-002 |
| 29 | Step ordering | Different for missing sequence | **Equivalent** for missing, negative, non-integer, duplicate and zero sequences (WBF-SPEC-0007) | WF-001…010 |
| 40 | Error category coverage | Laravel could not distinguish rule vs execution failure | **Equivalent** for the six D1 statuses (WBF-SPEC-0006). Categories beyond these (Conflict, Concurrency, ExternalDependency) remain uncovered | BF-001…007, AUTH-001…004 |
| 46 | Authorization | Equivalent, API provisional | **Equivalent**, API final (ADR-0010 accepted) | AUTH-001…004, BF-003 |
| 54 | Test suite status | Laravel 911 (3 incomplete), Node 244, branches 63.96 % | Laravel **1016** tests, 0 incomplete. Node **283** tests, branches **64.94 %** (below the unchanged 70 % threshold, i.e. the coverage gate fails) | PHASE_0_1_COMPLETION_REPORT §5 |

## Classification

| Class | Meaning |
|---|---|
| **Equivalent** | Same observable business behavior for the same inputs |
| **Partially equivalent** | Same intent. Observable behavior differs in some cases |
| **Different by design** | Different mechanism, acceptable runtime idiom, same business outcome possible |
| **Missing** | Absent in one or both runtimes |
| **N/A** | Not applicable to that runtime |

Verdict on each difference:

- **Idiom**: an intentional runtime-specific implementation detail. Acceptable.
- **Violation**: violates an existing normative spec requirement.
- **Unspecified**: diverges, but no spec requirement exists yet. Needs a spec decision.

## Matrix

| # | Area | Capability | Laravel | Node | Class | Verdict | Ref |
|---|---|---|---|---|---|---|---|
| 1 | Entity | Entity interface | `Types\EntityInterface` | `EntityInterface` | Partially equivalent | Unspecified. `getEntityId` type differs | ENTITY §1 |
| 2 | Entity | Entity base implementation | `BaseModelAbstract` (artifact base, not business entity) | none | Missing (both, for business entities) | Unspecified | LAR-09, NOD-05 |
| 3 | Entity | Audit fields | getters/setters on base, incl. delete | interface without delete fields | Partially equivalent | Unspecified | ENTITY §1 |
| 4 | Entity | Soft delete, timestamps, revision behavior | fields only | none | Missing | Unspecified | ENTITY §3 |
| 5 | Entity | Repository / persistence | `BaseRepository` (non-persistent) | none | Missing | Unspecified | LAR-06 |
| 6 | Entity | Transactions | no-op `transaction()` | none | Missing | Unspecified | LAR-06 |
| 7 | Module | Module definition | `namespace`, `author`, `category`, `priority`, `version:string` | `domains[]`, `status`, `businessOwner`, `version:number` | Partially equivalent | **Violation** (Laravel lacks domains: spec 0004 §5.1 "A Module SHALL contain one or more Domains") + Unspecified (version type) | XR-03 |
| 8 | Module | Domain / Capability artifacts | `Models\Domain`, `Models\BusinessCapability` (no registries) | strings on BF definition | Different by design | Unspecified (open question) | Audit §M.2 |
| 9 | Module | Circular dependency rejection | no | no | Missing | **Violation** (DR-03) | CORE §2 |
| 10 | Registration | Register / duplicate id / duplicate name | ✔ per-type exceptions | ✔ `DuplicateError` | Equivalent | Idiom (exception types) | — |
| 11 | Registration | Not-found behavior | throws `*NotFoundException` | throws `NotFoundError` | Equivalent | Idiom | — |
| 12 | Registration | Aliases | ✔ | ✔ | Equivalent | — | — |
| 13 | Registration | Auto-registration from config | config keys exist, unused | none | Missing | Unspecified | LAR-12 |
| 14 | Discovery | Specialized queries | `findByNamespace`, `findByCategory`, `requiringPermission`, `findByTrigger`… | `findByDomain`, `active`, `withDependencies`, `findByEntity`… | Partially equivalent | Idiom (query sets differ) | — |
| 15 | Discovery | Package / module discovery | Laravel package auto-discovery (provider only) | none | Different by design | Idiom | — |
| 16 | Business Function | Definition fields | Model: full spec 0005 identity and metadata. Registry definition: lacks domain, capability, lifecycle, owners | Requires domain and capability. Owners optional | Partially equivalent | **Violation** (BF-ID-05…07, BF-META-02/03 in Laravel registry definition and Node optional owners) | LAR-05, XR-03 |
| 17 | Business Function | Pipeline order | validate → authorize → rules → execute → publish → transform | same | Equivalent | — | spec 0005 §13 |
| 18 | Business Function | Logic attachment | subclass `executeBusiness()` | `registerHandler(fn)` | Different by design | Idiom | — |
| 19 | Business Function | Request contract enforcement | none (hook) | object-type check only | Missing | **Violation** (BF-REQ, BF-VAL) | CORE §1.2 |
| 20 | Business Function | Outcome model | raw array + exceptions | `BusinessFunctionResult` | Partially equivalent | **Violation** (BF-ERR-01/02: categorized outcome). Laravel has no category | XR-02 |
| 21 | Business Function | Invalid request classification | `InvalidArgumentException` | `failure` / `RequestValidationError` (bug) instead of `validation_failure` | Partially equivalent | **Violation** (NOD-01) | NOD-01 |
| 22 | Business Function | `null` request | `TypeError` (array type hint) | promise rejects | Partially equivalent | Unspecified | NOD-06 |
| 23 | Execution context | Correlation ID | not in BF pipeline | ✔ (not propagated to workflow steps) | Partially equivalent | Unspecified | NOD-09, LAR-22 |
| 24 | Execution context | Actor | `mixed $caller` | `caller?: string\|number` | Partially equivalent | Unspecified | CORE §3 |
| 25 | Validation | Built-in rule evaluation | **none; default pass** | required, range, format; `custom` passes | Partially equivalent → **divergent outcome** | **P0 divergence** (XR-01). Unspecified in spec | LAR-01, NOD-04 |
| 26 | Validation | Severities | error, warning, info | error, warning | Partially equivalent | Unspecified | VALIDATION §4 |
| 27 | Validation | Warnings surfaced to caller | in `ValidationResult` | dropped by BF runtime | Partially equivalent | Unspecified | NOD-04 |
| 28 | Validation | Validation lookup | by id | by scope (= moduleId in BF runtime) | Different by design | Unspecified | VALIDATION §4.10 |
| 29 | Workflow | Step ordering | first step by min sequence, then **buggy** next-step (skips) | **array order**, sequence ignored | Partially equivalent → divergent outcome | Unspecified (both defective) | LAR-02, NOD-02 |
| 30 | Workflow | Invokes BF for each step | **no** (empty extension point) | yes (registered runtimes) | Partially equivalent | **Violation** in spirit of DR-06 / spec 0004. Needs explicit spec | LAR-03 |
| 31 | Workflow | Fail-fast | ✔ | ✔ | Equivalent | — | — |
| 32 | Workflow | Transitions / conditions / retry | fields unused | not modeled | Missing | Unspecified | CORE §9 |
| 33 | Lifecycle | Handler registration and dispatch | ✔ | ✔ (handlers need explicit `enabled: true`) | Partially equivalent | Unspecified | NOD-07 |
| 34 | Lifecycle | BF events | none | before / completed / failed | Partially equivalent | Unspecified | CORE §6 |
| 35 | Lifecycle | Workflow events | 7 (no `afterStep`) | 8 (`afterStep` success only) | Partially equivalent | Unspecified | CORE §6 |
| 36 | Lifecycle | Validation events | 4 dispatched | defined, not dispatched | Partially equivalent | Unspecified | NOD-08 |
| 37 | Lifecycle | Handler failure | aborts execution (`LifecycleException`) | aborts dispatch | Equivalent | Unspecified (CORE §6 proposes observational) | — |
| 38 | Events | Business event publication | declared only | declared only | Equivalent (both stubs) | Missing per spec 0005 §13 | CORE §6 |
| 39 | Errors | Hierarchy | per-type exceptions, no root, odd parents | `WBFError` root + `category` | Different by design | Idiom for hierarchy. **Violation** for missing categories (§21) | XR-05, LAR-14 |
| 40 | Errors | Spec categories coverage | 0 / 8 explicit | 5 / 8 (no Concurrency, ExternalDependency, InternalSystemError class) | Partially equivalent | **Violation** (§21) | CORE §1.4 |
| 41 | Metadata | Definition metadata bag, tags | ✔ | ✔ | Equivalent | — | — |
| 42 | Metadata | Instance metadata on artifacts | ✔ (`BaseModelAbstract`) | N/A | Different by design | Idiom | — |
| 43 | Serialization | Definitions | `toArray`/`toJson`, camelCase | `toObject`/`toJson`, camelCase | Equivalent | Idiom (method name) | — |
| 44 | Serialization | Entity/artifact | `BaseModel::toArray` snake_case | interface only | Partially equivalent | Unspecified | LAR-21 |
| 45 | Serialization | Canonical BFD JSON | fixture + test | none | Missing (Node) | Unspecified (spec 0005 §25 defers format) | PRI-07 |
| 46 | Security | Authorization | empty hook (allow) | caller presence only | Partially equivalent | **Violation** of BF-SEC intent (fail-open) | LAR-08, NOD-03 |
| 47 | Security | Mass-assignment protection | none in `BaseRepository` | N/A | Missing | Unspecified | LAR-06 |
| 48 | Tenant context | Tenant propagation / isolation | none | none | Missing | Unspecified | CORE §8 |
| 49 | CLI | doctor / list / show / make / register / createdemo | ✔ (Artisan `wbf:*`) | ✔ (`wbf *`) | Partially equivalent | Idiom. Node list/show/register operate on empty registries | NOD-10 |
| 50 | Code generation | Writes files | ✔ (BF and workflow full; entity, module, validation minimal) | prints to stdout | Different by design | Idiom | — |
| 51 | Code generation | Input sanitization | none | none | Missing | Security P2 | LAR-15 |
| 52 | Documentation | README accuracy | several inaccuracies | several inaccuracies, stale paths | Partially equivalent | — | LAR-17, NOD-12 |
| 53 | Documentation | Parity claim | — (links to Node) | "identical to the Laravel implementation" | — | **Unsupported claim** | XR-07 |
| 54 | Quality | Test suite status | 841 tests, 4 errors (date-dependent) | 164 passed, branch coverage 60.99 % < 70 % threshold | — | — | LAR-04, NOD-14 |

## Summary counts (54 rows)

| Class | Count | Rows |
|---|---|---|
| Equivalent | 9 | 10, 11, 12, 17, 31, 37, 38, 41, 43 |
| Partially equivalent | 24 | 1, 3, 7, 14, 16, 20–27, 29, 30, 33–36, 40, 44, 46, 49, 52 |
| Different by design | 7 | 8, 15, 18, 28, 39, 42, 50 |
| Missing (one or both) | 12 | 2, 4, 5, 6, 9, 13, 19, 32, 45, 47, 48, 51 |
| Unclassified observations | 2 | 53, 54 |

Two of the "Equivalent" rows are equivalent only because both runtimes are stubs:
- Row 38: events are declared only.
- Row 37: a handler failure aborts execution in both runtimes.

**Spec violations identified:** rows 7, 9, 16, 19, 20, 21, 30, 39, 40, 46.

**P0 outcome divergences:** rows 25 and 29.
