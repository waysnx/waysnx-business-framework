---
documentId: WBF-ARCH-CC-0001
title: WBF Core Contract Consolidation (WBF-002)
status: Proposed – input to future normative specifications
version: 0.1.0
date: 2026-10-31
---

# WBF Core Contract Consolidation

This document consolidates the technology-independent contracts that the two runtimes already implement, or that the specification already requires. It is a **proposal**. Once approved, each section becomes a normative specification in `specification/`, with requirement IDs as defined in spec WBF-SPEC-0000 §9.

Every rule below is traceable to one of three sources:

- **[Spec]**: an existing normative requirement, usually `specification/foundation/WBF-SPEC-0005` (BF) or `WBF-SPEC-0004` (architecture).
- **[Both]**: behavior both runtimes already exhibit.
- **[Proposed]**: a new rule, introduced to resolve a finding in [REPOSITORY_AUDIT.md](../audit/REPOSITORY_AUDIT.md).

No feature is proposed only because other frameworks have it.

Each contract states:

- **Mandatory**: MUST / SHALL behavior.
- **Optional**: MAY behavior.
- **Extension points**.
- **Responsibilities**, split three ways:
  - **F**: framework (spec + both runtimes)
  - **A**: application
  - **R**: runtime-specific

---

## 1. Business Function

### 1.1 Definition (BFD)

| Field | Mandatory | Source | Laravel today | Node today |
|---|---|---|---|---|
| `functionId` (globally unique) | MUST | [Spec] BF-ID-01 | model ✅ / registry `id` ✅ | `id` ✅ |
| `name` | MUST | BF-ID-02 | ✅ | ✅ |
| `version` (SemVer string) | MUST | BF-ID-03, BF-VER-01 | `'1.0.0'` ✅ | `'1.0.0'` ✅ |
| `module`, `domain`, `capability` | MUST | BF-ID-04…06, OR-03 | model ✅ / registry ✗ | ✅ |
| `lifecycleStatus` ∈ {Draft, Defined, Approved, Implemented, Released, Deprecated, Retired} | MUST (value set RECOMMENDED by spec) | BF-ID-07, §8.4 | model ✅ | defaults `Draft`, not validated |
| `description`, `businessOwner`, `technicalOwner` | MUST | BF-META-01…03 | model ✅ | optional |
| `tags[]`, `classification`, `visibility`, `dependencies[]`, `eventsPublished[]`, `eventsConsumed[]` | MUST be present (may be empty) | BF-META-04…10 | model ✅ | ✅ (defaults) |
| `contract.request`, `contract.response`, `contract.errors` | MUST | BF-CON, BF-REQ, BF-RESP | model ✅ (non-empty check) | optional |
| `processing.validation`, `processing.authorization`, `processing.businessRules` | MAY | §6 | ✅ (descriptive) | ✅ (descriptive) |

- **Mandatory [Proposed]:**
  - A BFD MUST be serializable to the canonical JSON form defined by `schemas/business-function.schema.json`, which is to be authored.
  - Seed the schema from `business-framework-laravel/tests/Fixtures/BFD/ApplyLeave.canonical.json` (sections `identity / metadata / contract / processing / operational`). Replace its Laravel rule strings with canonical rule objects (VALIDATION_ARCHITECTURE §5).
- **Optional:**
  - Runtimes MAY use any internal representation (class, value object, decorator). Spec §6: "Implementations MAY organize these concepts differently".
- **Responsibilities:**
  - F: schema and mandatory-field validation at registration.
  - A: authoring BFDs.
  - R: mapping BFDs to native classes or handlers.

### 1.2 Execution pipeline

**Mandatory [Spec §13, BF-VAL]:**

1. **Contract validation**: request is checked against the request contract. No state change, no events.
2. **Authorization**.
3. **Business rule evaluation**.
4. **Business execution**.
5. **State changes**.
6. **Event publication**.
7. **Response**.

A failure at any step terminates the pipeline. Steps 5 and 6 MUST NOT happen after a failure.

| Step | Laravel | Node | Gap |
|---|---|---|---|
| Contract validation | empty hook | object-type check | Neither enforces `contract.request` |
| Authorization | empty hook | caller presence | §4 Authorizer port |
| Rules | hook | ValidationFramework (scope = moduleId) | §5 |
| Execution | abstract `executeBusiness` | registered handler | different by design ✅ |
| Events | empty hook | returns declared list | §6 Publisher port |
| Response | `transformToResponse` | object check | Neither enforces `contract.response` |

- **Extension points:**
  - Laravel: every step is an overridable hook.
  - Node: the handler, plus [Proposed] injected ports (validator, authorizer, publisher).
  - Both are acceptable. The ports in §4–§6 are the **normative** extension points. Hooks are runtime idiom.

### 1.3 Result

> **Promoted (Phase 0.1).** The status set and classification are now normative in [WBF-SPEC-0006](../../specification/runtime/WBF-SPEC-0006.md) (D1, ADR-0009, approved 2026-11-01). The shape below remains the target; `errors[]`/`issues[]` collections are deferred to M4.

**Mandatory [WBF-SPEC-0006; grounded in BF-ERR-02 "Errors SHALL be distinguishable from successful Responses"]:** every execution yields exactly one outcome with this canonical shape:

```json
{
  "executionId": "string",
  "functionId": "HR.LEAVE.APPLY.APPLY_LEAVE",
  "functionVersion": "1.0.0",
  "status": "success | validation_failure | authorization_failure | not_found | business_rule_failure | execution_failure",
  "response": {},
  "error": { "category": "ValidationError", "code": "string", "message": "string", "details": [] },
  "issues": [ { "severity": "error|warning|info", "code": "", "path": "", "message": "", "ruleId": "" } ],
  "events": [ { "type": "LeaveRequested" } ],
  "correlationId": "string",
  "durationMs": 0
}
```

- Node already returns `BusinessFunctionResult` with most of these fields, under the names `status`, `errorCategory`, `eventsPublished` and `duration`.
- Laravel `execute()` returns a raw array and throws. Since Phase 0.1 `BusinessFunctionRuntime::run()` returns `ExecutionResult` (`status`, `errorCategory`, `errorCode`, `errorMessage`, `failedPhase`, `events`, `durationMs`).
- **Optional:** a runtime MAY expose an exception-throwing API, as Laravel does today. It MUST also expose the result form, and the two MUST map 1:1.
  - [Implemented, Phase 0.1] Laravel: `BusinessFunctionRuntime::run(): ExecutionResult`; `execute()` unchanged.
- [Implemented, Phase 0.1] Node statuses replaced directly: `rule_failure` → `business_rule_failure`, `failure` → `authorization_failure` / `not_found` / `execution_failure`. Migration: WBF-SPEC-0006 §6.

### 1.4 Error model

**Mandatory [Spec §21]:** these eight categories, using the canonical identifiers proposed below.

| Spec category | Canonical id [Proposed] | Laravel today | Node today |
|---|---|---|---|
| Validation Error | `ValidationError` | `ValidationFailedException` (extends `InvalidArgumentException`); plain `InvalidArgumentException` in the validation phase | `ValidationError`, `RequestValidationError` (category `'RequestValidationError'`) |
| Authorization Error | `AuthorizationError` | `AuthorizationException` (extends `RuntimeException`) | `AuthorizationError` |
| Business Rule Violation | `BusinessRuleViolation` | `BusinessRuleViolationException` (extends `RuntimeException`); plain exception in the business-rules phase | `BusinessRuleViolation` |
| Resource Not Found | `ResourceNotFound` | `ResourceNotFoundException` (business resource). Registry `EntityNotFoundException` is a framework error | `ResourceNotFound`, `NotFoundError` (category `RegistryError`) |
| Conflict | `Conflict` | ✗ | `ConflictError` (category `'Conflict'`) |
| Concurrency Error | `ConcurrencyError` | ✗ | ✗ |
| External Dependency Failure | `ExternalDependencyFailure` | ✗ | ✗ |
| Internal System Error | `InternalSystemError` | any other `Throwable` | default |

- **Mandatory [BF-ERR-03]:** the public `message` MUST NOT include stack traces, class names or inner exception messages. Diagnostics go to a separate, non-serialized field. *Implemented for `execution_failure` in both runtimes (ADR-0009 amendment A1, WBF-SPEC-0006 RES-09…RES-12): fixed safe message + reference, full detail to a server-side error reporter.*
- **Framework errors** (registry duplicate or not-found, configuration, lifecycle handler failure) are *not* business errors. They are reported as `InternalSystemError` at the BF boundary.
- **Responsibilities:**
  - F: category set and mapping.
  - R: exception or class hierarchy (Laravel exceptions, Node `WBFError`). Both are fine, provided each maps to a category.
  - A: domain-specific `code` values.

---

## 2. Module

| Aspect | Mandatory | Optional | Source |
|---|---|---|---|
| Identity: `id` unique, `name` unique | MUST | — | [Both] registries reject duplicate id and name |
| `version` | MUST, SemVer string | — | [Proposed]. Laravel `string '1.0.0'`, Node `number 1` (XR-03). **Breaking for Node, needs approval** |
| `displayName`, `description` | `displayName` MUST | `description` MAY | [Both] |
| `domains[]` | MUST (≥1 per OR-01 / spec 0004 §5.1 "A Module SHALL contain one or more Domains") | — | [Spec]. Laravel ✗, Node ✅ |
| `dependencies[]` (module ids) | MAY | — | [Both] |
| Circular dependencies | MUST be rejected | — | [Spec] DR-03. Neither runtime checks. Laravel has `dependencies()`, Node has `withDependencies()` |
| `status` lifecycle | MAY | — | docs 0006 (descriptive). Node default `'Draft'` is outside its own documented list |
| `namespace`, `priority`, `author` | — | MAY (runtime-specific) | Laravel only |
| Registration | Explicit, programmatic | — | [Both]. Laravel config `definitions.*` and `auto_discovery` are inert |
| Discovery | MAY | — | Not implemented. Runtime-specific (Laravel package discovery, Node module scan) |
| Lifecycle (boot/shutdown) | — | MAY | Not implemented. Not proposed now |

---

## 3. Execution context

**Mandatory [Proposed, resolves LAR-22, NOD-19; grounded in spec 0002 glossary "Execution Context may include security information, tenant information…"]:**

```json
{
  "executionId": "string (unique per execution)",
  "correlationId": "string (propagated across BF → workflow → events)",
  "causationId": "string | null",
  "actor": { "id": "string", "type": "user|service|system", "roles": [], "attributes": {} },
  "tenant": { "id": "string" } ,
  "locale": "string | null",
  "requestedAt": "RFC 3339 timestamp",
  "metadata": {}
}
```

- **Mandatory:** `executionId`, `correlationId`, `requestedAt`.
  - `actor` MUST be present when authorization requirements are declared.
  - `tenant` MUST be present when the application declares multi-tenancy.
- **Mandatory:**
  - The context is immutable for the duration of an execution [Both: Node `Object.freeze`, Laravel readonly-style value objects].
  - Child executions (workflow steps) inherit `correlationId` and `tenant` (fixes NOD-09).
- **Responsibilities:**
  - A (host app): resolves `actor` and `tenant` from its own authentication or session. WBF does not authenticate.
  - R: adapters, e.g. Laravel `Auth::user()` → `actor`.
  - F: propagation and immutability.
- **Security boundary:** the framework MUST NOT accept `actor` or `tenant` from request payload data. They come from the host only.

---

## 4. Security context and authorization

- **Mandatory [Spec BF-SEC, §13; Proposed port]:**
  - Authorization is evaluated through an `Authorizer` port: `authorize(context, functionDefinition, request) → allow | deny(reason)`.
  - If `processing.authorization` declares requirements and **no** authorizer is configured, execution MUST fail with `AuthorizationError` (**fail closed**; fixes LAR-08, NOD-03).
- **Optional:** functions with no declared requirements MAY run without an authorizer.
- **Responsibilities:**
  - A: policies, roles, permissions.
  - R: adapters (Laravel Gate/Policy, Node callback).
  - F: invocation point, fail-closed default, error category.

## 5. Validation

See [VALIDATION_ARCHITECTURE.md](VALIDATION_ARCHITECTURE.md) and ADR-0007.

## 6. Events and lifecycle

| Kind | Purpose | Contract |
|---|---|---|
| **Business events** (spec docs 0016) | Domain facts (`LeaveRequested`) | [Proposed] Envelope `{type, version, occurredAt, correlationId, causationId, tenantId, source(functionId), payload}`. Published through a `EventPublisher` port **after** successful execution. Not published on failure (BF pipeline). Current runtimes only *declare* events |
| **Lifecycle events** (framework hooks) | Observability and extension | [Proposed] Canonical names below. Handlers are observational: a handler failure MUST NOT change the business outcome. Today it aborts execution in both runtimes |

Canonical lifecycle event names [Proposed; union of existing names]:

| Scope | Events | Laravel today | Node today |
|---|---|---|---|
| Business Function | `beforeFunction`, `functionCompleted`, `functionFailed`, `afterFunction` | none | first three |
| Validation | `beforeValidation`, `validationCompleted`, `validationFailed`, `afterValidation` | all four | defined, not dispatched |
| Workflow | `beforeWorkflow`, `workflowCompleted`, `workflowFailed`, `afterWorkflow` | all four | all four |
| Step | `beforeStep`, `stepCompleted`, `stepFailed`, `afterStep` | first three | four (afterStep only on success) |

`after*` events MUST fire on both success and failure.

## 7. Audit

- **Mandatory [Proposed]:** when a BFD declares `operational.audit.enabled = true`, the runtime MUST emit an audit record through an `AuditSink` port. The record contains `{executionId, correlationId, functionId, functionVersion, actor.id, tenant.id, status, occurredAt}`.
- Request and response payloads are excluded by default.
- **Optional:** payload capture MAY be enabled per field, with redaction.
- **Responsibilities:**
  - A: storage, retention (docs 0047).
  - R: adapter (Laravel log channel, Node callback).
  - F: record shape and emission point.

Entity-level audit fields (`createdBy`, `createdAt`, …) are covered in ENTITY_ARCHITECTURE.md.

## 8. Tenant context

- **Mandatory [Proposed]:**
  - When the application declares multi-tenancy, every execution context carries `tenant.id`.
  - Repository adapters MUST scope reads and writes by tenant.
  - Registries (definitions) are tenant-agnostic.
- **Responsibilities:**
  - A: tenant resolution.
  - R: query scoping (Eloquent global scope, Prisma middleware).
  - F: propagation.

## 9. Workflow

| Aspect | Mandatory | Source |
|---|---|---|
| Ownership: one Module | MUST | [Spec] OR-06 |
| Steps reference BFs by `functionId`. Workflows MUST NOT contain business rules | MUST | [Spec] DR-06 |
| Step `id` unique within workflow. `sequence` required, integer ≥ 0, unique | MUST | Normative in [WBF-SPEC-0007](../../specification/runtime/WBF-SPEC-0007.md) (D8). Implemented in both runtimes |
| Execution order: ascending `sequence`, independent of declaration order | MUST | [Proposed]. Fixes LAR-02, NOD-02 |
| Each step executes its BF through the BF pipeline with an inherited context | MUST | [Proposed]. Fixes LAR-03, NOD-09 |
| Failure: fail-fast. Result `failed`, with `completedSteps`, `failedSteps` and the failing step's error category | MUST | [Both] fail-fast |
| Input mapping from workflow context to step request | Extension point | [Both] `executeBusinessFunction` hook |
| Transitions, conditions, retry, timeout, compensation, parallelism | MAY (future spec) | Fields exist in Laravel `WorkflowStep` and are unused. Not proposed now |

## 10. Serialization and versioning

- **Mandatory [Proposed]:**
  - Canonical interchange form is JSON with **camelCase** keys (matches the BFD fixture, Node, and Laravel definitions).
  - Timestamps are RFC 3339 UTC strings.
  - Identifiers are strings.
  - Laravel `BaseModel::toArray()` snake_case output is a runtime-specific representation. A canonical serializer is required alongside it (no removal).
- **Mandatory [Spec BF-VER]:**
  - Definition `version` is SemVer.
  - Entity *revision* (optimistic concurrency counter) is an integer and is a different concept (ENTITY_ARCHITECTURE §3).
- **Mandatory [Proposed]:** deserialization of definitions MUST validate against the schema and MUST NOT instantiate classes named in the payload.

## 11. Responsibility summary

| Concern | Framework (spec + runtimes) | Application | Runtime-specific |
|---|---|---|---|
| BFD shape, mandatory fields | ✔ | authoring | native mapping |
| Pipeline order, failure semantics | ✔ | — | hooks vs ports |
| Request/response contract validation | ✔ semantics | contracts | engine (Laravel Validator, Node evaluator) |
| Business rules | invocation, result | rule logic | — |
| Authorization | invocation, fail-closed | policies | Gate / callback adapter |
| Tenant / actor resolution | propagation | resolution | auth adapter |
| Events | envelope, timing | event types | bus adapter |
| Persistence | repository port semantics | schema, migrations | Eloquent / Prisma adapters |
| Transactions | boundary semantics | — | DB transaction adapter |
| Audit | record shape | storage | sink adapter |
| Logging | no sensitive payloads by default | log config | logger adapter |
