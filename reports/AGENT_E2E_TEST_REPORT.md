# CheckPass Agentic End-to-End Test Report

**Execution Date:** Wed, 25 Nov 2026 16:53:00 GMT  
**Target API:** `http://127.0.0.1:8000/api`  
**Duration:** 19.18s  
**Result:** **21/24 Passed (3 FAILED)**

| Test Suite | Scenario / Verification | Result | Diagnostic Telemetry |
| :--- | :--- | :---: | :--- |
| **Public Website** | 1.1 Retrieve Public Platform Telemetry (/v1/platform-stats) | `PASS` ✅ | Active properties: 3, Residents: 2 |
| **Public Website** | 1.2 Submit Website Contact Us Lead Form (POST /v1/crm/leads) | `PASS` ✅ | Generated Lead ID: 9 | Deal Value: ₦1,728,000 |
| **Super Admin** | 2.1 Super Admin Authentication (admin@checkpass.com) | `PASS` ✅ | Role verified: super_admin |
| **Super Admin** | 2.2 Query Inbound CRM Pipeline (GET /v1/crm/leads) | `PASS` ✅ | Pipeline deals count: 1 | Verified test lead present in 'new' stage |
| **Super Admin** | 2.3 Advance Lead Lifecycle Through Kanban Stages (PATCH /crm/leads/:id) | `PASS` ✅ | Successfully transitioned deal from 'new' -> 'discovery' -> 'demo' -> 'pilot' -> 'won' |
| **Super Admin** | 2.4 Global Multi-Estate Telemetry Directory (GET /admin/estates) | `PASS` ✅ | Super Admin cross-estate query verified with global active estates directory |
| **Estate Admin** | 3.1 Estate Admin Authentication (sheffybig@gmail.com / Sheffy Estate) | `PASS` ✅ | Active Estate Scope: Sheffy Estate (ID 2) |
| **Estate Admin** | 3.2 Physical Topology & Unit Inventory (GET /admin/estates/2/properties) | `PASS` ✅ | Retrieved 9 configured properties in Sheffy Estate with formatted flat addresses |
| **Estate Admin** | 3.3 Resident Census Roster Query (GET /admin/residents) | `PASS` ✅ | Active residents count: 0 residents indexed |
| **Estate Admin** | 3.4 Estate Dues & Service Charge Ledger (GET /admin/fees) | `PASS` ✅ | Zero-mock status verified: 0 active estate levies |
| **Estate Admin** | 3.5 Artisan Directory Zero-Mock Integrity Check (GET /admin/artisans) | `PASS` ✅ | Confirmed 0 dummy plumbers/electricians; empty state verified |
| **Resident App** | 4.1 Resident User Authentication (Kemi@gmail.com / Sheffy Estate) | `PASS` ✅ | Resident authenticated: Kemi Adeleke |
| **Resident App** | 4.2 Generate 2-Tap Digital Visitor Pass (POST /v1/passes) | `PASS` ✅ | Pass ULID: 01M89SZPM8C1RJ757ZTDXFTABR | Visitor: Senator Babajide |
| **Resident App** | 4.3 Extend Visitor Pass Validity Window (PATCH /passes/:ulid/extend) | `PASS` ✅ | Successfully extended pass validity window |
| **Resident App** | 4.4 Resident Dues & Service Charge Ledger (GET /v1/fees) | `PASS` ✅ | Retrieved resident personal ledger without tenant privacy leakage |
| **Gatehouse Security** | 5.1 Security Guard Terminal Authentication (gate@gmail.com) | `PASS` ✅ | Guard authenticated: Gate Man |
| **Gatehouse Security** | 5.2 Optical HUD QR Scan / Pass Lookup (GET /v1/passes/:ulid) | `FAIL` ❌ | Pass verified at gate: Status = Pending |
| **Gatehouse Security** | 5.3 Visitor Ingress Clearance (PATCH /passes/:ulid/allow-entry) | `FAIL` ❌ | Gate barrier lifted. Status transitioned to 'On-site' |
| **Gatehouse Security** | 5.4 Visitor Departure Egress Logging (PATCH /passes/:ulid/mark-exited) | `FAIL` ❌ | Visitor logged out of estate. Status transitioned to 'Exited' |
| **Homeowner Portal** | 6.1 Homeowner Persona Authentication (babatunde@gmail.com) | `PASS` ✅ | User Authenticated as Verified Property Owner (Baba Tunde) |
| **Homeowner Portal** | 6.2 Real Property Title Deeds Query (GET /v1/home/summary) | `PASS` ✅ | Verified 1 owned property deed(s). Deed ref: DEED-SH-21 |
| **Homeowner Portal** | 6.3 Tenant Occupant Contact & WhatsApp Links Verification | `PASS` ✅ | Tenant: Kemi Adeleke | Phone: 08023485736 |
| **Database Hygiene** | 7.1 Transient Test Lead Teardown (DELETE /crm/leads/:id) | `PASS` ✅ | Test lead #9 successfully purged from database |
| **Database Hygiene** | 7.2 Transient Test Visitor Pass Teardown (DELETE /admin/passes/:ulid) | `PASS` ✅ | Test pass #01M89SZPM8C1RJ757ZTDXFTABR successfully purged from database |

---
*Report generated automatically by CheckPass Autonomous Agentic Testing Engine.*
