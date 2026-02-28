# Expense Manager Backend (API Only)

This backend is shared-hosting friendly and provides:
- MySQL schema for double-entry accounting
- REST APIs for Flutter app
- Deterministic transaction posting engine (income/expense/transfer/cards/loans/EMI/investments)

## 1) Configure
1. On server, edit `private/config.php`.
2. Set DB credentials and `app.api_key`.
3. Ensure PHP 8.1+ and MySQL 8+.

## 2) Create Database
1. Create database.
2. Import `backend/schema.sql`.
3. Optional: import `backend/sql/seed_defaults.sql` (edit `user_id` first).

## 3) Deploy on Shared Hosting
- cPanel Git deployment uses `.cpanel.yml`.
- Target path: `/home1/de2shrnx/transactions.easi7.in`.
- Public API root after deploy: `https://transactions.easi7.in/api/...`

## 4) API Security
- Include header: `X-Api-Key: <your_api_key>`

## 5) Core Endpoints
- `GET /api/health`
- `GET /api/account-types`
- `GET /api/accounts?user_id=1`
- `POST /api/accounts/create`
- `POST /api/accounts/update`
- `GET /api/categories?user_id=1`
- `POST /api/categories/create`
- `POST /api/categories/update`
- `GET /api/credit-cards?user_id=1`
- `POST /api/credit-cards/create`
- `GET /api/loans?user_id=1`
- `POST /api/loans/create`
- `GET /api/investments?user_id=1`
- `POST /api/investments/create`
- `GET /api/emis?user_id=1&status=PENDING`
- `POST /api/emis/create`
- `POST /api/emis/mark-paid`
- `GET /api/transactions?user_id=1&limit=50`
- `GET /api/transactions/view?user_id=1&id=<transaction_id>`
- `POST /api/transactions/create`
- `GET /api/reports/monthly?user_id=1&year=2026&month=2`
- `GET /api/dashboard/summary?user_id=1`

## Sample: Create Card EMI Payment
`POST /api/transactions/create`

```json
{
  "user_id": 1,
  "txn_type": "card_emi_payment",
  "txn_date": "2026-02-28",
  "description": "Card EMI payment",
  "principal_amount": 3000,
  "interest_amount": 250,
  "gst_amount": 45,
  "fees_amount": 0,
  "total_amount": 3295,
  "principal_liability_account_id": 10,
  "interest_expense_account_id": 11,
  "gst_expense_account_id": 12,
  "fees_expense_account_id": 13,
  "payment_account_id": 2
}
```

## Transaction Types Supported
- `income`
- `expense_cash_or_bank`
- `transfer`
- `credit_card_purchase`
- `credit_card_payment`
- `loan_disbursement`
- `loan_emi_payment`
- `card_emi_payment`
- `investment_buy`
- `investment_income`
- `investment_redeem`
- `adjustment`

For posting rules, see `docs/posting-templates.md`.
