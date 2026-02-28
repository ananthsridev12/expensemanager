# Expense Manager Backend (PHP + MySQL)

This backend is shared-hosting friendly and provides:
- MySQL schema for double-entry accounting
- REST APIs for Flutter app
- Admin web panel for manual operations

## 1) Configure
1. Copy `backend/config.example.php` to `backend/config.php`.
2. Update DB credentials and `app.api_key`.
3. Ensure PHP 8.0+ and MySQL 8+.

## 2) Create Database
1. Create database `expense_manager` (or your chosen name).
2. Import `backend/schema.sql`.
3. Optional: import `backend/sql/seed_defaults.sql` (edit `user_id` first).

## 3) Deploy on Shared Hosting
- Place repository contents in your hosting file manager.
- Set web root to `backend/public` if possible.
- If web root cannot be changed, keep files as-is and access:
  - API: `/backend/public/index.php/api/...`
  - Admin: `/backend/public/admin/login.php`

## 4) Create Admin User
- Open `/admin/setup.php` once and create user.
- Then delete or block access to `setup.php`.

## 5) API Security
- Include header `X-Api-Key: <your_api_key>` in every API request.

## API Endpoints
- `GET /api/health`
- `GET /api/accounts?user_id=1`
- `POST /api/accounts/create`
- `GET /api/categories?user_id=1`
- `POST /api/categories/create`
- `GET /api/transactions?user_id=1&limit=30`
- `POST /api/transactions/create`

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
- `adjustment` (manual entries)

For posting rules, see `docs/posting-templates.md`.
