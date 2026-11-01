---
documentId: WBF-ARCH-CONF-0001
title: Cross-Runtime Conformance Testing Strategy
status: Suite 0.1.0 baseline prepared (Phase 0.1); ADR-0008 Accepted 2026-11-01
version: 0.3.0
date: 2026-11-01
---

# Cross-Runtime Conformance Testing Strategy

## 1. Goal

One set of technology-independent fixtures, owned by Core, is executed by every runtime with its **native** test framework: PHPUnit for Laravel and Jest for Node. Every runtime gets the same inputs and the same expected **business outcomes**. The goal is equivalent business behavior, not identical internals.

A runtime may claim conformance or parity only as described in REPOSITORY_OWNERSHIP §7.

## 2. Location and layout

```text
waysnx-business-framework/tests/conformance/
├── README.md
├── RELEASE_NOTES.md         suite release notes (0.1.0 baseline)
├── suite.json               manifest: version, contracts, fixture IDs, fixtureCount
├── fixture.schema.json      JSON Schema for fixture files
└── fixtures/
    ├── validation.json          VAL-001 … VAL-009
    ├── workflow.json            WF-001 … WF-010
    ├── business-function.json   BF-001 … BF-007
    ├── authorization.json       AUTH-001 … AUTH-004
    └── lifecycle.json           LC-001 … LC-003
```

`specification/conformance/` is reserved for normative conformance requirements (prose). Executable fixtures live under `tests/` (ADR-0008).

## 3. Fixture format and suite manifest

Each fixture file declares `contract`, `suiteVersion`, `inputFormat` and `expectedFormat`, plus a `fixtures` array. Each fixture contains:

| Field | Meaning |
|---|---|
| `id` | Fixture ID (`VAL-002`), pattern `^[A-Z]+-\d{3}$`, unique across the suite, never reused |
| `description` | Scenario |
| `input` | Technology-neutral input. It never names runtime classes. Behaviors are declarative (`succeed`, `fail`, `throw`, `rule_violation`, `rule_violation_in_logic`, `resource_missing`; `allow`, `deny`, `none`, `invalid`) |
| `expected` | Expected **normalized** outcome. Non-empty. Only keys allowed for the contract (`suite.json` `expectedKeys`) |
| `metadata` | `decisions`, `findings` |

Assertions are partial: only the keys in `expected` are compared. Lists are compared in order. Objects are compared after key sorting.

`suite.json` is the source of truth for what the suite contains. Before any fixture result is trusted, each adapter runs a **suite integrity** test that checks:

- every contract file listed exists, declares the manifest `suiteVersion` and its `contract`
- the file contains exactly the manifest `fixtureIds`, in order, without duplicates
- every fixture has `id`, `description`, `input`, a non-empty `expected`
- `expected` uses only the contract's `expectedKeys`, and `expected.status` (where present) is one of the contract's `statuses`
- the total equals `fixtureCount` (33)

`fixture.schema.json` (JSON Schema draft 2020-12) documents the same rules for editors and external tools. The adapters implement the checks natively and do not add a schema-validation dependency.

## 4. Runtime adapters

| Runtime | Adapter | Known differences | Default fixture location | Environment |
|---|---|---|---|---|
| Laravel | `tests/Conformance/ConformanceTest.php` (PHPUnit data provider, testsuite `conformance`) | `tests/Conformance/conformance-known-differences.json` | `../waysnx-business-framework/tests/conformance/fixtures` | `WBF_CONFORMANCE_FIXTURES`, `WBF_CONFORMANCE_REPORT`, `WBF_CONFORMANCE_REQUIRED` |
| Node | `src/__tests__/conformance.test.ts` (Jest) | `src/__tests__/conformance-known-differences.json` | same | same |

Shared behavior:

| Situation | Local (default) | CI (`WBF_CONFORMANCE_REQUIRED=1`) |
|---|---|---|
| Fixtures or `suite.json` missing | Skipped, with a message naming the expected path | **Fails** with `CONFIGURATION ERROR: …` |
| Suite integrity problem | Fails | Fails |
| Unlisted fixture fails | Fails | Fails |
| Listed known difference fails | Reported (incomplete / `test.failing`) | Same |
| Listed known difference passes | **Fails** (the list must stay accurate) | Same |

With `WBF_CONFORMANCE_REPORT` set, each adapter writes `{runtime, suiteVersion, total, passed, failed, results[]}`. Each result includes the **normalized actual outcome**, so reports from two runtimes can be diffed directly.

Both known-differences files are currently empty (`{}`).

## 5. CI (prepared, not active)

Each runtime has `.github/workflows/conformance.yml`:

- checks out `waysnx/waysnx-business-framework` at ref **`conformance-v0.1.0`**
- sets `WBF_CONFORMANCE_REQUIRED=1` and `WBF_CONFORMANCE_FIXTURES` to the checkout's `tests/conformance/fixtures`
- runs the native adapter and uploads the JSON report

The trigger is `workflow_dispatch` only. **Activation** (approved under ADR-0008, pending) means adding `push` and `pull_request` triggers once the Core fixtures are committed and the tag exists. Until the tag exists a manual run fails at checkout, which is the intended signal. The existing `ci.yml` in each runtime is unchanged; there the conformance tests skip because no Core checkout is present.

## 6. Normalization rules (authoritative list)

Normalization exists so that runtimes can be compared by **business meaning** without identical representations. **Only the rules below are permitted.** Any normalization that would make a real behavioral difference disappear is forbidden (§6.6).

### 6.1 General

| Concern | Rule |
|---|---|
| Key casing | Fixtures use camelCase. Adapters build normalized outcomes directly in camelCase. No runtime output is re-cased |
| Identifiers | Generated IDs (`executionId`, issue UUIDs) are never compared |
| Dates and times | Timestamps and durations are never compared |
| Stack traces, internal metadata, messages | Never compared. Error **categories, codes and reasons** are compared instead |

### 6.2 Business Function result status (removed in Phase 0.1)

Phase 0 mapped Node `rule_failure`/`failure` and Laravel exceptions to the D1 vocabulary. Since D1 was implemented (WBF-SPEC-0006), both runtimes emit the D1 statuses natively:

- Node: `BusinessFunctionResult.status`, used as is.
- Laravel: `BusinessFunctionRuntime::run()` → `ExecutionResult::$status`, used as is.

No status mapping remains in either adapter.

### 6.3 Laravel exception mapping (removed in Phase 0.1)

Replaced by `run()` and the classification in WBF-SPEC-0006 §3, which lives in the runtime, not in the adapter. The former `unclassified_failure` value no longer occurs.

### 6.4 Workflow error kinds

Neither runtime emits workflow error codes for step failures yet. Error messages are mapped to kinds by these patterns only:

| Pattern | Kind |
|---|---|
| `No business function (runtime )?registered` | `function_not_registered` |
| `duplicate sequence` | `duplicate_sequence` |
| `duplicate step id` | `duplicate_step_id` |
| `Step '<id>' failed` | `step_failed` |
| anything else | `unclassified` (never matches an expectation) |

A definition the runtime refuses to construct is normalized to status `definition_rejected`. The adapters treat only the sequencing errors of WBF-SPEC-0007 (Laravel `WorkflowDefinitionException`; Node `WorkflowDefinitionError`) and duplicate-ID errors as rejection. Any other construction error propagates and fails the fixture.

### 6.5 Validation issues

Each issue is normalized to `{ruleId, field, code}` and grouped by severity into `errors` and `warnings`. Mapping:

- Laravel: `ruleId`, `target`, `errorCode`
- Node: `context.ruleId`, `context.field`, `code`

`status` is `passed` exactly when there is no error-severity issue.

### 6.6 Forbidden

- Mapping an exception or status to a category the runtime does not itself express.
- Ignoring keys listed in `expected`.
- Reordering `executionOrder`, `completedSteps`, `errors` or `warnings`.
- Treating `unclassified*` as a match.
- Editing a fixture to accommodate a runtime.

## 7. Conformance report (suite 0.1.0, 2026-11-01)

Commands (run with `WBF_CONFORMANCE_REPORT` set):

- Laravel: `vendor/bin/phpunit --testsuite conformance`
- Node: `npx jest src/__tests__/conformance.test.ts`

| Contract | Fixtures | Laravel | Node |
|---|---|---|---|
| Validation | 9 | 9/9 | 9/9 |
| Workflow | 10 | 10/10 | 10/10 |
| Business Function | 7 | 7/7 | 7/7 |
| Authorization | 4 | 4/4 | 4/4 |
| Lifecycle | 3 | 3/3 | 3/3 |
| **Total** | **33** | **33/33** | **33/33** |

Plus one suite-integrity test per runtime (passes in both).

Cross-runtime diff of the normalized actual outcomes in the two reports: **33 identical, 0 different**.

Phase 0 differences now resolved:

| Fixture | Phase 0 (Laravel) | Now | Resolved by |
|---|---|---|---|
| WF-003 missing sequence | `completed`, order `B,A,C` | `definition_rejected` | D8, WBF-SPEC-0007 |
| BF-004 business rule failure | `unclassified_failure` | `business_rule_failure` | D1, `run()` + phase fallback |
| BF-005 execution failure | `unclassified_failure` | `execution_failure` | D1, `run()` |

Fixtures added in Phase 0.1: WF-008 (negative sequence), WF-009 (non-integer sequence), WF-010 (sequence zero valid), BF-006 (missing business resource → `not_found`), BF-007 (rule violation raised by business logic → `business_rule_failure`). WF-004 changed from a fail-at-execution expectation to `definition_rejected`, because D8 rejects duplicates at construction.

Negative controls, run against temporary copies of the fixtures. Each one failed in both runtimes, as required:

| Control | Detected by |
|---|---|
| Duplicate fixture ID | integrity |
| Empty `expected` | integrity |
| Status not allowed for the contract | integrity |
| Wrong expectation (WF-002 order `A,C,B`) | fixture comparison |
| `fixtureCount` mismatch | integrity |
| Core checkout missing with `WBF_CONFORMANCE_REQUIRED=1` | `CONFIGURATION ERROR` |

Without `WBF_CONFORMANCE_REQUIRED`, a missing checkout skips.

**Parity statement.** Both runtimes produce identical normalized outcomes for all 33 fixtures of suite 0.1.0. That covers narrow slices of five areas: `required`/unknown-rule validation, step sequencing and fail-fast, Business Function result statuses, fail-closed authorization and lifecycle enablement. It is **not** a general equivalence claim. Everything in §8 is unverified, and the suite is not yet tagged or run in CI.

## 8. Not yet covered (planned)

| Area | Planned fixtures | Blocked by |
|---|---|---|
| Serialization | Canonical BFD round-trip, definition key casing | BFD schema (M2) |
| Errors | Public message hides internals (BF-ERR-03), error codes | M2 |
| Execution context | Correlation ID propagation (NOD-09), tenant propagation | Context contract (M2) |
| Lifecycle order | `afterStep` / `afterWorkflow` on failure (NOD-08) | Lifecycle event contract (M2) |
| Validation vocabulary | `type`, `minLength`/`maxLength`, `min`/`max`, `enum`, `pattern`, `compare` boundaries; `errors[]`/`warnings[]` on BF results | ADR-0007 vocabulary (M4) |
| Modules | Circular dependency rejection | Spec DR-03 enforcement decision |
| Entities | Identity, soft delete, revision conflict, audit from actor, undeclared attributes | ADR-0006 (M5, blocked) |

## 9. Rollout

| Step | Status |
|---|---|
| C0 fixture format + initial fixtures | **Done** (33 fixtures) |
| C1 adapters in both runtimes, known-difference lists | **Done** (lists empty) |
| C2 fix the P0/P1 runtime defects | **Done** for M0 scope and D1/D8 |
| C3 CI wiring against a pinned Core tag | **Prepared** (`conformance.yml`, manual trigger). Activation pending Core commit and tag `conformance-v0.1.0` |
| C4 fixture schema, suite manifest | **Done**. Remaining areas (§8) open |
