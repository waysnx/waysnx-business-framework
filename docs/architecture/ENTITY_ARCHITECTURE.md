---
documentId: WBF-ARCH-ENT-0001
title: Entity and BaseModel Architecture (WBF-003)
status: Proposed – implementation blocked pending review of ADR-0006
version: 0.1.0
date: 2026-10-31
---

# Entity and BaseModel Architecture

This document defines a technology-independent **Entity contract** and how the Laravel and Node runtimes should realize it. It also evaluates what the term "BaseModel" should mean.

The decision record is [ADR-0006](../../decisions/ADR-0006-Entity-Contract-and-Runtime-Realization.md).

**No implementation is to start until ADR-0006 is reviewed.**

## 1. What exists today (verified)

| Item | Laravel | Node |
|---|---|---|
| Entity interface | `Types\EntityInterface`: `getEntityId()`, `getEntityType()`, `getEntityVersion()`, `toArray()`, `toJson()` | `EntityInterface extends SerializableInterface`: `getEntityId(): string\|number`, `getEntityType()`, `getEntityVersion(): number`, `toArray()`, `toJson()` |
| Audit interface | `Types\AuditableInterface`: created/updated/deleted By and At, `isDeleted()` | `AuditableInterface`: created/updated At and By with setters. **No delete fields** |
| Versionable interface | `Types\VersionableInterface` (unused) | `VersionableInterface`: `getVersion()`, `incrementVersion()`, `isVersion()` |
| Base class | `Core\BaseModelAbstract` → `Models\BaseModel`, with UUID v4 identity (ramsey/uuid), FQCN type, int version, audit and soft-delete fields, metadata bag, and no-op `before/after{Validate,Create,Update,Delete}` hooks. **Not Eloquent.** | none |
| What extends the base | **Meta-model artifacts**: Module, Domain, BusinessCapability, BusinessFunction, Workflow (LAR-09) | — |
| Entity definition | `Registry\EntityDefinition(id, name, displayName, className, namespace, description, version:string, tags, metadata)` | `EntityDefinition(id, name, displayName, moduleId, version:number, enabled, tags, metadata)` |
| Attributes / types / defaults | none | none |
| Relationships | none (artifacts hold ID arrays) | none |
| Repository | `Contracts\RepositoryInterface` + `BaseRepository`. Does not persist, and `transaction()` is a no-op (LAR-06) | none |
| Persistence adapter | none (no Eloquent usage anywhere in `src/`) | none (no Prisma; zero dependencies) |
| Spec | docs WBF-DOC-0013 (descriptive): "persistent realization of a Business Object… identity, lifecycle and business state". Metadata: Entity ID, Name, Business Object, Business Key, Owner, Status, Version, Relationships | — |

**Key observation:** in today's code, "BaseModel" means *the base of framework artifacts*, not *the base of application business entities*. These two concepts must be separated before any entity work starts.

## 2. Concepts and vocabulary (proposed)

| Term | Meaning | Lives in |
|---|---|---|
| **Entity Definition** | Metadata describing an entity type: attributes, constraints, relationships, soft-delete and audit flags | Spec + schema. Runtime value object |
| **Entity** | An instance of an application business object with stable identity | Application code, using a runtime realization |
| **Entity contract** | Technology-independent behavioral rules every Entity realization must satisfy (§3) | Spec |
| **Repository** | Port that loads and persists entities and enforces lifecycle, audit, tenancy and concurrency rules | Spec (port) + runtime adapters |
| **Meta-model artifact** | Module, Domain, Capability, BusinessFunction, Workflow descriptors | Spec 0004/0005 |
| **BaseModel** | *Runtime-specific* Laravel class name for the artifact base. **Not** a WBF conceptual term | Laravel only (kept for backward compatibility) |

## 3. Technology-independent Entity contract

Each item states its source: [Spec], [Both] (both runtimes already do it), or [Proposed].

| # | Topic | Mandatory | Optional / extension | Responsibility |
|---|---|---|---|---|
| 1 | **Identity** | An entity has `entityType` (= EntityDefinition id) and `entityId`. `entityId` is an opaque scalar. It is serialized as a string in canonical form, immutable after creation, and unique within `entityType` (and within tenant, if multi-tenant). Equality is by (`entityType`, `entityId`). [Proposed; Laravel uses FQCN as type, see §6] | Generation strategy: UUID v4/v7, DB auto-increment, or application-assigned. A **business key** (docs 0013) is a separate attribute | F: rules. R: generation. A: business key |
| 2 | **Attributes** | Declared in the EntityDefinition: `name`, `type`, `required`, `nullable`, `default`, `writable`, `sensitive`. Undeclared attributes MUST NOT be assigned from external input [Proposed; mitigates LAR-06 mass assignment] | Constraint keywords from VALIDATION_ARCHITECTURE §5 | F: schema. A: definitions |
| 3 | **Type handling** | Canonical types: `string`, `integer`, `decimal`, `boolean`, `date`, `datetime`, `enum`, `json`, `reference`. JSON form: `decimal` as string, `date` `YYYY-MM-DD`, `datetime` RFC 3339 UTC | Runtime-native mapping (PHP `Carbon`/`DateTimeImmutable`, JS `Date`/string; `bcmath`/`decimal.js` or string) | R |
| 4 | **Defaults** | Static defaults from the definition are applied once, on creation, before validation | Dynamic defaults are application code (`beforeCreate`) | F / A |
| 5 | **Relationships** | Core contract references other entities **by identity only** (`reference` attribute: `targetEntityType`, `cardinality: one\|many`). Cross-aggregate writes go through Business Functions (spec 0004 DR-04) | Navigation, eager loading and ORM relations are runtime-specific | R |
| 6 | **Lifecycle** | Framework states: `new` → `active` → `deleted` (soft) → `active` (restore). Hard purge is application-controlled. *Business status* (docs 0013: Create, Validate, Active, Update, Archive, Retire) is a domain attribute, not framework state | — | F: states. A: business status |
| 7 | **Timestamps** | `createdAt` is set on first persist. `updatedAt` is set on every persist. UTC. Set by the repository from a clock port, never from input [Proposed] | Disable per definition (`timestamps: false`) | R adapter |
| 8 | **Soft delete** | If the definition has `softDelete: true`: delete sets `deletedAt` and `deletedBy`, default reads exclude deleted entities, and restore clears both. If `false`, delete is permanent [Proposed; Laravel has the fields but no behavior] | `withDeleted` / `onlyDeleted` read options | R adapter |
| 9 | **Validation integration** | Before create/update the repository evaluates attribute constraints plus entity-scoped ValidationDefinitions. Error-level issues abort the write with `ValidationError` | Warnings returned to caller | F semantics. R engine |
| 10 | **Lifecycle hooks** | Events `beforeCreate`, `afterCreate`, `beforeUpdate`, `afterUpdate`, `beforeDelete`, `afterDelete`, `beforeRestore`, `afterRestore`. `before*` MAY veto by raising a categorized error. `after*` are observational [Proposed; names match Laravel `BaseModelAbstract` hooks] | Handlers via LifecycleManager | F: names and order. R: dispatch |
| 11 | **Audit** | If `audit: true`: `createdBy`, `updatedBy` and `deletedBy` come from `ExecutionContext.actor.id`, **never from the payload** | Change history (before/after diff) through `AuditSink` | F. R adapter. A storage |
| 12 | **Metadata** | Definitions carry metadata (id, version, owner, tags) | Instance metadata bag (Laravel `HasMetadata`) is runtime-specific | — |
| 13 | **Serialization** | Canonical JSON: `{entityType, entityId, revision, attributes:{…}, createdAt, createdBy, updatedAt, updatedBy, deletedAt, deletedBy}`, camelCase. `sensitive` attributes excluded unless explicitly requested | Runtime-native forms (Eloquent `toArray`, snake_case) | F canonical. R native |
| 14 | **Concurrency** | `revision` is an integer, starting at 1 and incremented on every persisted change. `save` with a stale revision fails with `ConcurrencyError` (spec 0005 §21). *Revision ≠ definition `version` (SemVer)* | Pessimistic locking (runtime-specific) | R adapter |
| 15 | **Persistence responsibility** | Entities have **no** persistence methods in the core contract. Persistence happens only through a Repository | Active-record convenience in Laravel (Eloquent `save()`) is runtime-specific. It must still route through the same lifecycle, audit and tenancy rules | — |
| 16 | **Repository abstraction** | Port operations: `findById(id, ctx)`, `getById(id, ctx)` (→ `ResourceNotFound`), `save(entity, ctx)`, `delete(id, ctx)`, `restore(id, ctx)`, `findBy(criteria, page, sort, ctx)` with equality criteria only. All operations are tenant-scoped through `ctx` | Rich queries are runtime-specific | F port. R adapters |
| 17 | **Transactions** | A `TransactionManager` port: `run(ctx, fn)`. If configured, the BF pipeline steps *Execution* + *State changes* run inside one transaction. Business events are published **after** commit | Outbox pattern | F boundary. R adapter |

## 4. Options for "BaseModel"

| Option | Description | Assessment |
|---|---|---|
| **A. Conceptual base class, same inheritance in both runtimes** | Both runtimes ship `BaseModel` and entities extend it | ✗ PHP single inheritance prevents extending both `Illuminate\Database\Eloquent\Model` and a WBF base. Node/Prisma entities are generated types or plain objects. Contradicts spec 0003 AP-03 "Composition Over Inheritance" |
| **B. Runtime-specific base class only** | Each runtime invents its own base, with no shared contract | ✗ No parity guarantee. Repeats today's divergence |
| **C. Separate abstractions: conceptual contract + runtime realizations** | Spec defines the Entity contract (§3) and Repository/Transaction ports. Each runtime provides idiomatic realizations: interfaces, traits/mixins, and adapters | ✔ Satisfies ORM independence. Allows Eloquent and Prisma. Testable via conformance |
| **D. Make Laravel `BaseModel` extend Eloquent `Model`** | Retrofit the existing class | ✗ Couples core to Eloquent. Breaks Module/BF/Workflow models that extend it today. Violates the "no ORM in core" requirement |

**Recommendation: Option C.** "BaseModel" is dropped from the conceptual vocabulary. The Laravel class keeps its name and API, and is documented as the meta-model artifact base.

## 5. Runtime realizations (proposed, not implemented)

### 5.1 Laravel

| Concern | Proposal | Notes |
|---|---|---|
| Entity contract | Interface `Contracts\WbfEntity` (identity, type, revision, audit getters) + trait `Concerns\InteractsWithWbfEntity` for **Eloquent models** (composition, not inheritance) | Apps keep `extends Model` |
| Non-Eloquent entities | Plain PHP classes implementing `WbfEntity`. The existing `Types\EntityInterface` stays the minimal parent | — |
| Eloquent adapter | `Persistence\Eloquent\EloquentEntityRepository implements EntityRepository` | Maps soft delete to `SoftDeletes`, timestamps to Eloquent timestamps, revision to a `revision` column with `where revision = ?` update |
| Lifecycle integration | Bridge Eloquent model events (`creating`, `updating`, `deleting`, `restoring`) to WBF lifecycle events | Hooks fire once, from the repository *or* Eloquent events, never both. Design decision required |
| Mass assignment | Writable attributes come from EntityDefinition. Adapter uses `forceFill` on an allow-listed subset. Audit fields are never fillable | Fixes LAR-06 pattern |
| Transactions | `Persistence\Eloquent\DatabaseTransactionManager` → `DB::transaction()` | — |
| Validation | Entity constraints compiled to Laravel Validator rules (VALIDATION_ARCHITECTURE §6.1) | — |
| Tenancy | Global scope applied by the adapter from `ExecutionContext.tenant` | — |
| Dependency | Needs `illuminate/database`. **Must not become mandatory** (restriction). Use `suggest` + `require-dev`, or a separate `waysnx/business-framework-eloquent` package | Decision required |

### 5.2 Node.js

| Concern | Proposal | Notes |
|---|---|---|
| Entity contract | Interface `Entity` (`entityType`, `entityId`, `revision`, audit fields) + `EntityDefinition` extended with `attributes`, `softDelete`, `audit`, `timestamps` | Plain objects. No base class required |
| Optional helper | `createEntity(definition, data, ctx)` applies defaults, filters undeclared attributes and validates | Composition |
| Repository port | `EntityRepository<T>` interface + `InMemoryEntityRepository` (reference / test adapter; zero dependencies) | The in-memory adapter doubles as the conformance reference |
| Prisma adapter | Separate package `@waysnx/business-framework-prisma`, with `@prisma/client` as a **peer** dependency | Keeps core at zero runtime dependencies |
| Lifecycle | Repository dispatches entity lifecycle events through `LifecycleManager` | — |
| Transactions | `TransactionManager` port. Prisma adapter uses `$transaction(fn)` and passes the transactional client via the context | — |
| Validation | `ValidationFramework` evaluators (VALIDATION_ARCHITECTURE §6.2) | — |

## 6. Migration implications

| Area | Impact | Breaking? |
|---|---|---|
| Laravel `BaseModelAbstract` / `BaseModel` | Docblock clarification only | No |
| Laravel `Types\EntityInterface` | Unchanged. New `WbfEntity` extends it | No |
| Laravel `getEntityType()` returns FQCN | Canonical `entityType` = EntityDefinition id. Add a mapping in the adapter and keep the method | No |
| Laravel `Repositories\BaseRepository` | Deprecate (it never persisted). Point to the new adapter | Deprecation only |
| Laravel container binding `RepositoryInterface → BaseRepository` | Unresolvable today (constructor needs `$model`). Remove in a later minor | Technically yes. Practically it never worked |
| Laravel `Registry\EntityDefinition` | Add optional `attributes`, `softDelete`, `audit`, `timestamps`, `moduleId` named parameters **at the end** of the constructor | No |
| Node `EntityDefinition` | Add optional fields. `version` number → SemVer string is breaking (see CORE_CONTRACTS §10) | Version type: yes (needs approval) |
| Node `AuditableInterface` | Add deleted fields as optional members | No |
| Node `VersionableInterface.getVersion()` | Keep. Introduce `revision` on `Entity` | No |
| Database schemas | **None changed by this assignment.** Adapters will require `revision` and soft-delete columns *in host applications* when adopted | — |

## 7. Open decisions (for Umesh)

1. Should the Eloquent adapter live in the Laravel package (`suggest`) or in a separate package?
2. Should canonical `entityId` allow integer auto-increment keys (serialized as strings) or require UUIDs?
3. Should Laravel lifecycle hooks be driven by Eloquent model events or only by the repository? Supporting Eloquent `save()` outside the repository weakens the contract.
4. Should Domain and Capability become runtime artifacts? This affects whether EntityDefinition must reference a Domain.
5. Should the Node Prisma adapter be in scope for the next milestone, or the in-memory reference adapter only?
