import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/investment_provider.dart';
import '../../providers/account_provider.dart';
import '../../widgets/account_dropdown.dart';

class InvestmentFormScreen extends StatefulWidget {
  const InvestmentFormScreen({super.key});

  @override
  State<InvestmentFormScreen> createState() => _InvestmentFormScreenState();
}

class _InvestmentFormScreenState extends State<InvestmentFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  String _instrumentType = 'Mutual Fund';
  int? _assetAccountId;
  int? _incomeAccountId;
  bool _saving = false;

  static const _instruments = [
    'Mutual Fund',
    'Stocks',
    'FD',
    'PPF',
    'NPS',
    'Gold',
    'Other',
  ];

  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<AccountProvider>().fetchAll());
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final success =
        await context.read<InvestmentProvider>().createInvestment({
      'name': _nameCtrl.text.trim(),
      'instrument_type': _instrumentType,
      'asset_account_id': _assetAccountId,
      if (_incomeAccountId != null) 'income_account_id': _incomeAccountId,
    });

    if (!mounted) return;
    setState(() => _saving = false);
    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Investment created')));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content:
              Text(context.read<InvestmentProvider>().error ?? 'Failed')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('New Investment')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _nameCtrl,
              decoration:
                  const InputDecoration(labelText: 'Investment Name'),
              validator: (v) =>
                  v == null || v.trim().isEmpty ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              initialValue: _instrumentType,
              decoration:
                  const InputDecoration(labelText: 'Instrument Type'),
              items: _instruments
                  .map((t) =>
                      DropdownMenuItem(value: t, child: Text(t)))
                  .toList(),
              onChanged: (v) {
                if (v != null) setState(() => _instrumentType = v);
              },
            ),
            const SizedBox(height: 16),
            AccountDropdown(
              label: 'Asset Account',
              filterType: 'ASSET',
              value: _assetAccountId,
              onChanged: (v) => setState(() => _assetAccountId = v),
            ),
            const SizedBox(height: 16),
            AccountDropdown(
              label: 'Income Account (optional)',
              filterType: 'INCOME',
              value: _incomeAccountId,
              onChanged: (v) => setState(() => _incomeAccountId = v),
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
                  : const Text('Create Investment'),
            ),
          ],
        ),
      ),
    );
  }
}
