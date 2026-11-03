---
documentId: WBF-PLAN-0003
title: WBF Document Registry
status: Approved – identifier scheme approved as D11 (Umesh Wani, 2026-11-01)
version: 0.2.0
date: 2026-11-01
---

# WBF Document Registry

This registry is the single list of WBF document identifiers. It lives in the existing governance directory `docs/00-governance/`; there is no `docs/governance/` directory.

## 1. Identifier scheme

| Prefix | Used for | Location | Example |
|---|---|---|---|
| `WBF-SPEC-NNNN` | **Normative** specifications (MUST/SHALL) | `specification/**` | `WBF-SPEC-0005` Business Function Specification |
| `WBF-DOC-NNNN` | Descriptive architecture and guidance documents | `docs/NN-*/` | `WBF-DOC-0013` Business Entity Specification (descriptive) |
| `ADR-NNNN` (alias `WBF-ADR-NNNN`) | Architecture Decision Records. Numbers are **not** renumbered | `decisions/` | `ADR-0006` |
| `WBF-PLAN-NNNN` | Plans, registers, roadmaps-in-detail | `docs/**` | `WBF-PLAN-0001` Phase 0 Decision Register |
| `WBF-AUDIT-NNNN` | Audit and completion reports | `docs/audit/` | `WBF-AUDIT-0001` Repository Audit |
| `WBF-ARCH-*` | Architecture working proposals (pre-specification) | `docs/architecture/` | `WBF-ARCH-ENT-0001` Entity Architecture |

Rules:

1. Every identifier is unique across the repository. The Phase 0 check found 157 identified documents and 0 duplicates.
2. Identifiers are **never silently reused**. A retired identifier keeps a registry entry marked *Retired* or *Moved*.
3. ADR numbers are permanent. A superseded ADR stays and links to its successor.
4. Runtime repositories (`business-framework-laravel`, `business-framework-node`) do **not** mint `WBF-*` identifiers. Their documents are referenced by path.
5. New normative specifications continue at `WBF-SPEC-0008` (0006 and 0007 assigned in Phase 0.1).

## 2. Migration map (Phase 0)

Before Phase 0, the identifiers `WBF-DOC-0000` … `WBF-DOC-0005` were used by **two different sets of documents** (finding PRI-03).

| Legacy identifier | Meaning A: `specification/foundation/` (normative) → **new ID** | Meaning B: `docs/` (descriptive) → **unchanged** |
|---|---|---|
| WBF-DOC-0000 | Specification Writing Standard → **WBF-SPEC-0000** | Documentation Standards (stub) |
| WBF-DOC-0001 | WBF Manifesto → **WBF-SPEC-0001** | Introduction (stub) |
| WBF-DOC-0002 | Terminology and Glossary → **WBF-SPEC-0002** | Core Principles (stub) |
| WBF-DOC-0003 | Core Principles → **WBF-SPEC-0003** | Architecture Overview (stub) |
| WBF-DOC-0004 | Core Architecture → **WBF-SPEC-0004** | Business Architecture |
| WBF-DOC-0005 | Business Function Specification → **WBF-SPEC-0005** | Meta Model |

Why the normative set moved, rather than the descriptive set:

- The normative documents are the ones the user directive asked to identify as `WBF-SPEC`.
- The descriptive series `WBF-DOC-0000…0140` is contiguous, and renumbering six of its members would break that sequence.
- Every reference to `WBF-DOC-0000…0005` in runtime code and inside the normative specs meant the normative documents. Updating those references removes the ambiguity at the source.

Changes executed (uncommitted, reversible):

| Change | Detail |
|---|---|
| File rename | `specification/foundation/WBF-DOC-000N.md` → `specification/foundation/WBF-SPEC-000N.md` (N = 0…5). Content unchanged apart from identifiers. Git rename detection preserves history on commit |
| Front-matter | `id: WBF-SPEC-000N` plus `legacyId: WBF-DOC-000N` |
| Internal references | Updated in the six specs, Laravel `src/Core/{BusinessFunctionAbstract,DomainAbstract,BusinessCapabilityAbstract}.php`, `src/Types/BusinessFunctionInterface.php`, `examples/ApplyLeave.php`, three Laravel test docblocks, and Phase 0 architecture docs and ADRs |
| Not changed (by design) | `implementations/laravel/**` and `packages/**` (frozen copies at the time, see ADR-0005; removed in Phase 0.1). `docs/audit/REPOSITORY_AUDIT.md` (historical snapshot, uses the legacy IDs as of the audit) |

**Interpretation rule for legacy references.** In frozen copies, the audit snapshot and anything written before 2026-10-31, `WBF-DOC-000N` (N ≤ 5) next to a normative requirement ID (BF-*, OR-*, DR-*, AP-*, BP-*) or a `specification/foundation` path means `WBF-SPEC-000N`.

### 2.1 Unresolvable legacy references (need human judgment)

These references cite titles that match **neither** meaning. They were left unchanged rather than guessed:

| File | Line | Text |
|---|---|---|
| `docs/08-ai-automation/WBF-DOC-0071-AI-Automation-Architecture.md` | 419 | "WBF-DOC-0001 – WaysNX Business Framework Overview" |
| `docs/11-governance-compliance/WBF-DOC-0101-Governance-Architecture.md` | 364 | "WBF-DOC-0001 – Enterprise Architecture Principles" |
| `docs/11-governance-compliance/WBF-DOC-0101-Governance-Architecture.md` | 365 | "WBF-DOC-0002 – Enterprise Architecture Framework" |
| `docs/12-reference-models/WBF-DOC-0111-Business-Capability-Reference-Model.md` | 471 | "WBF-DOC-0002 – Business Architecture" |
| `docs/12-reference-models/WBF-DOC-0111-Business-Capability-Reference-Model.md` | 472 | "WBF-DOC-0004 – Enterprise Architecture" |

## 3. Normative specifications (`WBF-SPEC`)

| ID | Title | Path | Status |
|---|---|---|---|
| WBF-SPEC-0000 | Specification Writing Standard | `specification/foundation/WBF-SPEC-0000.md` | Draft v0.1.0 |
| WBF-SPEC-0001 | WBF Manifesto | `specification/foundation/WBF-SPEC-0001.md` | Draft v0.1.0 |
| WBF-SPEC-0002 | Terminology and Glossary | `specification/foundation/WBF-SPEC-0002.md` | Draft v1.0.0 |
| WBF-SPEC-0003 | Core Principles | `specification/foundation/WBF-SPEC-0003.md` | Draft v1.0.0 |
| WBF-SPEC-0004 | Core Architecture | `specification/foundation/WBF-SPEC-0004.md` | Draft v1.0.0 |
| WBF-SPEC-0005 | Business Function Specification | `specification/foundation/WBF-SPEC-0005.md` | Draft v1.0.0 |
| WBF-SPEC-0006 | Business Function Execution Result | `specification/runtime/WBF-SPEC-0006.md` | Draft v0.1.0 (encodes approved D1) |
| WBF-SPEC-0007 | Workflow Step Sequencing | `specification/runtime/WBF-SPEC-0007.md` | Draft v0.1.0 (encodes approved D8) |

## 4. Architecture Decision Records

| ID | Alias | Title | Status |
|---|---|---|---|
| ADR-0001 | WBF-ADR-0001 | Document Numbering | Stub (Planned) |
| ADR-0002 | WBF-ADR-0002 | Business First Model | Stub (Planned) |
| ADR-0003 | WBF-ADR-0003 | Meta Model Relationships | Stub (Planned) |
| ADR-0004 | WBF-ADR-0004 | Documentation Standards | Stub (Planned) |
| ADR-0005 | WBF-ADR-0005 | Repository Ownership and Canonical Sources | Accepted (2026-11-01) |
| ADR-0006 | WBF-ADR-0006 | Entity Contract and Runtime Realization | Proposed (implementation blocked) |
| ADR-0007 | WBF-ADR-0007 | Validation Responsibility Split | Accepted in part (M0 behavior, 2026-11-01) |
| ADR-0008 | WBF-ADR-0008 | Shared Conformance Suite | Accepted (2026-11-01) |
| ADR-0009 | WBF-ADR-0009 | Business Function Result Contract | Accepted (2026-11-01) |
| ADR-0010 | WBF-ADR-0010 | Fail-Closed Authorization | Accepted (2026-11-01) |

Note: ADR-0001 "Document Numbering" is a stub. This registry is a candidate for its content, pending approval.

## 5. Plans, audits and architecture working documents

| ID | Title | Path |
|---|---|---|
| WBF-PLAN-0001 | Phase 0 Decision Register | `docs/architecture/PHASE_0_DECISION_REGISTER.md` |
| WBF-PLAN-0003 | WBF Document Registry (this document) | `docs/00-governance/DOCUMENT_REGISTRY.md` |
| WBF-AUDIT-0001 | WBF Foundation Repository Audit | `docs/audit/REPOSITORY_AUDIT.md` |
| WBF-AUDIT-0002 | Phase 0 Completion Report | `docs/audit/PHASE_0_COMPLETION_REPORT.md` |
| WBF-AUDIT-0003 | Repository Cleanup Report (ADR-0005 R2–R4) | `docs/audit/REPOSITORY_CLEANUP_REPORT.md` |
| WBF-AUDIT-0004 | Security Follow-up Report | `docs/audit/SECURITY_FOLLOW_UP.md` |
| WBF-AUDIT-0005 | Phase 0.1 Completion Report | `docs/audit/PHASE_0_1_COMPLETION_REPORT.md` |
| WBF-AUDIT-0006 | Phase 0.1 Release-Readiness Review Package | `docs/audit/RELEASE_READINESS_REVIEW.md` |
| WBF-ARCH-OWN-0001 | Repository Ownership Specification | `docs/architecture/REPOSITORY_OWNERSHIP.md` |
| WBF-ARCH-CC-0001 | Core Contract Consolidation | `docs/architecture/CORE_CONTRACTS.md` |
| WBF-ARCH-ENT-0001 | Entity and BaseModel Architecture | `docs/architecture/ENTITY_ARCHITECTURE.md` |
| WBF-ARCH-VAL-0001 | Validation Architecture | `docs/architecture/VALIDATION_ARCHITECTURE.md` |
| WBF-ARCH-CAP-0001 | Implementation Capability Matrix | `docs/architecture/IMPLEMENTATION_CAPABILITY_MATRIX.md` |
| WBF-ARCH-CONF-0001 | Cross-Runtime Conformance Strategy | `docs/architecture/CROSS_RUNTIME_CONFORMANCE.md` |
| WBF-ARCH-HIER-0001 | Canonical Conceptual Hierarchy | `docs/architecture/CONCEPTUAL_HIERARCHY.md` |

`WBF-PLAN-0002` is intentionally unassigned. It was briefly used as a draft identifier for the decision register during Phase 0 and is retired so that it is not reused with a different meaning.

## 6. Descriptive documents (`WBF-DOC`)

Status was derived mechanically: "Stub" means a file under 400 bytes, which holds only the placeholder template. All other documents carry `status: Draft` or no status.

| ID | Title | Path | Status |
|---|---|---|---|
| WBF-DOC-0000 | Documentation Standards | `docs/00-governance/WBF-DOC-0000-Documentation-Standards.md` | Stub |
| WBF-DOC-0001 | Introduction | `docs/01-foundation/WBF-DOC-0001-Introduction.md` | Stub |
| WBF-DOC-0002 | Core Principles | `docs/01-foundation/WBF-DOC-0002-Core-Principles.md` | Stub |
| WBF-DOC-0003 | Architecture Overview | `docs/01-foundation/WBF-DOC-0003-Architecture-Overview.md` | Stub |
| WBF-DOC-0004 | Business Architecture | `docs/02-business-architecture/WBF-DOC-0004-Business-Architecture.md` | Draft |
| WBF-DOC-0005 | Meta Model | `docs/02-business-architecture/WBF-DOC-0005-Meta-Model.md` | Draft |
| WBF-DOC-0006 | Module Specification | `docs/02-business-architecture/WBF-DOC-0006-Module-Specification.md` | Draft |
| WBF-DOC-0007 | Domain Specification | `docs/02-business-architecture/WBF-DOC-0007-Domain-Specification.md` | Draft |
| WBF-DOC-0008 | Business Capability Specification | `docs/02-business-architecture/WBF-DOC-0008-Business-Capability-Specification.md` | Draft |
| WBF-DOC-0009 | Workflow Specification | `docs/02-business-architecture/WBF-DOC-0009-Workflow-Specification.md` | Draft |
| WBF-DOC-0010 | Service Specification | `docs/02-business-architecture/WBF-DOC-0010-Service-Specification.md` | Draft |
| WBF-DOC-0011 | Runtime Specification | `docs/02-business-architecture/WBF-DOC-0011-Runtime-Specification.md` | Draft |
| WBF-DOC-0012 | Business Object Specification | `docs/02-business-architecture/WBF-DOC-0012-Business-Object-Specification.md` | Draft |
| WBF-DOC-0013 | Business Entity Specification | `docs/02-business-architecture/WBF-DOC-0013-Business-Entity-Specification.md` | Draft |
| WBF-DOC-0014 | Business Rule Specification | `docs/02-business-architecture/WBF-DOC-0014-Business-Rule-Specification.md` | Draft |
| WBF-DOC-0015 | Business Policy Specification | `docs/02-business-architecture/WBF-DOC-0015-Business-Policy-Specification.md` | Draft |
| WBF-DOC-0016 | Business Event Specification | `docs/02-business-architecture/WBF-DOC-0016-Business-Event-Specification.md` | Draft |
| WBF-DOC-0017 | Business Command Specification | `docs/02-business-architecture/WBF-DOC-0017-Business-Command-Specification.md` | Draft |
| WBF-DOC-0018 | Business Query Specification | `docs/02-business-architecture/WBF-DOC-0018-Business-Query-Specification.md` | Draft |
| WBF-DOC-0019 | Business Decision Specification | `docs/02-business-architecture/WBF-DOC-0019-Business-Decision-Specification.md` | Draft |
| WBF-DOC-0020 | Business Goal Specification | `docs/02-business-architecture/WBF-DOC-0020-Business-Goal-Specification.md` | Draft |
| WBF-DOC-0021 | Business KPI Specification | `docs/02-business-architecture/WBF-DOC-0021-Business-KPI-Specification.md` | Draft |
| WBF-DOC-0022 | Business Actor Specification | `docs/02-business-architecture/WBF-DOC-0022-Business-Actor-Specification.md` | Draft |
| WBF-DOC-0023 | Business Role Specification | `docs/02-business-architecture/WBF-DOC-0023-Business-Role-Specification.md` | Draft |
| WBF-DOC-0024 | Integration Architecture | `docs/03-integration-architecture/WBF-DOC-0024-Integration-Architecture.md` | Draft |
| WBF-DOC-0025 | API Specification | `docs/03-integration-architecture/WBF-DOC-0025-API-Specification.md` | Draft |
| WBF-DOC-0026 | Messaging Event Streaming Specification | `docs/03-integration-architecture/WBF-DOC-0026-Messaging-Event-Streaming-Specification.md` | Draft |
| WBF-DOC-0027 | Data Exchange Specification | `docs/03-integration-architecture/WBF-DOC-0027-Data-Exchange-Specification.md` | Draft |
| WBF-DOC-0028 | Integration Patterns | `docs/03-integration-architecture/WBF-DOC-0028-Integration-Patterns.md` | Draft |
| WBF-DOC-0029 | External System Integration | `docs/03-integration-architecture/WBF-DOC-0029-External-System-Integration.md` | Draft |
| WBF-DOC-0030 | API Versioning Compatibility | `docs/03-integration-architecture/WBF-DOC-0030-API-Versioning-Compatibility.md` | Draft |
| WBF-DOC-0031 | Implementation Architecture | `docs/04-implementation/WBF-DOC-0031-Implementation-Architecture.md` | Draft |
| WBF-DOC-0032 | Application Architecture | `docs/04-implementation/WBF-DOC-0032-Application-Architecture.md` | Draft |
| WBF-DOC-0033 | Service Architecture | `docs/04-implementation/WBF-DOC-0033-Service-Architecture.md` | Draft |
| WBF-DOC-0034 | Component Architecture | `docs/04-implementation/WBF-DOC-0034-Component-Architecture.md` | Draft |
| WBF-DOC-0035 | Data Persistence Architecture | `docs/04-implementation/WBF-DOC-0035-Data-Persistence-Architecture.md` | Draft |
| WBF-DOC-0036 | Configuration Management | `docs/04-implementation/WBF-DOC-0036-Configuration-Management.md` | Draft |
| WBF-DOC-0037 | Dependency Management | `docs/04-implementation/WBF-DOC-0037-Dependency-Management.md` | Draft |
| WBF-DOC-0038 | Deployment Architecture | `docs/04-implementation/WBF-DOC-0038-Deployment-Architecture.md` | Draft |
| WBF-DOC-0039 | Environment Management | `docs/04-implementation/WBF-DOC-0039-Environment-Management.md` | Draft |
| WBF-DOC-0040 | Runtime Architecture | `docs/04-implementation/WBF-DOC-0040-Runtime-Architecture.md` | Draft |
| WBF-DOC-0041 | Security Architecture | `docs/05-security/WBF-DOC-0041-Security-Architecture.md` | Draft |
| WBF-DOC-0042 | Identity Access Management | `docs/05-security/WBF-DOC-0042-Identity-Access-Management.md` | Draft |
| WBF-DOC-0043 | Authentication Architecture | `docs/05-security/WBF-DOC-0043-Authentication-Architecture.md` | Draft |
| WBF-DOC-0044 | Authorization Architecture | `docs/05-security/WBF-DOC-0044-Authorization-Architecture.md` | Draft |
| WBF-DOC-0045 | Cryptography Key Management | `docs/05-security/WBF-DOC-0045-Cryptography-Key-Management.md` | Draft |
| WBF-DOC-0046 | Secrets Credential Management | `docs/05-security/WBF-DOC-0046-Secrets-Credential-Management.md` | Draft |
| WBF-DOC-0047 | Audit Logging Architecture | `docs/05-security/WBF-DOC-0047-Audit-Logging-Architecture.md` | Draft |
| WBF-DOC-0048 | Security Monitoring Incident Response | `docs/05-security/WBF-DOC-0048-Security-Monitoring-Incident-Response.md` | Draft |
| WBF-DOC-0049 | Data Privacy Protection | `docs/05-security/WBF-DOC-0049-Data-Privacy-Protection.md` | Draft |
| WBF-DOC-0050 | Security Governance Compliance | `docs/05-security/WBF-DOC-0050-Security-Governance-Compliance.md` | Draft |
| WBF-DOC-0051 | Data Architecture | `docs/06-data/WBF-DOC-0051-Data-Architecture.md` | Draft |
| WBF-DOC-0052 | Data Modeling | `docs/06-data/WBF-DOC-0052-Data-Modeling.md` | Draft |
| WBF-DOC-0053 | Data Storage Architecture | `docs/06-data/WBF-DOC-0053-Data-Storage-Architecture.md` | Draft |
| WBF-DOC-0054 | Data Integration | `docs/06-data/WBF-DOC-0054-Data-Integration.md` | Draft |
| WBF-DOC-0055 | Master Data Management | `docs/06-data/WBF-DOC-0055-Master-Data-Management.md` | Draft |
| WBF-DOC-0056 | Metadata Management | `docs/06-data/WBF-DOC-0056-Metadata-Management.md` | Draft |
| WBF-DOC-0057 | Data Quality | `docs/06-data/WBF-DOC-0057-Data-Quality.md` | Draft |
| WBF-DOC-0058 | Data Lifecycle Management | `docs/06-data/WBF-DOC-0058-Data-Lifecycle-Management.md` | Draft |
| WBF-DOC-0059 | Data Analytics Architecture | `docs/06-data/WBF-DOC-0059-Data-Analytics-Architecture.md` | Draft |
| WBF-DOC-0060 | Data Governance | `docs/06-data/WBF-DOC-0060-Data-Governance.md` | Draft |
| WBF-DOC-0061 | Presentation Architecture | `docs/07-presentation/WBF-DOC-0061-Presentation-Architecture.md` | Draft |
| WBF-DOC-0062 | User Experience Architecture | `docs/07-presentation/WBF-DOC-0062-User-Experience-Architecture.md` | Draft |
| WBF-DOC-0063 | User Interface Architecture | `docs/07-presentation/WBF-DOC-0063-User-Interface-Architecture.md` | Draft |
| WBF-DOC-0064 | Design System Architecture | `docs/07-presentation/WBF-DOC-0064-Design-System-Architecture.md` | Draft |
| WBF-DOC-0065 | Accessibility Architecture | `docs/07-presentation/WBF-DOC-0065-Accessibility-Architecture.md` | Draft |
| WBF-DOC-0066 | Responsive Adaptive Design | `docs/07-presentation/WBF-DOC-0066-Responsive-Adaptive-Design.md` | Draft |
| WBF-DOC-0067 | Client Application Architecture | `docs/07-presentation/WBF-DOC-0067-Client-Application-Architecture.md` | Draft |
| WBF-DOC-0068 | Visualization Architecture | `docs/07-presentation/WBF-DOC-0068-Visualization-Architecture.md` | Draft |
| WBF-DOC-0069 | Internationalization Localization | `docs/07-presentation/WBF-DOC-0069-Internationalization-Localization.md` | Draft |
| WBF-DOC-0070 | Presentation Governance | `docs/07-presentation/WBF-DOC-0070-Presentation-Governance.md` | Draft |
| WBF-DOC-0071 | AI Automation Architecture | `docs/08-ai-automation/WBF-DOC-0071-AI-Automation-Architecture.md` | Draft |
| WBF-DOC-0072 | AI Agent Architecture | `docs/08-ai-automation/WBF-DOC-0072-AI-Agent-Architecture.md` | Draft |
| WBF-DOC-0073 | Agentic Workflow Architecture | `docs/08-ai-automation/WBF-DOC-0073-Agentic-Workflow-Architecture.md` | Draft |
| WBF-DOC-0074 | Prompt Engineering Architecture | `docs/08-ai-automation/WBF-DOC-0074-Prompt-Engineering-Architecture.md` | Draft |
| WBF-DOC-0075 | Knowledge Management Architecture | `docs/08-ai-automation/WBF-DOC-0075-Knowledge-Management-Architecture.md` | Draft |
| WBF-DOC-0076 | RAG Architecture | `docs/08-ai-automation/WBF-DOC-0076-RAG-Architecture.md` | Draft |
| WBF-DOC-0077 | Model Governance | `docs/08-ai-automation/WBF-DOC-0077-Model-Governance.md` | Draft |
| WBF-DOC-0078 | AI Integration Architecture | `docs/08-ai-automation/WBF-DOC-0078-AI-Integration-Architecture.md` | Draft |
| WBF-DOC-0079 | AI Safety and Responsible AI | `docs/08-ai-automation/WBF-DOC-0079-AI-Safety-and-Responsible-AI.md` | Draft |
| WBF-DOC-0080 | AI Governance | `docs/08-ai-automation/WBF-DOC-0080-AI-Governance.md` | Draft |
| WBF-DOC-0081 | Quality Architecture | `docs/09-quality/WBF-DOC-0081-Quality-Architecture.md` | Draft |
| WBF-DOC-0082 | Quality Assurance Architecture | `docs/09-quality/WBF-DOC-0082-Quality-Assurance-Architecture.md` | Draft |
| WBF-DOC-0083 | Quality Engineering Architecture | `docs/09-quality/WBF-DOC-0083-Quality-Engineering-Architecture.md` | Draft |
| WBF-DOC-0084 | Testing Architecture | `docs/09-quality/WBF-DOC-0084-Testing-Architecture.md` | Draft |
| WBF-DOC-0085 | Test Automation Architecture | `docs/09-quality/WBF-DOC-0085-Test-Automation-Architecture.md` | Draft |
| WBF-DOC-0086 | Performance and Reliability Architecture | `docs/09-quality/WBF-DOC-0086-Performance-and-Reliability-Architecture.md` | Draft |
| WBF-DOC-0087 | Observability and Monitoring Architecture | `docs/09-quality/WBF-DOC-0087-Observability-and-Monitoring-Architecture.md` | Draft |
| WBF-DOC-0088 | DevSecOps and Continuous Quality | `docs/09-quality/WBF-DOC-0088-DevSecOps-and-Continuous-Quality.md` | Draft |
| WBF-DOC-0089 | Quality Metrics and Measurement | `docs/09-quality/WBF-DOC-0089-Quality-Metrics-and-Measurement.md` | Draft |
| WBF-DOC-0090 | Quality Governance | `docs/09-quality/WBF-DOC-0090-Quality-Governance.md` | Draft |
| WBF-DOC-0091 | Operations Architecture | `docs/10-operations/WBF-DOC-0091-Operations-Architecture.md` | Draft |
| WBF-DOC-0092 | Service Management Architecture | `docs/10-operations/WBF-DOC-0092-Service-Management-Architecture.md` | Draft |
| WBF-DOC-0093 | Site Reliability Engineering | `docs/10-operations/WBF-DOC-0093-Site-Reliability-Engineering.md` | Draft |
| WBF-DOC-0094 | Incident Management Architecture | `docs/10-operations/WBF-DOC-0094-Incident-Management-Architecture.md` | Draft |
| WBF-DOC-0095 | Change Release Management | `docs/10-operations/WBF-DOC-0095-Change-Release-Management.md` | Draft |
| WBF-DOC-0096 | Business Continuity Disaster Recovery | `docs/10-operations/WBF-DOC-0096-Business-Continuity-Disaster-Recovery.md` | Draft |
| WBF-DOC-0097 | Configuration Asset Management | `docs/10-operations/WBF-DOC-0097-Configuration-Asset-Management.md` | Draft |
| WBF-DOC-0098 | Platform Operations | `docs/10-operations/WBF-DOC-0098-Platform-Operations.md` | Draft |
| WBF-DOC-0099 | Operational Metrics Measurement | `docs/10-operations/WBF-DOC-0099-Operational-Metrics-Measurement.md` | Draft |
| WBF-DOC-0100 | Operations Governance | `docs/10-operations/WBF-DOC-0100-Operations-Governance.md` | Draft |
| WBF-DOC-0101 | Governance Architecture | `docs/11-governance-compliance/WBF-DOC-0101-Governance-Architecture.md` | Draft |
| WBF-DOC-0102 | Risk Management Architecture | `docs/11-governance-compliance/WBF-DOC-0102-Risk-Management-Architecture.md` | Draft |
| WBF-DOC-0103 | Compliance Architecture | `docs/11-governance-compliance/WBF-DOC-0103-Compliance-Architecture.md` | Draft |
| WBF-DOC-0104 | Policy Management | `docs/11-governance-compliance/WBF-DOC-0104-Policy-Management.md` | Draft |
| WBF-DOC-0105 | Enterprise Audit Architecture | `docs/11-governance-compliance/WBF-DOC-0105-Enterprise-Audit-Architecture.md` | Draft |
| WBF-DOC-0106 | Regulatory Compliance | `docs/11-governance-compliance/WBF-DOC-0106-Regulatory-Compliance.md` | Draft |
| WBF-DOC-0107 | Business Ethics Architecture | `docs/11-governance-compliance/WBF-DOC-0107-Business-Ethics-Architecture.md` | Draft |
| WBF-DOC-0108 | Decision Governance | `docs/11-governance-compliance/WBF-DOC-0108-Decision-Governance.md` | Draft |
| WBF-DOC-0109 | Enterprise Maturity Model | `docs/11-governance-compliance/WBF-DOC-0109-Enterprise-Maturity-Model.md` | Draft |
| WBF-DOC-0110 | Governance Framework | `docs/11-governance-compliance/WBF-DOC-0110-Governance-Framework.md` | Draft |
| WBF-DOC-0111 | Business Capability Reference Model | `docs/12-reference-models/WBF-DOC-0111-Business-Capability-Reference-Model.md` | Draft |
| WBF-DOC-0112 | Business Process Reference Model | `docs/12-reference-models/WBF-DOC-0112-Business-Process-Reference-Model.md` | Draft |
| WBF-DOC-0113 | Enterprise Application Reference Model | `docs/12-reference-models/WBF-DOC-0113-Enterprise-Application-Reference-Model.md` | Draft |
| WBF-DOC-0114 | Technology Reference Model | `docs/12-reference-models/WBF-DOC-0114-Technology-Reference-Model.md` | Draft |
| WBF-DOC-0115 | Integration Reference Model | `docs/12-reference-models/WBF-DOC-0115-Integration-Reference-Model.md` | Draft |
| WBF-DOC-0116 | Information Data Reference Model | `docs/12-reference-models/WBF-DOC-0116-Information-Data-Reference-Model.md` | Draft |
| WBF-DOC-0117 | Security Reference Model | `docs/12-reference-models/WBF-DOC-0117-Security-Reference-Model.md` | Draft |
| WBF-DOC-0118 | Cloud Infrastructure Reference Model | `docs/12-reference-models/WBF-DOC-0118-Cloud-Infrastructure-Reference-Model.md` | Draft |
| WBF-DOC-0119 | AI Automation Reference Model | `docs/12-reference-models/WBF-DOC-0119-AI-Automation-Reference-Model.md` | Draft |
| WBF-DOC-0120 | Enterprise Reference Architecture | `docs/12-reference-models/WBF-DOC-0120-Enterprise-Reference-Architecture.md` | Draft |
| WBF-DOC-0121 | Enterprise Architecture Development Guide | `docs/13-guides/WBF-DOC-0121-Enterprise-Architecture-Development-Guide.md` | Draft |
| WBF-DOC-0122 | Solution Architecture Guide | `docs/13-guides/WBF-DOC-0122-Solution-Architecture-Guide.md` | Draft |
| WBF-DOC-0123 | Architecture Review Guide | `docs/13-guides/WBF-DOC-0123-Architecture-Review-Guide.md` | Draft |
| WBF-DOC-0124 | Technology Selection Guide | `docs/13-guides/WBF-DOC-0124-Technology-Selection-Guide.md` | Draft |
| WBF-DOC-0125 | API Design Guide | `docs/13-guides/WBF-DOC-0125-API-Design-Guide.md` | Draft |
| WBF-DOC-0126 | Integration Design Guide | `docs/13-guides/WBF-DOC-0126-Integration-Design-Guide.md` | Draft |
| WBF-DOC-0127 | Data Architecture Guide | `docs/13-guides/WBF-DOC-0127-Data-Architecture-Guide.md` | Draft |
| WBF-DOC-0128 | Security Architecture Guide | `docs/13-guides/WBF-DOC-0128-Security-Architecture-Guide.md` | Draft |
| WBF-DOC-0129 | Cloud Architecture Guide | `docs/13-guides/WBF-DOC-0129-Cloud-Architecture-Guide.md` | Draft |
| WBF-DOC-0130 | AI Solution Architecture Guide | `docs/13-guides/WBF-DOC-0130-AI-Solution-Architecture-Guide.md` | Draft |
| WBF-DOC-0131 | Architecture Patterns Catalog | `docs/14-body-of-knowledge/WBF-DOC-0131-Architecture-Patterns-Catalog.md` | Draft |
| WBF-DOC-0132 | Integration Patterns Catalog | `docs/14-body-of-knowledge/WBF-DOC-0132-Integration-Patterns-Catalog.md` | Draft |
| WBF-DOC-0133 | Security Patterns Catalog | `docs/14-body-of-knowledge/WBF-DOC-0133-Security-Patterns-Catalog.md` | Draft |
| WBF-DOC-0134 | Cloud Patterns Catalog | `docs/14-body-of-knowledge/WBF-DOC-0134-Cloud-Patterns-Catalog.md` | Draft |
| WBF-DOC-0135 | Data Patterns Catalog | `docs/14-body-of-knowledge/WBF-DOC-0135-Data-Patterns-Catalog.md` | Draft |
| WBF-DOC-0136 | AI Patterns Catalog | `docs/14-body-of-knowledge/WBF-DOC-0136-AI-Patterns-Catalog.md` | Draft |
| WBF-DOC-0137 | Architecture Decision Records Guide | `docs/14-body-of-knowledge/WBF-DOC-0137-Architecture-Decision-Records-Guide.md` | Draft |
| WBF-DOC-0138 | Architecture Checklists | `docs/14-body-of-knowledge/WBF-DOC-0138-Architecture-Checklists.md` | Draft |
| WBF-DOC-0139 | Architecture Templates | `docs/14-body-of-knowledge/WBF-DOC-0139-Architecture-Templates.md` | Draft |
| WBF-DOC-0140 | Enterprise Architecture Body of Knowledge | `docs/14-body-of-knowledge/WBF-DOC-0140-Enterprise-Architecture-Body-of-Knowledge.md` | Draft |
