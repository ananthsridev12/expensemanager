import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/credit_card_provider.dart';
import '../../providers/account_provider.dart';
import '../../widgets/account_dropdown.dart';

class CreditCardFormScreen extends StatefulWidget {
  const CreditCardFormScreen({super.key});

  @override
  State<CreditCardFormScreen> createState() => _CreditCardFormScreenState();
}

class _CreditCardFormScreenState extends State<CreditCardFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _limitCtrl = TextEditingController();
  final _billingDayCtrl = TextEditingController();
  final _dueDayCtrl = TextEditingController();
  int? _principalAccountId;
  int? _interestAccountId;
  int? _gstAccountId;
  int? _feeAccountId;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<AccountProvider>().fetchAll());
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _limitCtrl.dispose();
    _billingDayCtrl.dispose();
    _dueDayCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final success =
        await context.read<CreditCardProvider>().createCreditCard({
      'name': _nameCtrl.text.trim(),
      'limit_amount': double.parse(_limitCtrl.text),
      'billing_day': int.parse(_billingDayCtrl.text),
      'due_day': int.parse(_dueDayCtrl.text),
      'principal_account_id': _principalAccountId,
      'interest_expense_account_id': _interestAccountId,
      'gst_expense_account_id': _gstAccountId,
      if (_feeAccountId != null) 'fee_expense_account_id': _feeAccountId,
    });

    if (!mounted) return;
    setState(() => _saving = false);
    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Credit card created')));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(
              context.read<CreditCardProvider>().error ?? 'Failed')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('New Credit Card')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'Card Name'),
              validator: (v) =>
                  v == null || v.trim().isEmpty ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _limitCtrl,
              decoration: const InputDecoration(
                  labelText: 'Credit Limit', prefixText: '\u20B9 '),
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              validator: (v) =>
                  double.tryParse(v ?? '') == null ? 'Enter a valid amount' : null,
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _billingDayCtrl,
                    decoration:
                        const InputDecoration(labelText: 'Billing Day'),
                    keyboardType: TextInputType.number,
                    validator: (v) {
                      final n = int.tryParse(v ?? '');
                      if (n == null || n < 1 || n > 31) return '1-31';
                      return null;
                    },
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: TextFormField(
                    controller: _dueDayCtrl,
                    decoration: const InputDecoration(labelText: 'Due Day'),
                    keyboardType: TextInputType.number,
                    validator: (v) {
                      final n = int.tryParse(v ?? '');
                      if (n == null || n < 1 || n > 31) return '1-31';
                      return null;
                    },
                  ),
                ),
              ],
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
              label: 'GST Expense Account',
              filterType: 'EXPENSE',
              value: _gstAccountId,
              onChanged: (v) => setState(() => _gstAccountId = v),
            ),
            const SizedBox(height: 16),
            AccountDropdown(
              label: 'Fee Expense Account (optional)',
              filterType: 'EXPENSE',
              value: _feeAccountId,
              onChanged: (v) => setState(() => _feeAccountId = v),
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
                  : const Text('Create Card'),
            ),
          ],
        ),
      ),
    );
  }
}
