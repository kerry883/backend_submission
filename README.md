# Money Tracker API

A RESTful API built with Laravel 12 for tracking personal finances. Users can create accounts, manage multiple wallets, and record income/expense transactions with automatic balance calculations.

## Tech Stack

- **PHP** 8.4
- **Laravel** 12
- **MySQL** (production) / **SQLite** (testing)
- **Pest PHP** for testing
- **Papyrus Docs** for interactive API documentation

## Getting Started

### Prerequisites

- PHP >= 8.4
- Composer
- MySQL

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd backend_submission

# Install dependencies
composer install

# Copy environment file and configure your database
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start the development server
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

### Interactive API Documentation

Once the server is running, visit:

```
http://127.0.0.1:8000/papyrus-docs
```

This provides a full interactive playground to explore and test all endpoints.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/users` | Create a new user |
| `GET` | `/api/users/{user}` | Get user profile with wallets & total balance |
| `GET` | `/api/users/{user}/wallets` | List all wallets for a user |
| `POST` | `/api/users/{user}/wallets` | Create a wallet for a user |
| `GET` | `/api/wallets/{wallet}` | Get wallet details with balance & transactions |
| `GET` | `/api/wallets/{wallet}/transactions` | List all transactions for a wallet |
| `POST` | `/api/wallets/{wallet}/transactions` | Add a transaction to a wallet |

## Usage Examples

### Create a User

```bash
POST /api/users
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-02-25T00:00:00.000000Z",
    "updated_at": "2026-02-25T00:00:00.000000Z"
  }
}
```

### Create a Wallet

```bash
POST /api/users/1/wallets
Content-Type: application/json

{
  "name": "Personal Savings",
  "description": "My main savings account"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "Personal Savings",
    "description": "My main savings account",
    "balance": "0.00",
    "transactions": [],
    "created_at": "2026-02-25T00:00:00.000000Z",
    "updated_at": "2026-02-25T00:00:00.000000Z"
  }
}
```

### Add a Transaction

```bash
POST /api/wallets/1/transactions
Content-Type: application/json

{
  "type": "income",
  "amount": 5000.00,
  "description": "Monthly salary"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "wallet_id": 1,
    "type": "income",
    "amount": "5000.00",
    "description": "Monthly salary",
    "created_at": "2026-02-25T00:00:00.000000Z",
    "updated_at": "2026-02-25T00:00:00.000000Z"
  }
}
```

### Get User Profile (with balances)

```bash
GET /api/users/1
```

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallets": [
      {
        "id": 1,
        "user_id": 1,
        "name": "Personal Savings",
        "description": "My main savings account",
        "balance": "5000.00",
        "transactions": [...],
        "created_at": "...",
        "updated_at": "..."
      }
    ],
    "total_balance": "5000.00",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

## Validation

All endpoints return structured JSON errors on invalid input:

```json
{
  "message": "A name is required to create a user account.",
  "errors": {
    "name": ["A name is required to create a user account."]
  }
}
```

| Resource | Field | Rules |
|----------|-------|-------|
| User | `name` | required, string, max 255 |
| User | `email` | required, valid email, unique |
| Wallet | `name` | required, string, max 255 |
| Wallet | `description` | optional, string, max 1000 |
| Transaction | `type` | required, `income` or `expense` |
| Transaction | `amount` | required, numeric, greater than 0 |
| Transaction | `description` | optional, string, max 255 |

## Balance Calculation

- **Wallet Balance** = sum of all income transactions − sum of all expense transactions
- **User Total Balance** = sum of all wallet balances
- Balances are computed dynamically from transactions and returned as formatted decimal strings (e.g., `"1250.00"`)

## Running Tests

```bash
php artisan test
```

The test suite includes 49 tests covering:
- User creation and retrieval
- Wallet CRUD operations
- Transaction recording and listing
- All validation rules
- Balance calculations (wallet-level and user-level)
- Edge cases (negative balances, decimal precision, empty states, 404s)

## Project Structure

```
app/
├── Enums/TransactionType.php        # Income/Expense enum
├── Http/
│   ├── Controllers/
│   │   ├── UserController.php
│   │   ├── WalletController.php
│   │   └── TransactionController.php
│   ├── Requests/
│   │   ├── StoreUserRequest.php
│   │   ├── StoreWalletRequest.php
│   │   └── StoreTransactionRequest.php
│   └── Resources/
│       ├── UserResource.php
│       ├── WalletResource.php
│       └── TransactionResource.php
├── Models/
│   ├── User.php
│   ├── Wallet.php
│   └── Transaction.php
database/
├── factories/
│   ├── UserFactory.php
│   ├── WalletFactory.php
│   └── TransactionFactory.php
├── migrations/
tests/Feature/
├── UserApiTest.php
├── WalletApiTest.php
├── TransactionApiTest.php
└── BalanceCalculationTest.php
```
