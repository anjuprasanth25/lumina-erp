# LuminaERP — Multi-Tenant Enterprise Resource Planning System

LuminaERP is a modern enterprise application designed to handle multi-company organizational structures, granular role-based access controls (RBAC), and multi-stage approval workflows. Built with a decoupled frontend/backend architecture, it combines enterprise security with responsive management interfaces.
---

## 🎬 System Demo Walkthrough
[![Watch on Loom](https://img.shields.io/badge/Loom-Watch_2--Min_Demo-625DF5?style=for-the-badge&logo=loom&logoColor=white)](https://www.loom.com/share/204d8c0ffe744069a9cdc2ca02abb69f)

Watch a 4-minute video walkthrough demonstrating end-to-end functionality:
* **Role-Based Access Control (RBAC):** Dynamic dashboard filtering powered by Laravel Gate policies (`ExpenseClaimPolicy.php`).
* **Multi-Tier Approvals:** End-to-end verification, manager approval, finance booking, and posting stages.
* **Automated Notifications & Audit Trails:** Automated email triggers for approvers and full history logs with rejection/resubmission handling.
* **Core ERP Suite:** Completed User Onboarding, Employee Onboarding, and Expense Claim modules.

---

## Key Capabilities & Modules

- **Multi-Tenant Structure:** Complete data isolation and relational mapping for multi-company operations.
- **Role-Based Access Control (RBAC):** Custom authorization policies built with Spatie RBAC and native Laravel Gate policies.
- **Expense Claims Processing:** Approval engine supporting submission, line manager verification, final approval, and financial posting transitions.
- **Asynchronous Processing:** Non-blocking background workers handling transactional email updates via queued jobs.
- **Dynamic Administration:** Dedicated management interfaces built using Filament resources for administrative oversight and audit tracking.

---

## Technical Architecture & Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Admin Interface:** Filament v3
- **Frontend Integration:** Inertia.js with React
- **Styling:** Tailwind CSS
- **Database:** MySQL
- **Queue Driver:** Database / Redis (for asynchronous mail dispatching)

---

## System Requirements & Installation

1. **Clone repository:**
    ```bash
    git clone https://github.com/anjuprasanth25/lumina-erp.git
    cd lumina-erp
    ```
