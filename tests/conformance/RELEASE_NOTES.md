# WBF Conformance Suite – Release Notes

## 0.1.0 (baseline, prepared 2026-11-01, not tagged)

**Tag:** `conformance-v0.1.0` is **not created**. Creating it is not yet authorized. It should point at the Core commit that contains this directory as reviewed. Runtime CI is pinned to that tag (ADR-0008) and stays inactive until it exists.

### Contents

| Contract | File | Fixtures | Decisions / specs |
|---|---|---|---|
| Validation | `fixtures/validation.json` | VAL-001 … VAL-009 (9) | D2, ADR-0007 |
| Workflow | `fixtures/workflow.json` | WF-001 … WF-010 (10) | D8, WBF-SPEC-0007 |
| Business Function | `fixtures/business-function.json` | BF-001 … BF-007 (7) | D1, ADR-0009, WBF-SPEC-0006 |
| Authorization | `fixtures/authorization.json` | AUTH-001 … AUTH-004 (4) | D6, ADR-0010 |
| Lifecycle | `fixtures/lifecycle.json` | LC-001 … LC-003 (3) | D12 |
| **Total** | | **33** | |

Also part of the release: `suite.json` (manifest) and `fixture.schema.json`.

### Changes since the Phase 0 draft (28 fixtures, unreleased)

- Added WF-008 negative sequence → `definition_rejected`.
- Added WF-009 non-integer sequence → `definition_rejected`.
- Added WF-010 sequence `0` is valid and runs first.
- Added BF-006 missing business resource → `not_found`.
- Added BF-007 business-rule violation raised by business logic → `business_rule_failure`.
- Changed WF-004 duplicate sequence: now `definition_rejected` with no step executed (D8 rejects at construction).
- Added `suite.json` and `fixture.schema.json`. Adapters now verify suite integrity and support `WBF_CONFORMANCE_REQUIRED=1`.
- Business Function statuses are now the native D1 vocabulary. The adapters' status-normalization layers were removed.

### Results at baseline

| Runtime | Branch (uncommitted) | Result | Known differences |
|---|---|---|---|
| Laravel (`business-framework-laravel`) | `phase0/m0-remediation` | 33/33 + integrity | none |
| Node (`business-framework-node`) | `phase0/m0-remediation` | 33/33 + integrity | none |

The normalized outcomes of the two runtimes are identical for all 33 fixtures.

### Scope limits

The suite covers narrow slices of five contract areas. It does not cover serialization, error codes and message hygiene, execution context, lifecycle ordering, the full validation vocabulary, modules or entities (CROSS_RUNTIME_CONFORMANCE.md §8). Passing it is not a general equivalence claim.

### Compatibility

Suite 0.1.0 requires runtimes that implement:

- D1: result statuses `success`, `validation_failure`, `authorization_failure`, `not_found`, `business_rule_failure`, `execution_failure`
- D8: required, integer, non-negative, unique step sequences, rejected at construction

Laravel tag `v0.1.0` and Node `0.4.0` as committed on `main` predate these changes and will **not** pass. Only the uncommitted Phase 0.1 branches do.
