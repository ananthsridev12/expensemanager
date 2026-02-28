-- Expense Manager MySQL Schema (MVP)

CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE account_types (
  id TINYINT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(30) NOT NULL UNIQUE,
  normal_side ENUM('DEBIT','CREDIT') NOT NULL
);

INSERT INTO account_types (code, normal_side) VALUES
('ASSET','DEBIT'),
('LIABILITY','CREDIT'),
('INCOME','CREDIT'),
('EXPENSE','DEBIT'),
('EQUITY','CREDIT');

CREATE TABLE accounts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  account_type_id TINYINT NOT NULL,
  name VARCHAR(150) NOT NULL,
  code VARCHAR(50) NULL,
  currency CHAR(3) NOT NULL DEFAULT 'INR',
  parent_account_id BIGINT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (account_type_id) REFERENCES account_types(id),
  FOREIGN KEY (parent_account_id) REFERENCES accounts(id),
  INDEX idx_accounts_user (user_id)
);

CREATE TABLE categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  kind ENUM('INCOME','EXPENSE','TRANSFER','INVESTMENT','LOAN') NOT NULL,
  name VARCHAR(120) NOT NULL,
  parent_category_id BIGINT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (parent_category_id) REFERENCES categories(id),
  INDEX idx_categories_user (user_id)
);

CREATE TABLE transactions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  txn_type VARCHAR(40) NOT NULL,
  txn_date DATE NOT NULL,
  description VARCHAR(255) NULL,
  external_ref VARCHAR(120) NULL,
  status ENUM('POSTED','VOID') NOT NULL DEFAULT 'POSTED',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_transactions_user_date (user_id, txn_date)
);

CREATE TABLE journal_entries (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  transaction_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  account_id BIGINT NOT NULL,
  category_id BIGINT NULL,
  side ENUM('DEBIT','CREDIT') NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (transaction_id) REFERENCES transactions(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (account_id) REFERENCES accounts(id),
  FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_journal_txn (transaction_id),
  INDEX idx_journal_user_account (user_id, account_id)
);

CREATE TABLE credit_cards (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  name VARCHAR(120) NOT NULL,
  limit_amount DECIMAL(14,2) NOT NULL,
  billing_day TINYINT NOT NULL,
  due_day TINYINT NOT NULL,
  principal_account_id BIGINT NOT NULL,
  interest_expense_account_id BIGINT NOT NULL,
  gst_expense_account_id BIGINT NOT NULL,
  fee_expense_account_id BIGINT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (principal_account_id) REFERENCES accounts(id),
  FOREIGN KEY (interest_expense_account_id) REFERENCES accounts(id),
  FOREIGN KEY (gst_expense_account_id) REFERENCES accounts(id),
  FOREIGN KEY (fee_expense_account_id) REFERENCES accounts(id)
);

CREATE TABLE loans (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  name VARCHAR(150) NOT NULL,
  lender VARCHAR(150) NULL,
  principal_account_id BIGINT NOT NULL,
  interest_expense_account_id BIGINT NOT NULL,
  charges_expense_account_id BIGINT NULL,
  sanction_amount DECIMAL(14,2) NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  interest_rate_annual DECIMAL(8,4) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (principal_account_id) REFERENCES accounts(id),
  FOREIGN KEY (interest_expense_account_id) REFERENCES accounts(id),
  FOREIGN KEY (charges_expense_account_id) REFERENCES accounts(id)
);

CREATE TABLE emis (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  loan_id BIGINT NULL,
  credit_card_id BIGINT NULL,
  due_date DATE NOT NULL,
  principal_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  interest_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  fees_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(14,2) NOT NULL,
  payment_transaction_id BIGINT NULL,
  status ENUM('PENDING','PAID','SKIPPED') NOT NULL DEFAULT 'PENDING',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (loan_id) REFERENCES loans(id),
  FOREIGN KEY (credit_card_id) REFERENCES credit_cards(id),
  FOREIGN KEY (payment_transaction_id) REFERENCES transactions(id),
  INDEX idx_emis_user_due (user_id, due_date)
);

CREATE TABLE investments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  name VARCHAR(150) NOT NULL,
  instrument_type VARCHAR(40) NOT NULL,
  asset_account_id BIGINT NOT NULL,
  income_account_id BIGINT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (asset_account_id) REFERENCES accounts(id),
  FOREIGN KEY (income_account_id) REFERENCES accounts(id)
);

-- View for balances
CREATE OR REPLACE VIEW account_balances AS
SELECT
  je.user_id,
  je.account_id,
  SUM(CASE
      WHEN je.side = 'DEBIT' THEN je.amount
      ELSE -je.amount
  END) AS net_debit_balance
FROM journal_entries je
GROUP BY je.user_id, je.account_id;

-- Credit card utilization logic (principal only)
-- utilization = absolute credit-normalized balance on principal liability account
