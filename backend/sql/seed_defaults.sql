-- Optional seed for user_id = 1. Run after creating admin and knowing your user id.

-- Example ASSET accounts
INSERT INTO accounts (user_id, account_type_id, name, code, currency)
VALUES
(1, (SELECT id FROM account_types WHERE code = 'ASSET'), 'Cash Wallet', 'CASH', 'INR'),
(1, (SELECT id FROM account_types WHERE code = 'ASSET'), 'HDFC Savings', 'HDFC_SAV', 'INR');

-- Example LIABILITY accounts (card principal + loan principal)
INSERT INTO accounts (user_id, account_type_id, name, code, currency)
VALUES
(1, (SELECT id FROM account_types WHERE code = 'LIABILITY'), 'ICICI Credit Card Principal', 'ICICI_CC_PR', 'INR'),
(1, (SELECT id FROM account_types WHERE code = 'LIABILITY'), 'Home Loan Principal', 'HOME_LOAN_PR', 'INR');

-- Example EXPENSE accounts
INSERT INTO accounts (user_id, account_type_id, name, code, currency)
VALUES
(1, (SELECT id FROM account_types WHERE code = 'EXPENSE'), 'Food Expense', 'EXP_FOOD', 'INR'),
(1, (SELECT id FROM account_types WHERE code = 'EXPENSE'), 'Card Interest Expense', 'EXP_CC_INT', 'INR'),
(1, (SELECT id FROM account_types WHERE code = 'EXPENSE'), 'GST Expense', 'EXP_GST', 'INR');

-- Example INCOME account
INSERT INTO accounts (user_id, account_type_id, name, code, currency)
VALUES
(1, (SELECT id FROM account_types WHERE code = 'INCOME'), 'Salary Income', 'INC_SALARY', 'INR');

-- Categories
INSERT INTO categories (user_id, kind, name) VALUES
(1, 'EXPENSE', 'Food'),
(1, 'EXPENSE', 'Fuel'),
(1, 'EXPENSE', 'Interest'),
(1, 'INCOME', 'Salary');
