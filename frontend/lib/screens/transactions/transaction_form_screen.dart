import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/transaction_provider.dart';
import '../../providers/account_provider.dart';
import '../../providers/category_provider.dart';
import '../../providers/credit_card_provider.dart';
import '../../providers/loan_provider.dart';
import '../../providers/investment_provider.dart';
import '../../widgets/account_dropdown.dart';
import '../../widgets/category_dropdown.dart';

class TransactionFormScreen extends StatefulWidget {
  const TransactionFormScreen({super.key});

  @override
  State<TransactionFormScreen> createState() => _TransactionFormScreenState();
}

class _TransactionFormScreenState extends State<TransactionFormScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _saving = false;

  String _txnType = 'expense_cash_or_bank';
  DateTime _txnDate = DateTime.now();
  final _descCtrl = TextEditingController();
  final _amountCtrl = TextEditingController();
  final _principalAmtCtrl = TextEditingController();
  final _interestAmtCtrl = TextEditingController();
  final _gstAmtCtrl = TextEditingController();
  final _feesAmtCtrl = TextEditingController();

  int? _fromAccountId;
  int? _toAccountId;
  int? _expenseAccountId;
  int? _incomeAccountId;
  int? _bankAccountId;
  int? _categoryId;
  int? _selectedCardId;
  int? _selectedLoanId;
  int? _selectedInvestmentId;

  // For adjustment entries
  final List<Map<String, dynamic>> _adjustmentEntries = [
    {'account_id': null, 'side': 'DEBIT', 'amount': ''},
    {'account_id': null, 'side': 'CREDIT', 'amount': ''},
  ];

  static const _txnTypes = [
    ('income', 'Income'),
    ('expense_cash_or_bank', 'Expense'),
    ('transfer', 'Transfer'),
    ('credit_card_purchase', 'Card Purchase'),
    ('credit_card_payment', 'Card Payment'),
    ('loan_disbursement', 'Loan Disbursement'),
    ('loan_emi_payment', 'Loan EMI'),
    ('card_emi_payment', 'Card EMI'),
    ('investment_buy', 'Investment Buy'),
    ('investment_income', 'Investment Income'),
    ('investment_redeem', 'Investment Redeem'),
    ('adjustment', 'Adjustment'),
  ];

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<AccountProvider>().fetchAll();
      context.read<CategoryProvider>().fetchAll();
      context.read<CreditCardProvider>().fetchAll();
      context.read<LoanProvider>().fetchAll();
      context.read<InvestmentProvider>().fetchAll();
    });
  }

  @override
  void dispose() {
    _descCtrl.dispose();
    _amountCtrl.dispose();
    _principalAmtCtrl.dispose();
    _interestAmtCtrl.dispose();
    _gstAmtCtrl.dispose();
    _feesAmtCtrl.dispose();
    super.dispose();
  }

  double _parseAmount(TextEditingController ctrl) =>
      double.tryParse(ctrl.text) ?? 0;

  double get _emiTotal =>
      _parseAmount(_principalAmtCtrl) +
      _parseAmount(_interestAmtCtrl) +
      _parseAmount(_gstAmtCtrl) +
      _parseAmount(_feesAmtCtrl);

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _txnDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
    );
    if (picked != null) setState(() => _txnDate = picked);
  }

  void _resetFields() {
    _amountCtrl.clear();
    _principalAmtCtrl.clear();
    _interestAmtCtrl.clear();
    _gstAmtCtrl.clear();
    _feesAmtCtrl.clear();
    _fromAccountId = null;
    _toAccountId = null;
    _expenseAccountId = null;
    _incomeAccountId = null;
    _bankAccountId = null;
    _categoryId = null;
    _selectedCardId = null;
    _selectedLoanId = null;
    _selectedInvestmentId = null;
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final body = _buildPayload();
    if (body == null) {
      setState(() => _saving = false);
      return;
    }

    final txnId =
        await context.read<TransactionProvider>().createTransaction(body);

    if (!mounted) return;
    setState(() => _saving = false);
    if (txnId != null) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Transaction created')));
    } else {
      final err = context.read<TransactionProvider>().error;
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(err ?? 'Failed')));
    }
  }

  Map<String, dynamic>? _buildPayload() {
    final date = DateFormat('yyyy-MM-dd').format(_txnDate);
    final base = <String, dynamic>{
      'txn_type': _txnType,
      'txn_date': date,
      if (_descCtrl.text.trim().isNotEmpty)
        'description': _descCtrl.text.trim(),
    };

    switch (_txnType) {
      case 'income':
        base['to_account_id'] = _toAccountId;
        base['income_account_id'] = _incomeAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        if (_categoryId != null) base['category_id'] = _categoryId;
        break;
      case 'expense_cash_or_bank':
        base['expense_account_id'] = _expenseAccountId;
        base['from_account_id'] = _fromAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        if (_categoryId != null) base['category_id'] = _categoryId;
        break;
      case 'transfer':
        base['from_account_id'] = _fromAccountId;
        base['to_account_id'] = _toAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        break;
      case 'credit_card_purchase':
        final card = context.read<CreditCardProvider>().findById(_selectedCardId ?? -1);
        base['expense_or_asset_account_id'] = _expenseAccountId;
        base['card_principal_account_id'] = card?.principalAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        if (_categoryId != null) base['category_id'] = _categoryId;
        break;
      case 'credit_card_payment':
        final card = context.read<CreditCardProvider>().findById(_selectedCardId ?? -1);
        base['card_principal_account_id'] = card?.principalAccountId;
        base['bank_account_id'] = _bankAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        break;
      case 'loan_disbursement':
        final loan = context.read<LoanProvider>().findById(_selectedLoanId ?? -1);
        base['bank_account_id'] = _bankAccountId;
        base['loan_principal_account_id'] = loan?.principalAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        break;
      case 'loan_emi_payment':
        final loan = context.read<LoanProvider>().findById(_selectedLoanId ?? -1);
        if (loan == null) return null;
        base['principal_liability_account_id'] = loan.principalAccountId;
        base['interest_expense_account_id'] = loan.interestExpenseAccountId;
        base['gst_expense_account_id'] = loan.chargesExpenseAccountId ?? loan.interestExpenseAccountId;
        base['fees_expense_account_id'] = loan.chargesExpenseAccountId ?? loan.interestExpenseAccountId;
        base['payment_account_id'] = _bankAccountId;
        base['principal_amount'] = _parseAmount(_principalAmtCtrl);
        base['interest_amount'] = _parseAmount(_interestAmtCtrl);
        base['gst_amount'] = _parseAmount(_gstAmtCtrl);
        base['fees_amount'] = _parseAmount(_feesAmtCtrl);
        base['total_amount'] = _emiTotal;
        break;
      case 'card_emi_payment':
        final card = context.read<CreditCardProvider>().findById(_selectedCardId ?? -1);
        if (card == null) return null;
        base['principal_liability_account_id'] = card.principalAccountId;
        base['interest_expense_account_id'] = card.interestExpenseAccountId;
        base['gst_expense_account_id'] = card.gstExpenseAccountId;
        base['fees_expense_account_id'] = card.feeExpenseAccountId ?? card.gstExpenseAccountId;
        base['payment_account_id'] = _bankAccountId;
        base['principal_amount'] = _parseAmount(_principalAmtCtrl);
        base['interest_amount'] = _parseAmount(_interestAmtCtrl);
        base['gst_amount'] = _parseAmount(_gstAmtCtrl);
        base['fees_amount'] = _parseAmount(_feesAmtCtrl);
        base['total_amount'] = _emiTotal;
        break;
      case 'investment_buy':
        final inv = context.read<InvestmentProvider>().findById(_selectedInvestmentId ?? -1);
        base['investment_asset_account_id'] = inv?.assetAccountId;
        base['bank_account_id'] = _bankAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        break;
      case 'investment_income':
        final inv = context.read<InvestmentProvider>().findById(_selectedInvestmentId ?? -1);
        base['bank_account_id'] = _bankAccountId;
        base['investment_income_account_id'] = inv?.incomeAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        if (_categoryId != null) base['category_id'] = _categoryId;
        break;
      case 'investment_redeem':
        final inv = context.read<InvestmentProvider>().findById(_selectedInvestmentId ?? -1);
        base['bank_account_id'] = _bankAccountId;
        base['investment_asset_account_id'] = inv?.assetAccountId;
        base['amount'] = _parseAmount(_amountCtrl);
        break;
      case 'adjustment':
        base['entries'] = _adjustmentEntries
            .where((e) =>
                e['account_id'] != null &&
                (double.tryParse(e['amount']?.toString() ?? '') ?? 0) > 0)
            .map((e) => {
                  'account_id': e['account_id'],
                  'side': e['side'],
                  'amount': double.parse(e['amount'].toString()),
                })
            .toList();
        break;
    }
    return base;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('New Transaction')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Transaction type
            DropdownButtonFormField<String>(
              initialValue: _txnType,
              decoration: const InputDecoration(labelText: 'Transaction Type'),
              isExpanded: true,
              items: _txnTypes
                  .map((t) =>
                      DropdownMenuItem(value: t.$1, child: Text(t.$2)))
                  .toList(),
              onChanged: (v) {
                if (v != null) {
                  setState(() {
                    _txnType = v;
                    _resetFields();
                  });
                }
              },
            ),
            const SizedBox(height: 16),

            // Date picker
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Date'),
              subtitle: Text(DateFormat('yyyy-MM-dd').format(_txnDate)),
              trailing: const Icon(Icons.calendar_today),
              onTap: _pickDate,
            ),
            const SizedBox(height: 8),

            // Description
            TextFormField(
              controller: _descCtrl,
              decoration: const InputDecoration(labelText: 'Description'),
            ),
            const SizedBox(height: 16),

            // Type-specific fields
            ..._buildTypeFields(),

            const SizedBox(height: 24),
            FilledButton(
              onPressed: _saving ? null : _submit,
              child: _saving
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Create Transaction'),
            ),
          ],
        ),
      ),
    );
  }

  List<Widget> _buildTypeFields() {
    switch (_txnType) {
      case 'income':
        return [
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'To Account (Bank/Cash)',
              filterType: 'ASSET',
              value: _toAccountId,
              onChanged: (v) => setState(() => _toAccountId = v)),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Income Account',
              filterType: 'INCOME',
              value: _incomeAccountId,
              onChanged: (v) => setState(() => _incomeAccountId = v)),
          const SizedBox(height: 16),
          CategoryDropdown(
              filterKind: 'INCOME',
              value: _categoryId,
              onChanged: (v) => setState(() => _categoryId = v)),
        ];

      case 'expense_cash_or_bank':
        return [
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Expense Account',
              filterType: 'EXPENSE',
              value: _expenseAccountId,
              onChanged: (v) => setState(() => _expenseAccountId = v)),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'From Account (Bank/Cash)',
              filterType: 'ASSET',
              value: _fromAccountId,
              onChanged: (v) => setState(() => _fromAccountId = v)),
          const SizedBox(height: 16),
          CategoryDropdown(
              filterKind: 'EXPENSE',
              value: _categoryId,
              onChanged: (v) => setState(() => _categoryId = v)),
        ];

      case 'transfer':
        return [
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'From Account',
              filterType: 'ASSET',
              value: _fromAccountId,
              onChanged: (v) => setState(() => _fromAccountId = v)),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'To Account',
              filterType: 'ASSET',
              value: _toAccountId,
              onChanged: (v) => setState(() => _toAccountId = v)),
        ];

      case 'credit_card_purchase':
        return [
          _cardPicker(),
          const SizedBox(height: 16),
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Expense/Asset Account',
              value: _expenseAccountId,
              onChanged: (v) => setState(() => _expenseAccountId = v)),
          const SizedBox(height: 16),
          CategoryDropdown(
              filterKind: 'EXPENSE',
              value: _categoryId,
              onChanged: (v) => setState(() => _categoryId = v)),
        ];

      case 'credit_card_payment':
        return [
          _cardPicker(),
          const SizedBox(height: 16),
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Bank Account',
              filterType: 'ASSET',
              value: _bankAccountId,
              onChanged: (v) => setState(() => _bankAccountId = v)),
        ];

      case 'loan_disbursement':
        return [
          _loanPicker(),
          const SizedBox(height: 16),
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Bank Account',
              filterType: 'ASSET',
              value: _bankAccountId,
              onChanged: (v) => setState(() => _bankAccountId = v)),
        ];

      case 'loan_emi_payment':
        return [
          _loanPicker(),
          const SizedBox(height: 16),
          ..._emiFields(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Payment Account (Bank)',
              filterType: 'ASSET',
              value: _bankAccountId,
              onChanged: (v) => setState(() => _bankAccountId = v)),
        ];

      case 'card_emi_payment':
        return [
          _cardPicker(),
          const SizedBox(height: 16),
          ..._emiFields(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Payment Account (Bank)',
              filterType: 'ASSET',
              value: _bankAccountId,
              onChanged: (v) => setState(() => _bankAccountId = v)),
        ];

      case 'investment_buy':
        return [
          _investmentPicker(),
          const SizedBox(height: 16),
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Bank Account',
              filterType: 'ASSET',
              value: _bankAccountId,
              onChanged: (v) => setState(() => _bankAccountId = v)),
        ];

      case 'investment_income':
        return [
          _investmentPicker(),
          const SizedBox(height: 16),
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Bank Account',
              filterType: 'ASSET',
              value: _bankAccountId,
              onChanged: (v) => setState(() => _bankAccountId = v)),
          const SizedBox(height: 16),
          CategoryDropdown(
              filterKind: 'INCOME',
              value: _categoryId,
              onChanged: (v) => setState(() => _categoryId = v)),
        ];

      case 'investment_redeem':
        return [
          _investmentPicker(),
          const SizedBox(height: 16),
          _amountField(),
          const SizedBox(height: 16),
          AccountDropdown(
              label: 'Bank Account',
              filterType: 'ASSET',
              value: _bankAccountId,
              onChanged: (v) => setState(() => _bankAccountId = v)),
        ];

      case 'adjustment':
        return _buildAdjustmentFields();

      default:
        return [];
    }
  }

  Widget _amountField() {
    return TextFormField(
      controller: _amountCtrl,
      decoration: const InputDecoration(
          labelText: 'Amount', prefixText: '\u20B9 '),
      keyboardType: const TextInputType.numberWithOptions(decimal: true),
      validator: (v) {
        final n = double.tryParse(v ?? '');
        if (n == null || n <= 0) return 'Enter a valid amount';
        return null;
      },
    );
  }

  Widget _cardPicker() {
    final cards = context.watch<CreditCardProvider>().cards;
    return DropdownButtonFormField<int>(
      initialValue: _selectedCardId,
      decoration: const InputDecoration(labelText: 'Credit Card'),
      isExpanded: true,
      items: cards
          .map((c) => DropdownMenuItem(value: c.id, child: Text(c.name)))
          .toList(),
      onChanged: (v) => setState(() => _selectedCardId = v),
      validator: (v) => v == null ? 'Select a card' : null,
    );
  }

  Widget _loanPicker() {
    final loans = context.watch<LoanProvider>().loans;
    return DropdownButtonFormField<int>(
      initialValue: _selectedLoanId,
      decoration: const InputDecoration(labelText: 'Loan'),
      isExpanded: true,
      items: loans
          .map((l) => DropdownMenuItem(value: l.id, child: Text(l.name)))
          .toList(),
      onChanged: (v) => setState(() => _selectedLoanId = v),
      validator: (v) => v == null ? 'Select a loan' : null,
    );
  }

  Widget _investmentPicker() {
    final invs = context.watch<InvestmentProvider>().investments;
    return DropdownButtonFormField<int>(
      initialValue: _selectedInvestmentId,
      decoration: const InputDecoration(labelText: 'Investment'),
      isExpanded: true,
      items: invs
          .map((i) => DropdownMenuItem(value: i.id, child: Text(i.name)))
          .toList(),
      onChanged: (v) => setState(() => _selectedInvestmentId = v),
      validator: (v) => v == null ? 'Select an investment' : null,
    );
  }

  List<Widget> _emiFields() {
    return [
      TextFormField(
        controller: _principalAmtCtrl,
        decoration: const InputDecoration(
            labelText: 'Principal Amount', prefixText: '\u20B9 '),
        keyboardType: const TextInputType.numberWithOptions(decimal: true),
        onChanged: (_) => setState(() {}),
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _interestAmtCtrl,
        decoration: const InputDecoration(
            labelText: 'Interest Amount', prefixText: '\u20B9 '),
        keyboardType: const TextInputType.numberWithOptions(decimal: true),
        onChanged: (_) => setState(() {}),
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _gstAmtCtrl,
        decoration: const InputDecoration(
            labelText: 'GST Amount', prefixText: '\u20B9 '),
        keyboardType: const TextInputType.numberWithOptions(decimal: true),
        onChanged: (_) => setState(() {}),
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _feesAmtCtrl,
        decoration: const InputDecoration(
            labelText: 'Fees Amount', prefixText: '\u20B9 '),
        keyboardType: const TextInputType.numberWithOptions(decimal: true),
        onChanged: (_) => setState(() {}),
      ),
      const SizedBox(height: 12),
      Card(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Total EMI',
                  style: TextStyle(fontWeight: FontWeight.bold)),
              Text('\u20B9 ${_emiTotal.toStringAsFixed(2)}',
                  style: const TextStyle(fontWeight: FontWeight.bold)),
            ],
          ),
        ),
      ),
    ];
  }

  List<Widget> _buildAdjustmentFields() {
    return [
      ..._adjustmentEntries.asMap().entries.map((entry) {
        final i = entry.key;
        final e = entry.value;
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                Row(
                  children: [
                    Text('Entry ${i + 1}',
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                    const Spacer(),
                    if (_adjustmentEntries.length > 2)
                      IconButton(
                        icon: const Icon(Icons.remove_circle_outline),
                        onPressed: () => setState(
                            () => _adjustmentEntries.removeAt(i)),
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                AccountDropdown(
                  label: 'Account',
                  value: e['account_id'] as int?,
                  onChanged: (v) =>
                      setState(() => _adjustmentEntries[i]['account_id'] = v),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<String>(
                  initialValue: e['side'] as String,
                  decoration: const InputDecoration(labelText: 'Side'),
                  items: const [
                    DropdownMenuItem(value: 'DEBIT', child: Text('DEBIT')),
                    DropdownMenuItem(value: 'CREDIT', child: Text('CREDIT')),
                  ],
                  onChanged: (v) =>
                      setState(() => _adjustmentEntries[i]['side'] = v),
                ),
                const SizedBox(height: 8),
                TextFormField(
                  decoration: const InputDecoration(
                      labelText: 'Amount', prefixText: '\u20B9 '),
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  onChanged: (v) => _adjustmentEntries[i]['amount'] = v,
                ),
              ],
            ),
          ),
        );
      }),
      const SizedBox(height: 8),
      OutlinedButton.icon(
        onPressed: () => setState(() => _adjustmentEntries
            .add({'account_id': null, 'side': 'DEBIT', 'amount': ''})),
        icon: const Icon(Icons.add),
        label: const Text('Add Entry'),
      ),
    ];
  }
}
