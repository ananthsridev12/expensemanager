# Posting Templates (Critical Rules)

All templates must produce balanced journal entries.

## 1) Income to Bank
Input: amount, bank_account, income_account
- DR bank_account
- CR income_account

## 2) Expense from Bank/Cash
Input: amount, expense_account, source_account
- DR expense_account
- CR source_account

## 3) Transfer Between Assets
Input: amount, from_asset, to_asset
- DR to_asset
- CR from_asset

## 4) Credit Card Purchase
Input: amount, expense_or_asset_account, card.principal_account
- DR expense_or_asset_account
- CR card.principal_account

## 5) Credit Card Bill Payment
Input: amount, bank_account, card.principal_account
- DR card.principal_account
- CR bank_account

## 6) Card EMI/Loan Conversion (at booking)
Input: principal, processing_fee, fee_gst, card.principal_account, fee_expense_account, gst_expense_account
- DR expense/asset (merchant purchase) OR already posted purchase
- CR card.principal_account (principal only)
- DR fee_expense_account (processing fee)
- DR gst_expense_account (GST on fee)
- CR bank/card payable based on actual charge mode

Important: principal is the only component that should affect card utilization.

## 7) Monthly EMI Payment (Loan/Card)
Input: principal_part, interest_part, gst_part, fees_part, bank_account
Loan accounts: principal_liability, interest_expense, charges_expense
Card accounts: principal_liability, interest_expense, gst_expense

- DR principal_liability (principal_part)
- DR interest_expense (interest_part)
- DR gst_expense/charges_expense (gst_part + fees_part as applicable)
- CR bank_account (sum)

## 8) Loan Disbursement
Input: amount, bank_account, loan_principal_liability
- DR bank_account
- CR loan_principal_liability

## 9) Investment Buy
Input: amount, investment_asset_account, bank_account
- DR investment_asset_account
- CR bank_account

## 10) Investment Income
Input: amount, bank_account, investment_income_account
- DR bank_account
- CR investment_income_account

## 11) Investment Redeem
Input: amount_redeemed, bank_account, investment_asset_account
- DR bank_account
- CR investment_asset_account (book value portion)
- Profit/loss booking can be additional line if required.

## Validation Checks
- Debits must equal credits.
- Amounts > 0.
- EMI split check: principal + interest + gst + fees = total.
- For credit card due dashboard: only principal liability account balance is used.
