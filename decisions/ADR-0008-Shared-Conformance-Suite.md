# ADR-0008 Shared Cross-Runtime Conformance Suite

| Field | Value |
|---|---|
| Identifier | ADR-0008 |
| Title | Shared Cross-Runtime Conformance Suite |
| Status | **Accepted** – approved by Umesh Wani, 2026-11-01. CI uses `WBF_CONFORMANCE_REQUIRED=1`, pinned to `conformance-v0.1.0`; activation pending tag (tag not yet authorized) |
| Date | 2026-10-31 |
| Deciders | Umesh Wani (pending) |
| Related | CROSS_RUNTIME_CONFORMANCE.md, REPOSITORY_AUDIT.md (PRI-08, XR-07) |

## Context

Both runtime READMEs claim semantic equivalence:

- Node: "portable contracts identical to the Laravel implementation"
- Primary: "Both implementations follow the same WBF concepts and architectural semantics"

No shared test supports these claims. Targeted probes during the audit showed divergent outcomes for validation (XR-01) and workflow ordering (LAR-02 vs NOD-02).

`specification/conformance/` exists but is empty. Spec WBF-SPEC-0000 §11 and WBF-SPEC-0005 §24 describe conformance only in prose.

## Problem Statement

How do we make parity a measurable property, without duplicating tests with diverging expectations in each runtime?

## Alternatives Considered

1. **Each runtime writes its own parity tests.**
   - Expectations drift. This is today's situation.
2. **A single cross-language test harness**, e.g. a CLI-driven black-box runner in one language.
   - Forces a process boundary and a CLI contract that neither runtime has.
   - Tests the CLI rather than the library API.
3. **Declarative JSON scenarios owned by the spec repo, executed by thin native drivers (PHPUnit, Jest) that call the library API (chosen).**

## Decision

Adopt alternative 3, as detailed in CROSS_RUNTIME_CONFORMANCE.md:

- **Scenario location (revised in Phase 0).** Shared fixtures live in `tests/conformance/fixtures/` (one JSON file per contract area) and are versioned with the specification. `specification/conformance/` is reserved for normative conformance *requirements* (prose). The Phase 0 directive placed executable fixtures under `tests/`, matching the existing directory purpose "Framework validation assets" (README repository tree).
- **Declarative behaviors.** Scenarios use declarative behaviors and contain no runtime code. Comparisons are normalized, and the clock is injected.
- **Native drivers.** Runtimes implement drivers as test code only. Locally they read the sibling checkout `../waysnx-business-framework/tests/conformance/fixtures`, or `$WBF_CONFORMANCE_FIXTURES`. In CI they will check out a pinned tag (not yet wired, see Phase 0 review). No new package dependency and no submodule is added.
- **Known differences.** Each runtime keeps a `conformance-known-differences.json` file that references findings or pending decisions. A listed fixture that fails is reported, not hidden: PHPUnit marks it incomplete and Jest runs it as `test.failing`. A listed fixture that *passes* fails the run. Fixture expectations are never changed per runtime.
- **Normalization.** Only the rules listed in CROSS_RUNTIME_CONFORMANCE.md §6 may be applied. They compare business meaning, not representation, and must not hide behavioral differences.
- **Parity claims.** A runtime may claim conformance or parity only for a suite version it fully passes.

## Consequences

- Positive:
  - Parity becomes evidence-based.
  - The capability matrix can be generated from reports.
  - Spec ambiguities surface early.
- Negative:
  - Each runtime has to maintain a driver.
  - Scenario authoring needs care to stay technology-neutral.
- Dependency:
  - The schemas in CROSS_RUNTIME_CONFORMANCE §4 must be authored first.

## Migration implications

- No runtime code changes are needed to adopt the ADR.
- Drivers are additive test code.
- README parity claims must be reworded until the suite passes (documentation synchronization plan, step S1).

## References

- docs/architecture/CROSS_RUNTIME_CONFORMANCE.md
- specification/foundation/WBF-SPEC-0000 §11, WBF-SPEC-0005 §24

## Phase 0 Review (2026-10-31)

| Aspect | Assessment |
|---|---|
| Problem statement | Sound. Confirmed by execution: the same `required` rule and the same `[1,3,2]` workflow gave different outcomes before M0 |
| Existing architecture | No shared tests existed. Each runtime had its own suite with different expectations |
| Proposed solution | Implemented as a foundation in Phase 0: 28 fixtures in 5 contract areas, a Laravel PHPUnit adapter and a Node Jest adapter |
| Alternatives | Adequate. The CLI black-box harness was rightly rejected (it would test the CLI, not the library contracts) |
| Gaps found | (1) The original location `specification/conformance/` conflicted with the Phase 0 directive (`tests/conformance/fixtures/`). Revised above. (2) The ADR did not define normalization. It is now defined in CROSS_RUNTIME_CONFORMANCE §6. (3) Pinned-tag CI consumption needs a Core tag such as `conformance-v0.1.0`. No tag was created (Phase 0 restriction). (4) The JSON schema for fixtures is not yet authored |
| Breaking changes | None. Adapters are test-only. The Laravel `phpunit.xml` gains a `conformance` suite that is skipped when fixtures are absent |
| Laravel impact | `tests/Conformance/ConformanceTest.php`, known differences WF-003, BF-004, BF-005 |
| Node impact | `src/__tests__/conformance.test.ts`, no known differences |
| Migration | None |
| Testing requirements | Both adapters were verified with a negative control: a tampered expectation fails in both runtimes |
| **Recommendation** | **Accept with the revisions above** |
| **Approval status** | **Accepted** – Umesh Wani, 2026-11-01 |
