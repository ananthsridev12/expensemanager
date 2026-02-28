import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/loan_provider.dart';
import '../../providers/account_provider.dart';
import '../../widgets/account_dropdown.dart';

class LoanFormScreen extends StatefulWidget {
  const LoanFormScreen({super.key});

  @override
  State<LoanFormScreen> createState() => _LoanFormScreenState();
}

class _LoanFormScreenState extends State<LoanFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _lenderCtrl = TextEditingController();
  final _sanctionCtrl = TextEditingController();
  final _rateCtrl = TextEditingController();
  int? _principalAccountId;
  int? _interestAccountId;
  int? _chargesAccountId;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<AccountProvider>().fetchAll());
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _lenderCtrl.dispose();
    _sanctionCtrl.dispose();
    _rateCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final success = await context.read<LoanProvider>().createLoan({
      'name': _nameCtrl.text.trim(),
      if (_lenderCtrl.text.trim().isNotEmpty) 'lender': _lenderCtrl.text.trim(),
      'principal_account_id': _principalAccountId,
      'interest_expense_account_id': _interestAccountId,
      if (_chargesAccountId != null)
        'charges_expense_account_id': _chargesAccountId,
      if (_sanctionCtrl.text.isNotEmpty)
        'sanction_amount': double.parse(_sanctionCtrl.text),
      if (_rateCtrl.text.isNotEmpty)
        'interest_rate_annual': double.parse(_rateCtrl.text),
    });

    if (!mounted) return;
    setState(() => _saving = false);
    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Loan created')));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(context.read<LoanProvider>().error ?? 'Failed')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('New Loan')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'Loan Name'),
              validator: (v) =>
                  v == null || v.trim().isEmpty ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _lenderCtrl,
              decoration:
                  const InputDecoration(labelText: 'Lender (optional)'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _sanctionCtrl,
              decoration: const InputDecoration(
                  labelText: 'Sanction Amount', prefixText: '\u20B9 '),
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _rateCtrl,
              decoration: const InputDecoration(
                  labelText: 'Interest Rate (% p.a.)'),
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
            ),
            const SizedBox(height: 16),
            AccountDropdown(
              label: 'Principal Liability Account',
              filterType: 'LIABILITY',
              value: _principalAccountId,
              onChanged: (v) => setState(() => _principalAccountId = v),
            ),
            const SizedBox(height: 16),
            AccountDropdown(
              label: 'Interest Expense Account',
              filterType: 'EXPENSE',
              value: _interestAccountId,
              onChanged: (v) => setState(() => _interestAccountId = v),
            ),
            const SizedBox(height: 16),
            AccountDropdown(
              label: 'Charges Expense Account (optional)',
              filterType: 'EXPENSE',
              value: _chargesAccountId,
              onChanged: (v) => setState(() => _chargesAccountId = v),
              required: false,
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Create Loan'),
            ),
          ],
        ),
      ),
    );
  }
}
