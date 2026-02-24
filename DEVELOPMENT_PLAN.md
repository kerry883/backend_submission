# Money Tracker API — Development Plan

> **Laravel 12.52.0 | PHP 8.2+ | API-Only Backend**  
> **Deadline:** February 25, 2026 — 3:00 PM  
> **Created:** February 24, 2026

---

## 📋 Project Overview

A **Money Tracker API** built with Laravel 12 that allows users to manage multiple wallets (accounts) and track income/expense transactions. This is a **backend-only** assessment — no frontend required. An existing frontend application will consume these API endpoints.

### Core Entities
| Entity        | Description                                                        |
|---------------|--------------------------------------------------------------------|
| **User**      | A person who owns wallets. No authentication required for creation |
| **Wallet**    | An account belonging to a user (e.g., Business, Personal)         |
| **Transaction** | An income or expense entry linked to a specific wallet          |

### Key Business Rules
- A user can have **multiple wallets**
- Each wallet tracks its own **balance** (derived from transactions)
- **Income** transactions **add** to the wallet balance
- **Expense** transactions **subtract** from the wallet balance
- A user's **total balance** = sum of all wallet balances

---

## 🏗️ Architecture & Design Decisions

### Why These Choices?

| Decision | Rationale |
|----------|-----------|
| **No authentication** | Assessment explicitly states "no authentication required" |
| **API Resources** | Clean, consistent JSON responses using Laravel's Eloquent API Resources |
| **Form Requests** | Validation logic separated from controllers — clean, testable, reusable |
| **PHP Enum for transaction type** | Type-safe, modern PHP 8.1+ backed enum — prevents invalid types at the code level |
| **Decimal(16,2) for amounts** | Financial precision — avoids floating point issues, supports up to 99 trillion |
| **Computed balances** | Balances calculated from transactions (SUM) — single source of truth, no sync issues |
| **`apiResource` routes** | Laravel 12 convention for API-only controllers — excludes `create`/`edit` HTML routes |
| **Nested resource routes** | `users/{user}/wallets` and `wallets/{wallet}/transactions` — RESTful hierarchy |
| **SQLite database** | Already configured — lightweight, zero-setup, perfect for assessment |

### API Endpoint Design

```
POST   /api/users                              → Create a user
GET    /api/users/{user}                        → View user profile (wallets + balances)

POST   /api/users/{user}/wallets                → Create a wallet for a user
GET    /api/users/{user}/wallets                → List all wallets for a user
GET    /api/wallets/{wallet}                    → View single wallet (balance + transactions)

POST   /api/wallets/{wallet}/transactions       → Add a transaction to a wallet
GET    /api/wallets/{wallet}/transactions       → List transactions for a wallet
```

### Database Schema Design

```
users
├── id (bigint, PK)
├── name (string)
├── email (string, unique)
├── created_at / updated_at

wallets
├── id (bigint, PK)
├── user_id (bigint, FK → users.id, CASCADE)
├── name (string) — e.g., "Business Account", "Personal Savings"
├── description (text, nullable)
├── created_at / updated_at

transactions
├── id (bigint, PK)
├── wallet_id (bigint, FK → wallets.id, CASCADE)
├── type (enum: 'income', 'expense')
├── amount (decimal 16,2) — always positive
├── description (string, nullable)
├── created_at / updated_at
```

---

## 🔄 Git Workflow Strategy

### Branch Strategy
```
main                    ← Production-ready code
└── develop             ← Integration branch (all features merge here)
    ├── feature/phase-1-setup-and-models
    ├── feature/phase-2-controllers-and-routes
    ├── feature/phase-3-validation-and-resources
    └── feature/phase-4-testing-and-polish
```

### Initial Git Setup (Run these commands FIRST)
```powershell
# Navigate to project
cd "d:\PC\Laravel\laravel-projects\backend_submission"

# Initialize git repository
git init

# Create initial commit with the fresh Laravel project
git add .
git commit -m "Initial commit: Fresh Laravel 12 project"

# Create and switch to develop branch
git checkout -b develop
```

---

## 📦 Phase 1: Database — Models, Migrations & Relationships

### Objective
Set up the database foundation: migrations, models with relationships, and the PHP enum.

### Todo List
- [ ] Create `TransactionType` PHP Enum (income/expense)
- [ ] Create `wallets` migration with proper schema
- [ ] Create `transactions` migration with proper schema
- [ ] Create `Wallet` model with relationships & fillable attributes
- [ ] Create `Transaction` model with relationships, fillable attributes & casts
- [ ] Update `User` model with wallet relationship
- [ ] Run migrations to verify schema integrity
- [ ] Verify relationships work using `php artisan tinker`

### Git Commands (after Phase 1 completion)
```powershell
git checkout -b feature/phase-1-setup-and-models

# ... (I will implement code here) ...

# After implementation, stage and commit
git add .
git commit -m "feat: add Wallet and Transaction models, migrations, and TransactionType enum

- Created wallets migration with user_id foreign key
- Created transactions migration with wallet_id foreign key, type enum, decimal amount
- Created Wallet model with user/transactions relationships
- Created Transaction model with wallet relationship and type casting
- Added TransactionType backed enum (income/expense)
- Updated User model with wallets relationship"

# Merge back to develop
git checkout develop
git merge feature/phase-1-setup-and-models
```

### Postman Testing (Phase 1)
> No API endpoints yet — verification done via `tinker` and migration status.

---

## 📦 Phase 2: API Routes, Controllers & API Resources

### Objective
Build the API layer — controllers with store/show methods, API resource transformers, and route definitions.

### Todo List
- [ ] Install API routing (`php artisan install:api` or manual setup)
- [ ] Create `UserController` (store, show)
- [ ] Create `WalletController` (store, index, show)
- [ ] Create `TransactionController` (store, index)
- [ ] Create `UserResource` (with wallets, balances)
- [ ] Create `WalletResource` (with balance, transactions)
- [ ] Create `TransactionResource`
- [ ] Define API routes in `routes/api.php`
- [ ] Test all endpoints return correct JSON structure

### Git Commands (after Phase 2 completion)
```powershell
git checkout -b feature/phase-2-controllers-and-routes

# ... (I will implement code here) ...

git add .
git commit -m "feat: add API controllers, resources, and route definitions

- Created UserController with store and show methods
- Created WalletController with store, index, and show methods
- Created TransactionController with store and index methods
- Created UserResource, WalletResource, and TransactionResource
- Defined RESTful API routes with proper nesting
- Balance calculation via transaction aggregation"

git checkout develop
git merge feature/phase-2-controllers-and-routes
```

### Postman Testing (Phase 2)

| # | Method | URL | Body | Expected Status | Expected Response |
|---|--------|-----|------|-----------------|-------------------|
| 1 | POST | `/api/users` | `{"name":"John Doe","email":"john@example.com"}` | 201 | User object with id, name, email |
| 2 | GET | `/api/users/1` | — | 200 | User with wallets array, total_balance = 0 |
| 3 | POST | `/api/users/1/wallets` | `{"name":"Personal Savings"}` | 201 | Wallet object with id, name, balance = 0 |
| 4 | GET | `/api/users/1/wallets` | — | 200 | Array of wallets with balances |
| 5 | POST | `/api/wallets/1/transactions` | `{"type":"income","amount":5000,"description":"Salary"}` | 201 | Transaction object |
| 6 | POST | `/api/wallets/1/transactions` | `{"type":"expense","amount":1500,"description":"Rent"}` | 201 | Transaction object |
| 7 | GET | `/api/wallets/1` | — | 200 | Wallet with balance = 3500, transactions array |
| 8 | GET | `/api/wallets/1/transactions` | — | 200 | Array of transactions |
| 9 | GET | `/api/users/1` | — | 200 | User with wallet balance = 3500, total_balance = 3500 |

---

## 📦 Phase 3: Validation & Error Handling

### Objective
Add robust validation via Form Requests and consistent error responses.

### Todo List
- [ ] Create `StoreUserRequest` (name required, email required/unique/valid)
- [ ] Create `StoreWalletRequest` (name required, description optional)
- [ ] Create `StoreTransactionRequest` (type required/enum, amount required/numeric/positive, description optional)
- [ ] Wire Form Requests into controllers
- [ ] Add consistent JSON error responses for validation failures
- [ ] Add 404 handling for missing resources
- [ ] Test all validation rules with invalid data

### Git Commands (after Phase 3 completion)
```powershell
git checkout -b feature/phase-3-validation-and-resources

# ... (I will implement code here) ...

git add .
git commit -m "feat: implement request validation and error handling

- Created StoreUserRequest with name/email validation
- Created StoreWalletRequest with name validation
- Created StoreTransactionRequest with type/amount validation
- Added TransactionType enum validation rule
- Configured consistent JSON error responses"

git checkout develop
git merge feature/phase-3-validation-and-resources
```

### Postman Testing (Phase 3)

| # | Method | URL | Body | Expected Status | Expected Response |
|---|--------|-----|------|-----------------|-------------------|
| 1 | POST | `/api/users` | `{}` | 422 | Validation errors for name, email |
| 2 | POST | `/api/users` | `{"name":"John","email":"invalid"}` | 422 | Email validation error |
| 3 | POST | `/api/users` | `{"name":"John","email":"john@example.com"}` (duplicate) | 422 | Email uniqueness error |
| 4 | POST | `/api/users/1/wallets` | `{}` | 422 | Validation error for name |
| 5 | POST | `/api/wallets/1/transactions` | `{}` | 422 | Validation errors for type, amount |
| 6 | POST | `/api/wallets/1/transactions` | `{"type":"gift","amount":100}` | 422 | Invalid transaction type error |
| 7 | POST | `/api/wallets/1/transactions` | `{"type":"income","amount":-500}` | 422 | Amount must be positive error |
| 8 | GET | `/api/users/999` | — | 404 | Not found error |
| 9 | GET | `/api/wallets/999` | — | 404 | Not found error |

---

## 📦 Phase 4: Testing, Code Quality & Final Polish

### Objective
Write automated tests, add code comments, and ensure everything works end-to-end.

### Todo List
- [ ] Write Feature tests for User endpoints (create, show profile)
- [ ] Write Feature tests for Wallet endpoints (create, list, show)
- [ ] Write Feature tests for Transaction endpoints (create, list)
- [ ] Write Feature tests for validation rules
- [ ] Write Feature tests for balance calculations
- [ ] Review and add code comments throughout
- [ ] Run full test suite and fix any issues
- [ ] Final manual end-to-end test

### Git Commands (after Phase 4 completion)
```powershell
git checkout -b feature/phase-4-testing-and-polish

# ... (I will implement code here) ...

git add .
git commit -m "test: add comprehensive feature tests for all API endpoints

- Added User creation and profile tests
- Added Wallet CRUD tests
- Added Transaction creation and listing tests
- Added validation rule tests
- Added balance calculation tests
- All tests passing"

git checkout develop
git merge feature/phase-4-testing-and-polish

# Final merge to main
git checkout main
git merge develop
git tag v1.0.0
```

### Test Expectations

```
✓ User can be created with valid data
✓ User creation fails with missing/invalid data
✓ User profile shows all wallets with balances
✓ User profile shows correct total balance
✓ Wallet can be created for a user
✓ All wallets for a user can be listed
✓ Single wallet shows balance and transactions
✓ Income transaction increases wallet balance
✓ Expense transaction decreases wallet balance
✓ Transaction creation fails with invalid type
✓ Transaction creation fails with negative amount
✓ 404 returned for non-existent resources
```

---

## 📂 File Structure (What Will Be Created)

```
app/
├── Enums/
│   └── TransactionType.php              ← PHP 8.1 backed enum
├── Http/
│   ├── Controllers/
│   │   ├── UserController.php           ← store, show
│   │   ├── WalletController.php         ← store, index, show
│   │   └── TransactionController.php    ← store, index
│   ├── Requests/
│   │   ├── StoreUserRequest.php         ← User validation
│   │   ├── StoreWalletRequest.php       ← Wallet validation
│   │   └── StoreTransactionRequest.php  ← Transaction validation
│   └── Resources/
│       ├── UserResource.php             ← User JSON transform
│       ├── WalletResource.php           ← Wallet JSON transform
│       └── TransactionResource.php      ← Transaction JSON transform
├── Models/
│   ├── User.php                         ← Updated with relationships
│   ├── Wallet.php                       ← New model
│   └── Transaction.php                  ← New model
database/
├── migrations/
│   ├── xxxx_create_wallets_table.php    ← New migration
│   └── xxxx_create_transactions_table.php ← New migration
routes/
│   └── api.php                          ← New API routes file
tests/
├── Feature/
│   ├── UserApiTest.php                  ← User endpoint tests
│   ├── WalletApiTest.php                ← Wallet endpoint tests
│   └── TransactionApiTest.php           ← Transaction endpoint tests
```

---

## ⏰ Timeline

| Time          | Task                                    |
|---------------|-----------------------------------------|
| **Now**       | Phase 1 — Models, Migrations, Enum      |
| After Phase 1 | Phase 2 — Controllers, Resources, Routes|
| After Phase 2 | Phase 3 — Validation & Error Handling   |
| After Phase 3 | Phase 4 — Testing & Final Polish        |
| **By 3 PM**   | Final merge to main, tag v1.0.0         |

---

## 🚀 Getting Started — Pre-Development Setup

Before starting Phase 1, run these git commands:

```powershell
cd "d:\PC\Laravel\laravel-projects\backend_submission"
git init
git add .
git commit -m "Initial commit: Fresh Laravel 12 project"
git checkout -b develop
```

**Ready? Confirm to proceed with Phase 1.**
