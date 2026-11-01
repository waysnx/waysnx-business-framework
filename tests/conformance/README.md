# WBF Cross-Runtime Conformance Fixtures

| | |
|---|---|
| Suite version | 0.1.0 (baseline prepared in Phase 0.1, **not tagged**) |
| Fixtures | 33 (`suite.json` → `fixtureCount`) |
| Owner | `waysnx-business-framework` (this repository) |
| Strategy | [docs/architecture/CROSS_RUNTIME_CONFORMANCE.md](../../docs/architecture/CROSS_RUNTIME_CONFORMANCE.md) |
| Release notes | [RELEASE_NOTES.md](RELEASE_NOTES.md) |
| Status | Expected outcomes follow decisions D1, D2, D6 and D8, approved 2026-11-01 (ADR-0007/0008/0009/0010, WBF-SPEC-0006/0007) |

These fixtures are technology-independent. They define inputs and expected **business outcomes**. Each runtime executes them through a native test adapter:

| Runtime | Adapter | Command |
|---|---|---|
| Laravel | `business-framework-laravel/tests/Conformance/ConformanceTest.php` | `vendor/bin/phpunit --testsuite conformance` |
| Node.js | `business-framework-node/src/__tests__/conformance.test.ts` | `npx jest src/__tests__/conformance.test.ts` |

## Locating the fixtures

By default, both adapters look for this directory as a sibling checkout (`../waysnx-business-framework/tests/conformance/fixtures`). `suite.json` must sit one level above `fixtures/`.

| Variable | Effect |
|---|---|
| `WBF_CONFORMANCE_FIXTURES` | Path to `tests/conformance/fixtures` of a Core checkout |
| `WBF_CONFORMANCE_REQUIRED=1` | Missing fixtures fail with `CONFIGURATION ERROR` instead of skipping. Set in CI |
| `WBF_CONFORMANCE_REPORT` | Write a JSON report (including normalized actual outcomes) to this path |

Without `WBF_CONFORMANCE_REQUIRED`, a missing checkout skips the suite so that a runtime can be developed on its own.

## Files

| File | Purpose |
|---|---|
| `suite.json` | Manifest: suite version, contract files, allowed `expected` keys and statuses per contract, ordered fixture IDs, total count |
| `fixture.schema.json` | JSON Schema (draft 2020-12) for fixture files |
| `fixtures/validation.json` | Validation (D2): VAL-001 … VAL-009 |
| `fixtures/workflow.json` | Workflow sequencing and invocation (D8, WBF-SPEC-0007): WF-001 … WF-010 |
| `fixtures/business-function.json` | Business Function result statuses (D1, WBF-SPEC-0006): BF-001 … BF-007 |
| `fixtures/authorization.json` | Fail-closed authorization (D6): AUTH-001 … AUTH-004 |
| `fixtures/lifecycle.json` | Lifecycle handler enablement (D12): LC-001 … LC-003 |

## Fixture structure

```json
{
  "id": "VAL-002",
  "description": "Required field missing",
  "input": { "...": "contract-specific, technology-neutral" },
  "expected": { "status": "failed", "errors": [], "warnings": [] },
  "metadata": { "decisions": ["D2"], "findings": ["LAR-01"] }
}
```

Assertions are partial: only the keys listed in `expected` are compared. Arrays are compared in order.

## Rules

1. Expected outcomes follow the Core contract, not the current behavior of either runtime.
2. Fixtures are never changed per runtime. A runtime that cannot meet a fixture lists its ID in its own known-differences file with a finding or decision reference. Both lists are currently empty.
3. Adapters fail when a listed known difference starts passing, so the lists stay accurate.
4. Adapters check suite integrity against `suite.json` before trusting any result.
5. The normalization rules are defined in CROSS_RUNTIME_CONFORMANCE.md §6. Any normalization not listed there is forbidden.

## Changing the suite

1. Add or change fixtures, update `suite.json` (`fixtureIds`, `fixtureCount`) and bump `suiteVersion` in the manifest and every fixture file.
2. Record the change in `RELEASE_NOTES.md`.
3. After approval and merge, tag `conformance-vX.Y.Z`. Runtimes pin their CI to the tag (ADR-0008).
4. Fixture IDs are never reused. A retired fixture keeps its ID out of circulation.
