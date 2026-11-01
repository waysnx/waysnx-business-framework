# Roadmap

> **Master roadmap.** Owned by `waysnx-business-framework`. Runtime READMEs link here and list only runtime-specific items.
> Status values: **Done** means evidence exists (linked). **In progress**. **Not started**. **Blocked** means waiting on a named approval.
> Nothing is marked Done without test or review evidence. Evidence for Phase 0 is in [docs/audit/PHASE_0_COMPLETION_REPORT.md](docs/audit/PHASE_0_COMPLETION_REPORT.md).
> Updated 2026-10-31. This roadmap sequence is **proposed** and awaits Umesh's approval.

## v0.1 Foundation (original entry, retained)
- Repository
- WBF-DOC-0000 to WBF-DOC-0005 (the normative set is now identified as WBF-SPEC-0000 … WBF-SPEC-0005; see docs/00-governance/DOCUMENT_REGISTRY.md)

---

## M0 – Critical defect remediation

| | |
|---|---|
| Scope | Confirmed P0/P1 runtime defects: Laravel LAR-01, LAR-02, LAR-03, LAR-04, LAR-08. Node NOD-01, NOD-02, NOD-03, NOD-06, NOD-07. Validation parity XR-01 |
| Dependencies | Phase 0 directive (decisions D2, D6, D12 behavior). No public contract approvals needed for the behavior-level fixes |
| Deliverables | Fixes plus regression tests in both runtimes. Updated test doubles and demos |
| Acceptance criteria | No implicit allow in validation or authorization. Workflow sequence respected. Hard-coded-date failures resolved. Both suites green |
| Status | **Done.** API names approved (ADR-0010, 2026-11-01). Phase 0.1: Laravel 1016 tests pass, Node 283 tests pass |
| Blockers | None |

## M1 – Repository and documentation alignment

| | |
|---|---|
| Scope | Ownership (ADR-0005), frozen copies, document IDs, hierarchy, README accuracy |
| Dependencies | ADR-0005 approval for removal steps |
| Deliverables | REPOSITORY_OWNERSHIP.md, DOCUMENT_REGISTRY.md, CONCEPTUAL_HIERARCHY.md, frozen notices, corrected READMEs (all three repos) |
| Acceptance criteria | Ownership unambiguous. Unique document IDs with migration map. README claims match verified reality |
| Status | **In progress.** Phase 0 delivered the documents, notices and README corrections. Still open: removal steps R2–R4, START_HERE/REPOSITORY_GUIDE directory maps, CHANGELOG `1.0.0-preview` entry, 5 unresolvable legacy references |
| Blockers | ADR-0005 approval (R2–R4). Human judgment on the legacy references |

## M2 – Core normative contracts

| | |
|---|---|
| Scope | Promote CORE_CONTRACTS.md to `WBF-SPEC-0006+`: Business Function result and error categories (ADR-0009), execution context (actor, tenant, correlation), authorization contract (ADR-0010), lifecycle event names, workflow sequencing (incl. D8), version format (D4), identifiers (D5), serialization casing |
| Dependencies | Approval of D1, D4, D5, D6, D8 |
| Deliverables | New WBF-SPEC documents. Schemas: BFD, result, error, execution context, fixture schema |
| Acceptance criteria | Specs reviewed and approved. Schemas validate the existing fixtures |
| Status | **Not started** |
| Blockers | Decisions D1, D4, D5, D6, D8 |

## M3 – Cross-runtime conformance foundation

| | |
|---|---|
| Scope | Shared fixtures, native adapters, known-difference policy, reports, CI |
| Dependencies | ADR-0008. A Core tag for pinned CI checkout |
| Deliverables | `tests/conformance/fixtures` (33 fixtures), `suite.json`, `fixture.schema.json`, Laravel and Node adapters, CROSS_RUNTIME_CONFORMANCE.md, `conformance.yml` in both runtimes |
| Acceptance criteria | Fixtures execute in both runtimes. Differences documented. CI runs the suite against a pinned tag |
| Status | **In progress.** Suite 0.1.0 baseline prepared: Laravel 33/33, Node 33/33, identical normalized outcomes, no known differences. Fixture schema and manifest done. CI workflows prepared with a manual trigger only |
| Blockers | Core commit of the fixtures, then approval to create the `conformance-v0.1.0` tag. CI activation follows the tag |

## M4 – Validation implementation

| | |
|---|---|
| Scope | ADR-0007 rule vocabulary v1. Laravel Validator adapter. Node evaluator completion. Warnings in BF results. Explicit BF ↔ validation binding |
| Dependencies | ADR-0007 approval. Approval of the `illuminate/validation` dependency (Laravel) |
| Deliverables | Rule compiler/engine (Laravel), extended evaluator (Node), VAL-* fixtures for the full vocabulary |
| Acceptance criteria | All VAL-* fixtures pass in both runtimes |
| Status | **Not started.** The M0 subset (`required`, fail-closed) is done |
| Blockers | ADR-0007, dependency approval |

## M5 – Entity foundation

| | |
|---|---|
| Scope | ADR-0006 Entity / Repository / Transaction contracts. Laravel Eloquent trait + adapter. Node in-memory reference adapter. Prisma only when justified |
| Dependencies | **Explicit approval of ADR-0006**, M2 (execution context, tenant), M3 (ENT-* fixtures) |
| Deliverables | Entity spec, ENT-* fixtures, runtime adapters |
| Acceptance criteria | ENT-* fixtures pass in both runtimes. No ORM required by core |
| Status | **Blocked** |
| Blockers | **ADR-0006 must be explicitly approved before any Entity implementation** |

## Later (not scheduled)
- WaysNX DQP, UI Kit, Studio (README "Phase 4 – Ecosystem Expansion")
- Additional reference implementations (Spring Boot, ASP.NET, Go, Python)
- Workflow transitions, conditions, retry and compensation (spec work first)
