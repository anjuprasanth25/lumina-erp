# LuminaERP — Multi-Tenant Enterprise Resource Planning System

LuminaERP is a modern multi-tenant enterprise application designed to support multi-company organizational structures, role-based access control (RBAC), and multi-stage business workflows.

Built with Laravel 12, PHP 8.2+, React, Inertia.js, Filament v3, Tailwind CSS, and MySQL, the platform combines server-side authorization, business rules, workflow controls, and responsive management interfaces.
---

## 🎬 System Demo Walkthrough
[![Watch on Loom](https://img.shields.io/badge/Loom-Watch_2--Min_Demo-625DF5?style=for-the-badge&logo=loom&logoColor=white)](https://www.loom.com/share/204d8c0ffe744069a9cdc2ca02abb69f)

Watch the video walkthrough demonstrating an end-to-end expense claim workflow:

**Employee Submission → Verification → Line Manager Approval → Finance Approval → Booking → Posting**

**Demonstrated Capabilities**
**Role-Based Access Control (RBAC):** Role- and policy-based authorization using Laravel Policies/Gates, with available actions determined by the user's role and the current transaction state.
**Multi-Stage Financial Workflow:** End-to-end expense claim processing covering verification, manager approval, finance approval, booking, and final posting.
**Audit Trail:** Workflow history records key actions throughout the transaction lifecycle, providing traceability across approval and financial processing stages.
**Rejection & Resubmission:** Expense claims can be rejected and resubmitted, allowing the transaction to re-enter the appropriate workflow stage.
**ERP Modules:** User Onboarding, Employee Onboarding, and Expense Claims modules.

---

## Key Capabilities & Modules

**Multi-Tenant Structure**
Application architecture designed to support multiple companies and organizational entities within a shared ERP platform.

**Role-Based Access Control**
Custom authorization policies using RBAC and native Laravel Policies/Gates to control access to resources and business actions.

**Expense Claims Processing**
Multi-stage approval engine supporting:

**Submission → Verification → Line Manager Approval → Finance Approval → Booking → Posting**

Available actions are controlled according to both user authorization and transaction state.

**Asynchronous Processing**
Queued background jobs support asynchronous operations such as email notifications without blocking the primary application workflow.

**Dynamic Administration**
Administrative interfaces built with Filament v3 resources, providing structured management, data administration, and workflow oversight.

---

## Technical Architecture & Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Application Interface:** Inertia.js + React
- **Administration:** Filament v3
- **Styling:** Tailwind CSS
- **Database:** MySQL
- **Queue Driver:** Laravel Queues with database queue driver; Redis supported for asynchronous processing.

---

## System Requirements & Installation

1. **Clone repository:**
    ```bash
    git clone https://github.com/anjuprasanth25/lumina-erp.git
    cd lumina-erp
    ```
