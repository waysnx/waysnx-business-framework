# ADR-0006 Entity Contract and Runtime Realization ("BaseModel")

| Field | Value |
|---|---|
| Identifier | ADR-0006 |
| Title | Entity Contract and Runtime Realization |
| Status | **Proposed – implementation blocked until accepted** |
| Date | 2026-10-31 |
| Deciders | Umesh Wani (pending) |
| Related | ENTITY_ARCHITECTURE.md, CORE_CONTRACTS.md, REPOSITORY_AUDIT.md (LAR-06, LAR-09, NOD-05, XR-03) |

## Context

- Laravel's `Core\BaseModelAbstract` / `Models\BaseModel` already exists.
  - It provides UUID identity, an integer version, audit and soft-delete fields, a metadata bag, and no-op lifecycle hooks.
  - It is the base class of the **meta-model artifacts** (Module, Domain, BusinessCapability, BusinessFunction, Workflow), not of application business entities.
  - It does not use Eloquent.
- Laravel `Repositories\BaseRepository` does not persist anything, and its `transaction()` is a no-op.
- Node has only `EntityInterface`, `AuditableInterface` and `VersionableInterface`. There are no implementations, repository or persistence.
- `EntityDefinition` differs between the runtimes:
  - Laravel requires `className` and `namespace`, and has a string version.
  - Node requires `moduleId`, and has a numeric version.
- The specification describes entities only descriptively (docs WBF-DOC-0013).
- Requirement: WBF Core must not require Eloquent, Prisma or any specific ORM.
- Spec WBF-SPEC-0003 AP-03 states "Composition Over Inheritance".

## Problem Statement

What does "BaseModel" mean in WBF, and how do two runtimes with different persistence ecosystems realize one entity contract without forcing an ORM or a shared inheritance model?

## Alternatives Considered

| Option | Summary | Reason rejected / chosen |
|---|---|---|
| A | One conceptual `BaseModel` class with the same inheritance in both runtimes | PHP single inheritance conflicts with Eloquent `Model`. Node/Prisma entities are plain objects or generated types. Violates AP-03 |
| B | Runtime-specific base classes with no shared contract | Gives no parity guarantee and repeats current divergence |
| C | **Technology-independent Entity contract + Repository/Transaction ports in the spec. Idiomatic runtime realizations (interfaces, traits/mixins, adapters)** | **Chosen** |
| D | Make Laravel `BaseModel` extend Eloquent `Model` | Couples core to Eloquent and breaks the existing artifact models that extend `BaseModel` |

## Decision

1. Introduce a normative **Entity contract** (ENTITY_ARCHITECTURE §3). It covers identity, attributes, canonical types, defaults, id-only relationships, framework lifecycle states, timestamps, soft delete, validation integration, lifecycle hooks, audit from the execution context, canonical serialization, an integer `revision` for optimistic concurrency, and a persistence-free entity.
2. Introduce **Repository** and **TransactionManager** ports. Persistence happens only through them.
3. "BaseModel" is **not** a WBF conceptual term.
   - The Laravel class keeps its name and API, and is documented as the *meta-model artifact base*.
4. Laravel realizes entities as Eloquent models that **compose** a `WbfEntity` interface and an `InteractsWithWbfEntity` trait, persisted by an Eloquent repository adapter.
5. Node realizes entities as plain objects described by an extended `EntityDefinition`, with:
   - an in-memory reference repository in core
   - a Prisma adapter in a separate package, with Prisma as a peer dependency, **only when a reference application justifies it** (revised in Phase 0, decision D3)
6. Neither runtime adds a new **mandatory** dependency.
7. **Identity (revised in Phase 0, decision D5).** The technology-independent `entityId` is a **string**. UUID (v4/v7) and ULID are *recommended* generation strategies, not mandated ones. Applications may use other string identifiers, including stringified database keys. WBF does not impose a single generation strategy.
8. **Versions (Phase 0, decision D4).** Any SemVer *version* of a definition is a string (e.g. `"1.0.0"`), never a number. The entity `revision` used for optimistic concurrency is a separate integer counter and is not a semantic version.
9. **No change to the existing Laravel `BaseModel` / `BaseModelAbstract`** to fit this design (Phase 0 directive). They remain the meta-model artifact base.

## Consequences

- Positive:
  - The core stays ORM-free.
  - Applications keep idiomatic models (`extends Model` in Laravel, Prisma types in Node).
  - Behavior is testable through conformance scenarios (ENT-*).
- Negative:
  - Lifecycle, audit and tenancy guarantees hold only when writes go through the repository. Direct Eloquent `save()` or Prisma calls bypass them, and this has to be documented.
  - There are two concepts to learn: artifact vs entity.
- Risks:
  - Duplicate hook firing if Eloquent model events and repository events are both bridged. A single source must be chosen.

## Migration implications

- No breaking change to `BaseModelAbstract`, `BaseModel`, `Types\EntityInterface` or Node `EntityInterface`.
- Laravel:
  - `Repositories\BaseRepository` is deprecated.
  - The unresolvable container binding `RepositoryInterface → BaseRepository` is removed in a later minor release.
- Additive optional fields on both `EntityDefinition`s. PHP named parameters are appended at the end of the constructor.
- Changing Node `EntityDefinition.version` from number to SemVer string **is** breaking. It needs separate approval.
- No database schema in this repository changes. Host applications adopting the adapters will need `revision` and soft-delete columns.

## Open decisions

See ENTITY_ARCHITECTURE §7:

- Eloquent adapter packaging
- integer vs UUID identity
- source of the lifecycle hooks
- Domain and Capability as runtime artifacts
- Prisma adapter timing

## References

- docs/architecture/ENTITY_ARCHITECTURE.md
- specification/foundation/WBF-SPEC-0003 (AP-03), WBF-SPEC-0004 (DR-04), WBF-SPEC-0005 §21 (ConcurrencyError)
- docs/02-business-architecture/WBF-DOC-0013, docs/04-implementation/WBF-DOC-0035

## Phase 0 Review (2026-10-31)

| Aspect | Assessment |
|---|---|
| Problem statement | Accurate. Re-verified: no business-entity implementation exists in either runtime. Laravel `BaseRepository` still does not persist (unchanged in Phase 0) |
| Proposed solution | Consistent with Phase 0 decisions D3 (contracts in Core, Eloquent trait + adapter, Node plain objects + in-memory reference, Prisma only when justified) and D5 (string IDs, UUID/ULID recommended). Revised above |
| Alternatives | Adequate. Option D (BaseModel extends Eloquent) correctly rejected |
| Gaps / unresolved | (1) Lifecycle-hook source in Laravel: repository-driven, Eloquent-event-driven, or both (risk of double firing). (2) Eloquent adapter packaging: same package with `suggest`, or a separate package. Both avoid a new mandatory dependency; choice needed. (3) Changing Node `EntityDefinition.version` from number to string (D4) is breaking and needs an approved migration (e.g. accept both for one minor, emit a deprecation). (4) The canonical serialization casing decision (camelCase) is not yet ratified. (5) Tenant scoping depends on an execution-context contract that is not yet approved |
| "Domain and Capability as runtime artifacts" | Resolved by D7: **metadata only** for now. No runtime registries until a reference application proves the need |
| Breaking changes | None in Phase 0 (nothing implemented). Future: Node `EntityDefinition.version` type (D4) |
| Laravel impact (future) | New interface + trait + Eloquent adapter. `BaseRepository` deprecation |
| Node impact (future) | New interfaces + in-memory adapter. `EntityDefinition` optional fields |
| Testing requirements | ENT-* conformance fixtures must exist before implementation |
| **Recommendation** | **Revise (done above). Accept after the open items (1)–(3) are decided** |
| **Approval status** | **Awaiting Umesh's approval. Entity implementation (M5) remains BLOCKED until this ADR is explicitly accepted.** |
