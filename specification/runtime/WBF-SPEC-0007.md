---
id: WBF-SPEC-0007
title: Workflow Step Sequencing
version: 0.1.0
status: Draft – encodes approved decision D8 (Umesh Wani, 2026-11-01)
category: Runtime
owner: WaysNX Technologies Pvt. Ltd.
authors:
  - WaysNX Architecture Team
created: 2026-11-01
updated: 2026-11-01
related:
  - WBF-SPEC-0004 (OR-06, DR-06)
  - docs/architecture/CORE_CONTRACTS.md §9
conformance: tests/conformance/fixtures/workflow.json (WF-001…WF-010, suite 0.1.0)
---

# WBF-SPEC-0007
# Workflow Step Sequencing

## 1. Purpose

This specification defines how workflow steps are ordered and when an invalid order is rejected. It resolves findings LAR-02, NOD-02 and the Phase 0 known difference WF-003.

Keywords MUST, MUST NOT, SHOULD and MAY are used as defined in WBF-SPEC-0000.

## 2. Requirements

**SEQ-01.** Every workflow step MUST have a `sequence`.

**SEQ-02.** `sequence` MUST be an integer greater than or equal to `0`. Zero is valid.

**SEQ-03.** A missing, non-integer or negative sequence MUST be rejected **when the step is constructed**, with a WBF error that names the step.

**SEQ-04.** Sequences MUST be unique within one workflow definition. A duplicate MUST be rejected **when the workflow definition is constructed**, with a WBF error that names both steps.

**SEQ-05.** Steps MUST execute in ascending `sequence` order, independent of declaration order.

**SEQ-06.** The execution engine MUST keep its own defensive validation (duplicate and negative sequences) for definitions that reach it without passing through the constructors, for example deserialized or hand-built structures.

**SEQ-07.** Runtimes MAY accept the sequence as an optional parameter at the API level so that omitting it reaches the SEQ-03 error rather than a language-level error. They MUST NOT default it to a value.

## 3. Error reasons

Rejections carry a machine-readable reason:

| Reason | Trigger |
|---|---|
| `missing_sequence` | SEQ-01 |
| `invalid_sequence` | not an integer (SEQ-02) |
| `negative_sequence` | below zero (SEQ-02) |
| `duplicate_sequence` | SEQ-04 |

| Runtime | Error | Reason accessor |
|---|---|---|
| Laravel | `WorkflowDefinitionException` (extends `\InvalidArgumentException`) | `getReasonCode()` |
| Node | `WorkflowDefinitionError` (extends `WBFError`, category `WorkflowDefinitionError`) | `reason`, `context.reason` |

## 4. Migration

**Laravel (breaking).** `Registry\WorkflowStep::__construct()` previously defaulted `$sequence` to `0`, so a step without a sequence silently ran first. It is now `mixed $sequence = null` and must be set:

```php
// before: new WorkflowStep('s1', 'HR.LEAVE.APPLY'); // ran as sequence 0
new WorkflowStep('s1', 'HR.LEAVE.APPLY', 0);
```

Two steps with the same sequence in one `WorkflowDefinition` now fail at construction instead of at registration or execution.

**Node.** The TypeScript type `sequence: number` was already required. The runtime check now raises `WorkflowDefinitionError` (previously a plain `Error('WorkflowStep requires valid sequence')`). Non-integer numbers such as `1.5` were previously accepted and are now rejected. Duplicate sequences now fail when `WorkflowDefinition` is constructed.

## 5. Conformance

WF-001…WF-010. A fixture whose definition violates SEQ-01…SEQ-04 expects the outcome `definition_rejected`.
