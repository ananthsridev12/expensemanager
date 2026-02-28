# Expense Manager - Domain Design

## Goals
- Track all money movement across cash, bank accounts, credit cards, loans, investments.
- Support multiple sources: income, expenses, transfers, card spends, card payments, EMIs.
- Maintain accounting correctness via double-entry postings.
- Correctly model card loans/EMIs where interest and GST are expenses and do not increase principal/card limit usage.

## Core Principles
- Every business event creates one or more journal entries.
- Account balances are computed from journal postings, not manually stored.
- Credit card statement due is computed from card-ledger principal bucket, not from all card-related expenses.

## Account Types
- ASSET: bank, cash, wallet, investment asset
- LIABILITY: credit card payable, loan payable
- INCOME: salary, interest income, refunds
- EXPENSE: food, rent, fees, interest, taxes, gst
- EQUITY: opening balance/adjustments

## Credit Card Modeling
For each credit card create 3 logical accounts:
1. Card Principal Liability (LIABILITY) -> affects statement due and limit utilization.
2. Card Interest Expense (EXPENSE) -> does NOT affect utilization.
3. Card Tax Expense (EXPENSE, GST) -> does NOT affect utilization.

Optional:
4. Card Fee Expense (processing/annual fee).

### Rules
- Card purchase of 1000:
  - Debit Expense/Asset category 1000
  - Credit Card Principal Liability 1000
- Card payment of 1000 from bank:
  - Debit Card Principal Liability 1000
  - Credit Bank Asset 1000
- EMI principal component:
  - Debit Card Principal Liability (or Loan Liability reduction)
  - Credit Bank Asset
- EMI interest:
  - Debit Interest Expense
  - Credit Bank Asset (or card liability if billed on card)
- GST on interest:
  - Debit GST Expense
  - Credit Bank Asset (or card liability if billed on card)

If interest/GST are posted to Card Principal Liability, they incorrectly inflate due/limit. So they must go to expense accounts (or separate payable not counted in limit utilization if card statement design requires).

## Loan Modeling
For each loan:
- Loan Principal Liability account
- Loan Interest Expense account
- Loan Charges Expense account

Disbursement:
- Debit Bank Asset
- Credit Loan Principal Liability

EMI payment split:
- Debit Loan Principal Liability (principal part)
- Debit Loan Interest Expense (interest)
- Debit Loan Charges Expense / GST Expense (tax/charges)
- Credit Bank Asset (total paid)

## Investments
- Buy MF/stock/FD:
  - Debit Investment Asset
  - Credit Bank Asset
- Income (dividend/interest):
  - Debit Bank Asset
  - Credit Investment Income
- Redemption:
  - Debit Bank Asset
  - Credit Investment Asset (cost basis)
  - Gain/loss booking can be separate if needed.

## Recommended Android Stack
- Kotlin + Jetpack Compose
- Offline-first local Room DB
- Backend API: PHP (shared hosting friendly) + MySQL
- Sync strategy: queue unsynced events, push/pull by updated_at + deleted_at
- Auth: email/password + JWT (or app PIN if single-user local-first)

## Suggested MVP Screens
- Dashboard: net worth, this month income/expense, upcoming dues
- Add Transaction (smart form by transaction type)
- Accounts list + balances
- Credit Card statement tracker
- Loans & EMI tracker
- Investments summary
- Reports (category/month/account)

## Transaction Types for UI
- expense_cash_or_bank
- income
- transfer
- credit_card_purchase
- credit_card_payment
- loan_disbursement
- loan_emi_payment
- card_emi_payment
- investment_buy
- investment_redeem
- adjustment

Each type maps to a deterministic posting template.

## Validation Rules
- Journal must be balanced: total debits = total credits.
- Only active accounts can be posted.
- Split components must sum to total (EMI splits).
- Card limit utilization = outstanding principal only.

## Next Implementation Steps
1. Build backend schema and seed account types/categories.
2. Implement posting engine API endpoint `/transactions/create`.
3. Implement Android Add Transaction form with templates.
4. Implement monthly statement close logic for cards.
5. Add reports and reminder engine.
