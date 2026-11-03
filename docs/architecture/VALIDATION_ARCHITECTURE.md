---
documentId: WBF-ARCH-VAL-0001
title: Validation Architecture (WBF-004)
status: Proposed – see ADR-0007
version: 0.1.0
date: 2026-10-31
---

# Validation Architecture

This document defines the common business-level validation semantics and the split of responsibilities between WBF Core (the spec), the runtimes, and applications.

The decision record is [ADR-0007](../../decisions/ADR-0007-Validation-Responsibility-Split.md).

## 1. What exists today (verified)

| Aspect | Laravel | Node |
|---|---|---|
| Definition | `ValidationDefinition(id, name, displayName, description, version, moduleId, category, scope='entity', severity='error', rules[], supportedEntityTypes[], businessFunctionIds[], workflowIds[], priority, …)` | `ValidationDefinition(id, name, displayName, moduleId, scope, severity='error', enabled, version, rules: Record, metadata)` |
| Rule definition | `ValidationRuleDefinition(id, name, ruleType='custom', target, message, errorCode, severity='error', priority, metadata)` | rule object `{type, field, severity?, min?, max?, pattern?, message?}` |
| Built-in rule evaluation | **None.** `evaluateRule()` returns `true` (LAR-01) [V-exec] | `required`, `range` (number or length), `format` (RegExp). `custom` and unknown types **pass** (NOD-04) |
| Severities | `error`, `warning`, `info` | `error`, `warning` |
| Result | `ValidationResult{passed, issues[], executionTime, metadata}`. `passed` = no error issues | `ValidationIssue[]`. BF runtime throws `BusinessRuleViolation` on the first error and **drops warnings** |
| Issue | `ValidationIssue(id, ruleId, severity, message, target, errorCode, category, metadata)` | `ValidationIssue(validationId, severity, message, context)` |
| Lookup | By validation id (`execute($validationId, $ctx)`) | By scope. BF runtime uses `scope === definition.moduleId` |
| Stop on error | `stopOnError` per definition | no |
| Laravel Validator | Not used anywhere in `src/` | n/a |
| Canonical BFD fixture | `processing.validation` uses Laravel rule strings (`required\|string\|max:50`) (LAR-21) | — |
| Spec | Severity is listed as metadata (docs 0014) but undefined. Spec 0005 BF-VAL: validation runs before authorization, causes no state change, and failure terminates execution | — |

**Consequence:** one definition gives a different business outcome per runtime (XR-01, P0). For example, `required` on an empty `name` passes in Laravel and fails in Node.

## 2. Validation layers

| Layer | When | What | Failure maps to | Spec |
|---|---|---|---|---|
| **L1 Contract validation** | Pipeline step 1, before authorization | Shape, required, type, format, length, range, enumeration of the *request* | `ValidationError` → `status: validation_failure` | BF-VAL-01…06 |
| **L2 Entity validation** | Before repository create/update | Attribute constraints from EntityDefinition + entity-scoped ValidationDefinitions | `ValidationError` | ENTITY_ARCHITECTURE §3.9 |
| **L3 Business rule validation** | Pipeline step 3, after authorization | Stateful rules (balance sufficient, no overlap) | `BusinessRuleViolation` → `status: rule_failure` | BF-02, spec 0005 §13 |
| **L4 Workflow validation** | Definition registration (static) and before each step (via that step's BF) | Static: unique step IDs and sequences, referenced BFs exist. Runtime: delegated to the step's BF | Static: framework configuration error. Runtime: step's category | [Proposed] |

L1 and L2 are stateless and declarative, and are expressible in the canonical rule vocabulary (§5). L3 is application code with a standard result shape.

## 3. Responsibility split

| Concern | WBF Core (spec) | Runtime | Application |
|---|---|---|---|
| Rule vocabulary and semantics (§5) | **Defines** | Implements evaluators | Uses |
| Validation definition and result schemas | **Defines** | Value objects | Authors definitions |
| Outcome semantics (pass/fail, severities, ordering) | **Defines** | Enforces | — |
| Evaluation engine | — | **Owns.** Laravel: Laravel Validator adapter. Node: built-in evaluator | — |
| Custom rule implementations | Extension contract (§7) | Registry for named custom rules | **Owns** |
| Business rules (L3) | Result shape and pipeline position | Invocation | **Owns** logic |
| Messages and localization | Default message keys | Translation adapter (Laravel `lang`) | Overrides |

WBF does **not** reimplement Laravel Validator or adopt a Node validation library as a mandatory dependency. The runtimes translate canonical rules to their engines and normalize the output into the canonical result.

## 4. Outcome semantics (mandatory, proposed)

1. A validation produces a `ValidationResult` = `{ passed: boolean, issues: Issue[] }`.
2. `Issue = { severity: "error"|"warning"|"info", code: string, path: string, message: string, ruleId: string, validationId: string, params?: object }`.
   - `path` uses dot notation (`attachments.0`), matching Node's `getNestedValue` and Laravel's `attachments.*`.
3. `passed` is `true` exactly when there are zero `error` issues [Both].
4. Warnings and info **never** block. They **must** be returned to the caller, in the BF result's `issues[]`. Fixes Node dropping them (NOD-04).
5. All rules of a validation are evaluated, and all issues collected, unless `stopOnFirstError: true` [Proposed]. The default is collect-all, which is Laravel's behavior when `stopOnError` is false. Node's BF runtime currently reports only the first error message, though it attaches all issues in `context.issues`.
6. **Fail closed:** an unknown or unimplemented `ruleType` produces an `error` issue with code `WBF.VALIDATION.UNKNOWN_RULE` [Proposed]. This replaces default-pass in both runtimes (LAR-01, NOD-04).
7. A rule evaluator throwing is reported as `InternalSystemError`, not as a validation issue. Node currently converts it to an `error` issue, and Laravel wraps it in `ValidationRuleException`.
8. `null` vs. missing vs. empty string:
   - `required` fails for missing, `null`, `""`, and empty arrays.
   - Every other rule *skips* `null` or missing values unless `required` is also present (Laravel "nullable" semantics).
   - Node `range` and `format` currently evaluate `undefined`. **Decision required.**
9. Severity precedence: the rule's severity wins. If it is absent, the definition's severity applies, then the default `error`. Node currently ignores definition severity.
10. Validation→BF binding is explicit. A BFD lists `processing.validation` rules (L1) and the ids of entity or business validations. Implicit `scope === moduleId` lookup (Node) is deprecated.

## 5. Canonical rule vocabulary (proposed v1)

Each rule is a JSON object: `{ "rule": "<type>", ...params, "severity"?: "...", "message"?: "...", "code"?: "..." }`.

| Rule | Params | Applies to | Semantics | Laravel mapping | Node today |
|---|---|---|---|---|---|
| `required` | — | any | Fails for missing, `null`, `""`, `[]` | `required` | ✅ (not `[]`) |
| `type` | `of`: string, integer, number, decimal, boolean, date, datetime, array, object | any | Type check on the JSON value | `string`, `integer`, `numeric`, `boolean`, `date_format:Y-m-d`, `array` | ✗ |
| `minLength` / `maxLength` | `value` | string (code points), array (items) | Inclusive | `min`/`max` combined with `string`/`array` | `range` (`.length`) |
| `min` / `max` | `value`, `exclusive?` | number, decimal, date | Inclusive by default | `min`/`max` with `numeric`, `after_or_equal`, `before_or_equal` | `range` |
| `pattern` | `regex` (ECMAScript subset, anchored by author) | string | Full-string semantics as written | `regex:/…/` | `format` |
| `format` | `name`: email, uuid, date, datetime, uri | string | Named formats | `email`, `uuid`, `date_format`, `url` | ✗ |
| `enum` | `values[]` | scalar | Membership, case-sensitive | `in:` | ✗ |
| `compare` | `field`, `op`: eq, ne, lt, lte, gt, gte | any | Cross-field comparison | `same`, `different`, `gt`, `lte`, `after_or_equal:field` | ✗ |
| `custom` | `name`, `params?` | any | Delegates to a registered custom rule (§7). Unknown name → fail closed | Custom `Rule` class | no-op |

Relative date keywords such as Laravel's `after_or_equal:today` are business rules (L3). They depend on a clock and are deliberately **not** in the stateless vocabulary. This also explains the time-dependent test failures in LAR-04.

## 6. Runtime realization (proposed)

### 6.1 Laravel

- Add a `RuleCompiler` that translates canonical rules to Laravel Validator rule arrays.
- Add an `IlluminateValidationEngine` that runs `Validator::make()` and maps `MessageBag` entries into canonical `Issue`s (path, code = canonical rule, message).
- `ValidationFramework::evaluateRule()` keeps its signature. The default implementation delegates to the engine, and unknown types fail closed (behind a `strict` config flag for one minor release).
- Requires `illuminate/validation`, which ships with Laravel apps. Declare it as `suggest` or a guarded optional dependency so it does not become mandatory (decision shared with ENTITY_ARCHITECTURE §7).
- Canonical BFD fixture: keep the Laravel rule strings as a Laravel-specific representation, and add canonical rule objects.

### 6.2 Node.js

- Extend the built-in evaluator to the full §5 vocabulary with zero dependencies. The vocabulary is small enough for this.
- Add a `CustomRuleRegistry` and fail closed for unknown rules.
- Return warnings in `BusinessFunctionResult.issues`.
- Introduce L1 contract validation in `validateRequest` from `processing.validation`.
- An optional adapter interface (`ValidationEngine`) lets applications plug in a JSON-Schema or other engine without adding a dependency to core.

## 7. Custom rule extension contract

```text
CustomRule {
  name: string                         // unique, namespaced: "hr.leaveBalanceSufficient"
  evaluate(value, params, ctx) -> Issue[] | Promise<Issue[]>
}
```

- Registered per runtime (Laravel container tag, Node registry).
- Must be pure with respect to the request. Stateful checks belong in L3 business rules.
- Must return canonical issues and must not throw for invalid data.

## 8. Conformance scenarios (to be added to the suite)

VAL-REQ-001 required missing · VAL-REQ-002 required null/empty · VAL-TYPE-001 · VAL-LEN-001 boundary (min, max, max+1) · VAL-RANGE-001 boundary · VAL-ENUM-001 · VAL-PATTERN-001 · VAL-UNKNOWN-001 fail-closed · VAL-WARN-001 warnings returned, not blocking · VAL-COLLECT-001 multiple issues · VAL-NULLABLE-001 · VAL-BF-ORDER-001 validation before authorization. Details are in [CROSS_RUNTIME_CONFORMANCE.md](CROSS_RUNTIME_CONFORMANCE.md).
