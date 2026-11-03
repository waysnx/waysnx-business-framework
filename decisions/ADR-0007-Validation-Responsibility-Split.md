# ADR-0007 Validation Responsibility Split

| Field | Value |
|---|---|
| Identifier | ADR-0007 |
| Title | Validation Responsibility Split and Fail-Closed Semantics |
| Status | **Accepted in part** – the M0 validation behavior (D2) approved by Umesh Wani, 2026-11-01. Rule vocabulary v1 and the Laravel Validator adapter remain **Proposed** (M4) |
| Date | 2026-10-31 |
| Deciders | Umesh Wani (pending) |
| Related | VALIDATION_ARCHITECTURE.md, REPOSITORY_AUDIT.md (LAR-01, NOD-04, XR-01, LAR-21) |

## Context

- Laravel `ValidationFramework::evaluateRule()` returns `true` by default, so every rule passes. This was reproduced for a `required` rule on empty input.
- Node evaluates `required`, `range` and `format`, but `custom` and unknown types pass.
- Node's BF runtime drops warnings.
- As a result, the same definition produces opposite outcomes in the two runtimes.
- The spec gives validation a position in the pipeline (WBF-SPEC-0005 BF-VAL) but defines no rule vocabulary, severities or result shape.
- The "canonical" BFD fixture uses Laravel rule-string syntax.

## Problem Statement

Where do validation definitions, evaluation and custom rules belong, and how do we guarantee the same business outcome without reimplementing Laravel Validator or forcing a Node validation library on users?

## Alternatives Considered

1. **The spec defines only outcomes; each runtime picks its own rule syntax.**
   - Definitions are not portable, so conformance can't use shared inputs. Rejected.
2. **WBF ships its own full validation engine in both runtimes.**
   - Duplicates Laravel Validator and adds maintenance cost. Rejected.
3. **The spec defines a small canonical rule vocabulary, issue/result shape and outcome semantics. Runtimes compile canonical rules to their native engine and normalize the results. Applications own custom and business rules (chosen).**

## Decision

Adopt alternative 3 (VALIDATION_ARCHITECTURE §2–§7):

- **Four layers:**
  - L1 contract validation, before authorization
  - L2 entity validation
  - L3 business rules, after authorization
  - L4 static workflow-definition checks
- **Canonical vocabulary v1:**
  - `required`, `type`, `minLength`, `maxLength`, `min`, `max`, `pattern`, `format`, `enum`, `compare`, `custom`
- **Result:**
  - `{passed, issues[]}`, where each issue is `{severity, code, path, message, ruleId, validationId}`
  - `passed` means there are no error-severity issues
  - Warnings never block, and they are always returned to the caller
- **Fail closed:**
  - An unknown rule type or an unregistered custom rule produces an error issue
- **Engines:**
  - Laravel uses the Laravel Validator through a compiler and adapter
  - Node extends its built-in, zero-dependency evaluator
- **Binding:**
  - BF ↔ validation binding is explicit in the BFD
  - Node's implicit `scope === moduleId` lookup is deprecated

## Consequences

- Positive:
  - Portable definitions
  - Conformance-testable outcomes
  - No duplicate engine
- Negative:
  - Fail-closed is a behavioral change. Applications relying on the default pass break, which is the intended effect for a security-relevant default.
- Rollout (revised in Phase 0):
  - ~~A `strict` flag that reports unknown rules as warnings for one minor release, then as errors.~~ Withdrawn. The Phase 0 directive forbids making validation permissive for backward compatibility. Fail-closed applies immediately, with no warn-only transition.

## Migration implications

- Laravel:
  - The `evaluateRule()` signature is unchanged.
  - The default implementation delegates to the engine.
  - `illuminate/validation` is declared as an optional (suggested or guarded) dependency, not a mandatory one.
- Node:
  - `ValidationIssue` gains `code` (added in Phase 0), plus `path` and `ruleId` (M4). These are additive.
  - The `custom` rule changes from a passing no-op to fail-closed. Applications implement it by overriding the now-`protected` `evaluateCustom()`. This was done in Phase 0 and is breaking for anyone relying on `custom` passing. A registry-based extension (M4) remains a proposal.
- Canonical BFD fixture:
  - Canonical rule objects are added.
  - The Laravel strings are kept as a runtime-specific representation.

## References

- docs/architecture/VALIDATION_ARCHITECTURE.md
- specification/foundation/WBF-SPEC-0005 §13, BF-VAL-01…06, §21

## Phase 0 Review (2026-10-31)

| Aspect | Assessment |
|---|---|
| Problem statement | Confirmed by execution. Laravel `evaluateRule()` returned `true` for everything. Node passed `custom` and unknown types |
| Proposed solution | Direction is sound and matches Phase 0 decision D2. The full rule vocabulary v1 is **not** needed to close the P0 defect |
| Contradiction found | The "strict flag, warn first" rollout contradicted the Phase 0 instruction not to make validation permissive for backward compatibility. Withdrawn above |
| Unresolved questions | (1) Exact semantics of `range`/`format` for missing values in Node (they skip today; Laravel has no such rules). (2) Whether `custom` extension stays by subclass override (Phase 0) or moves to a named-rule registry (M4). (3) Laravel Validator adoption requires `illuminate/validation`, which is a new dependency and needs approval. (4) The `info` severity exists only in Laravel |
| Implemented in Phase 0 (M0 subset, both runtimes) | `required` semantics: missing, null, whitespace-only and empty array fail; 0 and false are present. Unknown or unimplemented rule types fail closed as errors (`WBF.VALIDATION.UNKNOWN_RULE`), even when declared as warnings. A required rule without a target fails closed (`WBF.VALIDATION.INVALID_RULE`). Default code `WBF.VALIDATION.REQUIRED`. Warnings never block. All errors are collected |
| Breaking changes | Behavioral. Laravel rules of unsupported types (including the default `ruleType: 'custom'`) now fail unless the application overrides `evaluateRule()`. Node `custom` and unknown types now fail. No signature removed |
| Laravel impact | `ValidationFramework::evaluateRule()` default, `createIssue()` default code and message, new public constants. Tests: `tests/Unit/Validation/ValidationFailClosedTest.php` |
| Node impact | `ValidationFramework` (unknown and custom fail closed, `evaluateCustom` now protected, `isMissingOrEmpty`), `ValidationIssue.code`. Tests: `src/__tests__/phase0-remediation.test.ts` |
| Conformance | VAL-001 … VAL-009 pass in both runtimes |
| **Recommendation** | **Revise (done above), then Accept.** The full vocabulary and the Laravel Validator adapter stay in M4 |
| **Approval status** | **M0 subset Accepted** – Umesh Wani, 2026-11-01. Remainder Proposed |
