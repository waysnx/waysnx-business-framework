---
documentId: WBF-PLAN-0001
title: Phase 0 Decision Register
status: Active – D1, D2, D6, D8, D9 (ADR-0005), D10 (ADR-0008) approved by Umesh Wani on 2026-11-01; all others await approval
version: 0.2.0
date: 2026-11-01
---

# Phase 0 Decision Register

Approval status values:

| Value | Meaning |
|---|---|
| **Awaiting approval** | Recommendation documented. Nothing dependent on it has been implemented |
| **Awaiting approval – M0 subset implemented under directive** | The Phase 0 directive explicitly required a fix that depends on this decision. Only that minimal, behavior-level fix was implemented, using existing public types where possible. Any new public API surface is marked *provisional* and must be confirmed or renamed on approval |
| **Blocked** | Implementation must not start until approval |

Phase 0.1 (2026-11-01): ADR-0005, ADR-0008, ADR-0009 and ADR-0010 are **Accepted**; ADR-0007 is **Accepted in part** (M0 validation behavior). ADR-0006 remains **Proposed** and Entity work remains **Blocked**. Approval record: §Phase 0.1 approvals at the end of this register.

## Summary

| ID | Topic | Recommendation | ADR | Approval status |
|---|---|---|---|---|
| D1 | Business Function result contract | Standardized result object with 6 statuses | ADR-0009 (new) | **Approved** – Umesh Wani, 2026-11-01 (Phase 0.1 options, see §D1) |
| D2 | Validation contract | Core-defined semantics, fail closed, runtime engines | ADR-0007 (revised) | **Approved (M0 validation behavior)** – Umesh Wani, 2026-11-01. Vocabulary v1 / Laravel Validator adapter remain Proposed (M4) |
| D3 | Entity architecture | Core contracts. Eloquent trait + adapter. Node plain objects + in-memory reference. Prisma only when justified | ADR-0006 (revised) | **Blocked** (M5) |
| D4 | Version format | SemVer strings everywhere | ADR-0006 §Decision 8, CORE_CONTRACTS §10 | Awaiting approval |
| D5 | Identifier format | String IDs. UUID/ULID recommended, not mandated | ADR-0006 §Decision 7 | Awaiting approval |
| D6 | Authorization | Fail closed. Explicit decision required. New `authorization_failure` status | ADR-0010 (new) | **Approved** (semantics and API names) – Umesh Wani, 2026-11-01 |
| D7 | Domain and Capability | Defined in Core. Metadata only. No runtime registries yet | CONCEPTUAL_HIERARCHY.md | Awaiting approval |
| D8 | Missing workflow step sequence (new) | Sequence required in both runtimes | — | **Approved** – Umesh Wani, 2026-11-01 (Phase 0.1 options, see §D8) |
| D9 | Repository ownership | Core = what, runtimes = how. Freeze in-repo copies | ADR-0005 | **Approved** – Umesh Wani, 2026-11-01. R2, R3, R4 authorized subject to removal report and verification |
| D10 | Conformance suite | JSON fixtures in Core `tests/conformance/fixtures`, native adapters | ADR-0008 (revised) | **Approved** – Umesh Wani, 2026-11-01. CI pinned to `conformance-v0.1.0`, activation pending tag; **tag not authorized yet** |
| D11 | Document identifiers (new) | `WBF-SPEC` for normative specs. `WBF-DOC` kept for descriptive docs. Aliases for ADR/PLAN | DOCUMENT_REGISTRY.md | **Approved** – Umesh Wani, 2026-11-01. Renames executed, reversible, documented |
| D12 | Lifecycle handler default (new) | Unspecified `enabled` ⇒ enabled. Non-boolean rejected | — | Awaiting approval – M0 subset implemented under directive |

---

## D1 – Business Function result contract

| | |
|---|---|
| Recommendation | Every execution boundary returns one result with `status ∈ {success, validation_failure, authorization_failure, not_found, business_rule_failure, execution_failure}`. Unexpected exceptions are translated to `execution_failure`. Internal exception idioms stay runtime-specific |
| Rationale | BF-ERR-01/02 (WBF-SPEC-0005 §21). XR-02. Conformance fixtures BF-004 and BF-005 cannot pass in Laravel without categories |
| Affected repositories | Core (spec + schema), Laravel (new result method + categorized exceptions), Node (status values) |
| Breaking changes | **Node: yes.** `rule_failure` → `business_rule_failure`; `failure` splits in three. Laravel: additive if exceptions subclass the documented SPL types |
| Implemented in Phase 0 | Nothing new. Existing Node statuses retained. Laravel unchanged |
| Outstanding questions | Node migration path (dual field vs. major bump at 0.x). Laravel method name. Whether `not_found` covers missing handlers (proposed: no) |

## D2 – Validation contract

| | |
|---|---|
| Recommendation | Core defines rule definitions, input, scope, pass/fail, error and warning structure, unknown-rule behavior, extension points and result format. Laravel uses Laravel Validator (M4). Node keeps its own zero-dependency evaluator |
| Mandatory rules (directive) | Unknown rules fail safely. `required` fails on missing or empty input. Warnings never block alone. Errors are returned consistently. Custom rules remain extensible. Business-specific rules belong to the application |
| Rationale | XR-01 (P0): the same `required` rule passed in Laravel and failed in Node |
| Affected repositories | All three |
| Breaking changes | Behavioral. Unsupported rule types (Laravel default `ruleType: 'custom'`, Node `custom` and unknown types) now fail unless the application implements them |
| Implemented in Phase 0 | Both runtimes: `required` semantics (missing / null / whitespace-only / empty array fail; 0 and false present), fail-closed `WBF.VALIDATION.UNKNOWN_RULE`, `WBF.VALIDATION.INVALID_RULE`, default `WBF.VALIDATION.REQUIRED`. Framework failures are always error severity. Node `ValidationIssue.code` (additive). Node `evaluateCustom()` is now `protected` (extension point) |
| Evidence | Laravel `ValidationFailClosedTest` (17 tests). Node `phase0-remediation.test.ts` › Validation (14). Conformance VAL-001…009 pass in both runtimes |
| Outstanding questions | Full rule vocabulary v1. `illuminate/validation` dependency (needs approval). Node `range`/`format` with missing values. `info` severity (Laravel only). Named-rule registry vs. subclass extension |

## D3 – Entity architecture

| | |
|---|---|
| Recommendation | Core defines Entity, Repository and Transaction contracts. Laravel: Eloquent models + trait + repository adapter. Node: plain objects + repository interface + in-memory reference. Prisma adapter only when justified by a reference application. BaseModel is **not** turned into an Entity abstraction, and the existing Laravel `BaseModel` is not modified |
| Rationale | Spec WBF-SPEC-0003 AP-03 (composition over inheritance). No-ORM-in-Core requirement. LAR-06, LAR-09, NOD-05 |
| Affected repositories | All three (future) |
| Breaking changes | None in Phase 0. Future: deprecation of Laravel `BaseRepository` |
| Implemented in Phase 0 | **Nothing.** Blocked |
| Outstanding questions | Laravel hook source (repository vs. Eloquent events). Eloquent adapter packaging. Tenant context dependency |

## D4 – Version format

| | |
|---|---|
| Recommendation | All semantic versions are strings (`"1.0.0"`), in shared definitions and both runtimes. Entity `revision` (concurrency counter) stays an integer and is not a version |
| Current state (verified) | Laravel definitions: string. Node `ModuleDefinition.version`, `EntityDefinition.version`, `ValidationDefinition.version`: **number**. Node BF/Workflow definitions: string |
| Affected repositories | Core (schema), Node (3 definitions) |
| Breaking changes | **Node: yes** (constructor input and property type) |
| Implemented in Phase 0 | Nothing (public contract change) |
| Outstanding questions | Migration: accept number with deprecation warning for one minor, or change at the next 0.x minor |

## D5 – Identifier format

| | |
|---|---|
| Recommendation | Technology-independent Entity contract uses **string** identifiers. UUID and ULID are recommended, not mandated. Applications choose the generation strategy |
| Current state | Laravel `BaseModel` UUID v4 (artifacts). Node `EntityInterface.getEntityId(): string \| number` |
| Breaking changes | Node `EntityInterface` type narrowing would be breaking. Proposed: keep the interface and stringify in the canonical serializer |
| Implemented in Phase 0 | Nothing |

## D6 – Authorization

| | |
|---|---|
| Recommendation | Fail closed. No default allow. An explicit valid decision is required. Caller-provided values are never proof. Add `authorization_failure` status (via D1) |
| Rationale | LAR-08 (empty hook = allow all). NOD-03 (any truthy caller accepted) |
| Affected repositories | Laravel, Node, Core spec |
| Breaking changes | **Behavioral, intended.** Any execution without a decision point now fails. No method removed |
| Implemented in Phase 0 | Laravel: `AuthorizationDecision`, `AuthorizerInterface`, `AuthorizationException` (extends `\RuntimeException`), `BusinessFunctionAbstract::setAuthorizer()` / `getAuthorizer()`, fail-closed `checkAuthorization()`. Node: `Authorizer`, `AuthorizationDecision`, `AuthorizationDecisions`, `BusinessFunctionRuntime` 4th constructor argument, `setAuthorizer()`, `hasAuthorizer()`. **All names provisional.** Node keeps status `failure` + category `AuthorizationError` until D1 |
| Evidence | Laravel `AuthorizationFailClosedTest` (11). Node NOD-03 tests (13). Conformance AUTH-001…004, BF-003 pass in both runtimes |
| Outstanding questions | API names. Laravel Gate adapter (dependency). `wbf:make` scaffolding of an authorizer stub |

## D7 – Domain and Capability

| | |
|---|---|
| Recommendation | Define concepts and relationships in Core: Module ⊃ Domain ⊃ Capability ⊃ Business Function, with Entities owned by a Domain (see CONCEPTUAL_HIERARCHY.md). Keep them as metadata (BFD fields and identifiers). No runtime registries until a reference application proves the need |
| Rationale | Normative spec WBF-SPEC-0004 OR-01…03 already defines the containment. The descriptive docs contradicted it (PRI-04) |
| Affected repositories | Core (docs corrected in Phase 0). Runtimes: none (Laravel `Models\Domain`, `Models\BusinessCapability` remain as-is) |
| Breaking changes | None |
| Outstanding questions | Whether Entity → Domain ownership should be added to the normative spec (currently informative) |

## D8 – Missing workflow step sequence (new)

| | |
|---|---|
| Recommendation | `sequence` is required for every step. A step without it makes the definition invalid |
| Current state | Node: `WorkflowStep` constructor rejects a missing sequence (conformance WF-003 passes). Laravel: `Registry\WorkflowStep::__construct(..., int $sequence = 0, ...)` silently defaults to 0, so the step runs first (WF-003 fails, recorded known difference). Two Laravel steps that both omit it now fail as a duplicate sequence (Phase 0 fix) |
| Breaking changes | **Laravel: yes.** Removing the default or making it nullable changes a public constructor signature |
| Implemented in Phase 0 | Duplicate/negative-sequence rejection only (both runtimes). No signature change |
| Outstanding questions | Accept a signature change (e.g. `?int $sequence = null` + validation) in the next 0.x minor? |

## D9 – Repository ownership

See ADR-0005 and REPOSITORY_OWNERSHIP.md. Phase 0.1: R2, R3 and R4 executed. `BusinessFunctionAbstractTest.php` (never passed) was retired in Core and its uncovered behavior rewritten in Laravel `tests/Core/` (owner decision, Phase 0.1 review). Evidence: docs/audit/REPOSITORY_CLEANUP_REPORT.md.

## D10 – Conformance suite

See ADR-0008 and CROSS_RUNTIME_CONFORMANCE.md. Phase 0.1: suite manifest `tests/conformance/suite.json` and `fixture.schema.json` added, 33 fixtures, both runtimes 33/33. CI workflows prepared (manual trigger only). Outstanding: Core commit, tag `conformance-v0.1.0`, CI activation.

## D11 – Document identifiers (new)

See docs/00-governance/DOCUMENT_REGISTRY.md.

| | |
|---|---|
| Recommendation | Normative specs move from `WBF-DOC-0000…0005` to `WBF-SPEC-0000…0005`. Descriptive `docs/` keep `WBF-DOC-0000…0140`. ADRs keep `ADR-NNNN` numbers, with alias `WBF-ADR-NNNN`. Plans use `WBF-PLAN-NNNN` |
| Implemented | Files `specification/foundation/WBF-DOC-000N.md` renamed to `WBF-SPEC-000N.md`. Front-matter `id` updated with `legacyId`. Internal normative references updated in all three repositories. Old → new map recorded |
| Reversible | Yes (rename only, uncommitted) |

## D12 – Lifecycle handler default (new)

| | |
|---|---|
| Recommendation | A handler with unspecified `enabled` is enabled (Laravel constructor default `true`, Node `LifecycleHandlerImpl` and `registerCallback()` already behaved this way). Only an explicit `false` disables it. Non-boolean `enabled` or non-number `priority` are rejected at registration |
| Rationale | NOD-07. The Node README example registered handlers without `enabled`, and they silently never fired |
| Breaking changes | Behavioral. Node object-literal handlers without `enabled` now fire. Type widening: `LifecycleHandler.priority` and `enabled` became optional |
| Implemented in Phase 0 | Yes (Node). Conformance LC-001…003 pass in both runtimes |
| **Status (Phase 0.1)** | **APPROVED** – Umesh Wani, 2026-11-01 |

---

## Phase 0.1 approvals (recorded 2026-11-01)

| | |
|---|---|
| Approver | **Umesh Wani** (project owner) |
| Date | 2026-11-01 |
| Approved | D1 (ADR-0009), D2 (ADR-0007, M0 validation behavior), D6 (ADR-0010), D8, D9 (ADR-0005), D10 (ADR-0008), D11 (DOCUMENT_REGISTRY), D12 (Lifecycle Handler Default) |
| Not approved / unchanged | D3 (ADR-0006, **Proposed, Entity work blocked**), D4, D5, D7 (still awaiting approval) |
| Explicit restrictions | No commit, push, merge, tag or package-version change. No Entity, Repository, Transaction or tenant-isolation work. Tag `conformance-v0.1.0` **not** authorized yet |

### D1 – approved options

1. Node result statuses are **replaced directly**: `rule_failure` → `business_rule_failure`; `failure` → `authorization_failure` / `not_found` / `execution_failure`. The migration is documented.
2. Laravel adds `BusinessFunctionRuntime::run(): ExecutionResult`. The existing throwing `execute()` is preserved.
3. Laravel adds **categorized exceptions** that extend the existing base exceptions (`\InvalidArgumentException` / `\RuntimeException`).
4. Business-rule classification: categorized exceptions first. A plain exception thrown from `evaluateBusinessRules()` falls back to `business_rule_failure` by pipeline phase.
5. `not_found` is used only for missing business resources. A missing handler is `execution_failure`.

### D2 – approved

The Phase 0 M0 validation behavior is confirmed:

- `required` semantics
- fail-closed unknown and unimplemented `custom` rules
- `WBF.VALIDATION.*` codes
- warnings never block

### D6 – approved

Fail-closed semantics and the API names are final:

- Laravel: `AuthorizerInterface::authorize()`, `AuthorizationDecision::allow()/deny()`, `AuthorizationException` (reasons `no_authorizer`, `invalid_decision`, `denied`), `setAuthorizer()`
- Node: `Authorizer`, `AuthorizationDecisions.allow()/deny()`, 4th constructor argument, `setAuthorizer()`

### D8 – approved options

1. Sequence input is nullable at the API level. Omitting it raises a clear WBF error.
2. A missing, negative or non-integer sequence is rejected when the step is constructed.
3. Duplicate sequences are rejected when the workflow definition is constructed.
4. The engine keeps its defensive validation.
5. Sequence `0` is valid.

### D9 / ADR-0005 – approved

R2, R3 and R4 are authorized, subject to the removal report, dependency checks and successful verification of the moved tests.

### D10 / ADR-0008 – approved

- CI uses `WBF_CONFORMANCE_REQUIRED=1`, so missing fixtures are a hard configuration failure.
- CI is pinned to `conformance-v0.1.0`.
- CI activation stays **pending** until the Core fixtures are committed and the tag exists.

---

## Phase 0.1 review decisions (recorded 2026-11-01)

| | |
|---|---|
| Approver | **Umesh Wani** |
| Date | 2026-11-01 |

| # | Item | Decision | Implemented |
|---|---|---|---|
| R-1 | `packages/core/tests/BusinessFunctionAbstractTest.php` | Do not move unchanged. Investigate; rewrite in Laravel if it covers important uncovered behavior, otherwise retire with a rationale. No Laravel-specific tests in Core | Retired in Core; definition-validation behavior rewritten in Laravel `tests/Core/` (29 tests). Rationale: REPOSITORY_CLEANUP_REPORT §3.1 |
| R-2a | Plain `\InvalidArgumentException` from the validation phase → `validation_failure` | **Approved** as a controlled phase fallback; categorized exceptions take precedence | Laravel `classify()`; tests in `ExecutionResultTest` |
| R-2b | Plain exception from `checkAuthorization()` → `authorization_failure` | **Rejected.** Only explicit denials or categorized authorization exceptions; anything else `execution_failure` | Fallback removed (Laravel); `ApplyLeave` and `examples/ApplyLeave.php` deny with `AuthorizationException`; tests in both runtimes |
| R-3 | Commit, push, merge, tag, CI activation | **Not authorized.** Prepare a release-readiness review package first | `docs/audit/RELEASE_READINESS_REVIEW.md` |
| R-4 | ADR-0009 amendment for BF-ERR-03 | **Approved** | ADR-0009 A1, WBF-SPEC-0006 RES-09…RES-12, both runtimes, regression tests |
| R-5 | Node dev-dependency advisories | **Investigation only**; no upgrade, version or lockfile change | business-framework-node `docs/DEV_DEPENDENCY_ADVISORY_ASSESSMENT.md` |
| R-6 | Coverage | Keep the 70 % branch threshold; record the actual result and gaps; never present it as passing | Node branches 64.94 %, recorded as failing the threshold |
| R-7 | Boundaries | No Entity / Repository / Transaction / tenant isolation; ADR-0006 stays Proposed; no version changes; no commits or tags without approval | Respected |
