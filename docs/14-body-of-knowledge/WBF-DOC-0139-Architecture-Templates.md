---
documentId: WBF-DOC-0139
title: Architecture Templates
version: 1.0.0
status: Draft
owner: WaysNX Business Framework
category: Body of Knowledge
lastUpdated: 2026-07-22
---

# WBF-DOC-0139 – Architecture Templates

## Purpose

The Architecture Templates document defines the standard set of reusable templates used for documenting enterprise architecture, solution architecture, software architecture, APIs, integrations, data platforms, cloud infrastructure, security, AI solutions, and governance artifacts across the WaysNX ecosystem.

These templates promote consistency, improve documentation quality, accelerate project delivery, simplify architecture reviews, and enable AI-assisted generation of standardized architectural documentation.

This document complements the Architecture Decision Records Guide and Architecture Checklists by defining the official documentation formats for architectural deliverables.

---

# Table of Contents

1. Scope
2. Definitions
3. Objectives
4. Template Library Overview
5. Enterprise Architecture Templates
6. Solution Architecture Templates
7. Application Architecture Templates
8. API & Integration Templates
9. Data Architecture Templates
10. Security Templates
11. Cloud & Infrastructure Templates
12. AI Solution Templates
13. Governance Templates
14. Template Usage Guidelines
15. Best Practices
16. Anti-Patterns
17. Related WBF Documents
18. Version History

---

# 1. Scope

The Architecture Templates apply to:

- Enterprise Architecture
- Solution Architecture
- Software Architecture
- APIs
- Integration
- Data Platforms
- Cloud Platforms
- Security
- AI Solutions
- Governance Documentation

---

# 2. Definitions

## Architecture Template

A standardized document structure used to capture architecture-related information consistently across projects.

---

## Deliverable

A documented artifact produced during architecture, design, implementation, governance, or operational activities.

---

# 3. Objectives

The Architecture Templates should:

- Standardize architecture documentation
- Improve review quality
- Simplify governance
- Accelerate project delivery
- Enable document automation
- Improve traceability
- Support AI-assisted documentation
- Preserve architectural knowledge

---

# 4. Template Library Overview

The WBF standard template library includes:

- Enterprise Architecture Template
- Solution Architecture Template
- High-Level Design (HLD)
- Low-Level Design (LLD)
- Architecture Decision Record (ADR)
- API Specification
- Integration Specification
- Data Architecture
- Data Model
- Security Assessment
- Risk Assessment
- Technology Evaluation
- Cloud Architecture
- AI Solution Design
- Production Readiness Assessment

---

# 5. Enterprise Architecture Templates

## Enterprise Architecture Document

Sections include:

- Executive Summary
- Business Context
- Business Capabilities
- Enterprise Principles
- Current State
- Target State
- Capability Map
- Reference Models
- Roadmap
- Risks
- Governance

---

# 6. Solution Architecture Templates

## Solution Architecture

Sections include:

- Business Problem
- Functional Requirements
- Non-Functional Requirements
- Context Diagram
- Component Diagram
- Deployment Architecture
- Security
- Integration
- Data Architecture
- Risks
- ADR References

---

## High-Level Design (HLD)

Sections include:

- Overview
- Architecture Diagram
- Component Overview
- Technology Stack
- Integration
- Security
- Deployment

---

## Low-Level Design (LLD)

Sections include:

- Module Design
- Database Design
- APIs
- Interfaces
- Sequence Diagrams
- Error Handling
- Configuration
- Logging

---

# 7. Application Architecture Templates

Templates include:

- Module Design
- Component Specification
- UI Architecture
- Service Design
- Domain Model
- Event Model
- Plugin Design

Each template should include:

- Purpose
- Scope
- Architecture
- Dependencies
- Interfaces
- Risks
- Testing
- Operational Considerations

---

# 8. API & Integration Templates

Templates include:

- OpenAPI Specification
- API Contract
- Integration Specification
- Event Schema
- Webhook Specification
- Message Contract
- Consumer Contract
- API Security Assessment

---

# 9. Data Architecture Templates

Templates include:

- Logical Data Model
- Physical Data Model
- Master Data Design
- Metadata Specification
- Data Dictionary
- Data Governance Assessment
- Data Migration Plan
- Analytics Architecture

---

# 10. Security Templates

Templates include:

- Threat Model
- Security Assessment
- Secure Architecture Review
- Risk Register
- Compliance Assessment
- Security Controls Matrix
- Penetration Test Summary
- Security Exception Request

---

# 11. Cloud & Infrastructure Templates

Templates include:

- Cloud Architecture
- Kubernetes Design
- Infrastructure as Code Design
- Network Architecture
- Disaster Recovery Plan
- Monitoring Architecture
- Capacity Planning
- Cost Assessment

---

# 12. AI Solution Templates

Templates include:

- AI Solution Design
- RAG Design
- AI Agent Design
- Prompt Specification
- Knowledge Repository Design
- Model Evaluation
- AI Risk Assessment
- Responsible AI Assessment
- AI Operations Runbook

---

# 13. Governance Templates

Templates include:

- Architecture Review Report
- ADR
- Technology Evaluation
- Exception Request
- Architecture Compliance Report
- Go-Live Approval
- Architecture Scorecard
- Architecture Roadmap

---

# 14. Template Usage Guidelines

Templates should:

- Follow WBF naming standards.
- Be version controlled.
- Reference related ADRs.
- Reference applicable WBF documents.
- Maintain consistent section ordering.
- Be reviewed during architecture governance.
- Support automation where practical.
- Remain technology neutral where possible.

---

# 15. Best Practices

- Use standard templates for every project.
- Keep templates concise and reusable.
- Separate templates from implementation details.
- Update templates through governance.
- Link templates to ADRs and checklists.
- Maintain backward compatibility where practical.
- Automate template generation using AI tools.
- Store templates in a central repository.

---

# 16. Anti-Patterns

Avoid:

- Project-specific template variations without approval
- Duplicate templates
- Mixing architecture and implementation documentation
- Missing version information
- Missing ownership
- Unmaintained templates
- Excessively complex templates
- Inconsistent terminology
- Manual formatting inconsistencies

---

# 17. Related WBF Documents

- WBF-DOC-0110 – Governance Framework
- WBF-DOC-0121 – Enterprise Architecture Development Guide
- WBF-DOC-0122 – Solution Architecture Guide
- WBF-DOC-0123 – Architecture Review Guide
- WBF-DOC-0137 – Architecture Decision Records Guide
- WBF-DOC-0138 – Architecture Checklists

---

# 18. Version History

| Version | Date | Description |
|----------|------|-------------|
| 1.0.0 | 2026-07-22 | Initial version |