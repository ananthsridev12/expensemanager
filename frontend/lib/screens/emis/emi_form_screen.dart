import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/emi_provider.dart';
import '../../providers/loan_provider.dart';
import '../../providers/credit_card_provider.dart';

class EmiFormScreen extends StatefulWidget {
  const EmiFormScreen({super.key});

  @override
  State<EmiFormScreen> createState() => _EmiFormScreenState();
}

class _EmiFormScreenState extends State<EmiFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _principalCtrl = TextEditingController();
  final _interestCtrl = TextEditingController();
  final _gstCtrl = TextEditingController();
  final _feesCtrl = TextEditingController();
  DateTime _dueDate = DateTime.now().add(const Duration(days: 30));
  int? _loanId;
  int? _creditCardId;
  bool _saving = false;

  double get _total =>
      (double.tryParse(_principalCtrl.text) ?? 0) +
      (double.tryParse(_interestCtrl.text) ?? 0) +
      (double.tryParse(_gstCtrl.text) ?? 0) +
      (double.tryParse(_feesCtrl.text) ?? 0);

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<LoanProvider>().fetchAll();
      context.read<CreditCardProvider>().fetchAll();
    });
  }

  @override
  void dispose() {
    _principalCtrl.dispose();
    _interestCtrl.dispose();
    _gstCtrl.dispose();
    _feesCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _dueDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2035),
    );
    if (picked != null) setState(() => _dueDate = picked);
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final success = await context.read<EmiProvider>().createEmi({
      if (_loanId != null) 'loan_id': _loanId,
      if (_creditCardId != null) 'credit_card_id': _creditCardId,
      'due_date': DateFormat('yyyy-MM-dd').format(_dueDate),
      'principal_amount': double.tryParse(_principalCtrl.text) ?? 0,
      'interest_amount': double.tryParse(_interestCtrl.text) ?? 0,
      'gst_amount': double.tryParse(_gstCtrl.text) ?? 0,
      'fees_amount': double.tryParse(_feesCtrl.text) ?? 0,
      'total_amount': _total,
    });

    if (!mounted) return;
    setState(() => _saving = false);
    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('EMI created')));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(context.read<EmiProvider>().error ?? 'Failed')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final loans = context.watch<LoanProvider>().loans;
    final cards = context.watch<CreditCardProvider>().cards;
    return Scaffold(
      appBar: AppBar(title: const Text('New EMI')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            DropdownButtonFormField<int>(
              initialValue: _loanId,
              decoration: const InputDecoration(labelText: 'Loan (optional)'),
              isExpanded: true,
              items: [
                const DropdownMenuItem(value: null, child: Text('None')),
                ...loans.map((l) =>
                    DropdownMenuItem(value: l.id, child: Text(l.name))),
              ],
              onChanged: (v) => setState(() {
                _loanId = v;
                if (v != null) _creditCardId = null;
              }),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              initialValue: _creditCardId,
              decoration:
                  const InputDecoration(labelText: 'Credit Card (optional)'),
              isExpanded: true,
              items: [
                const DropdownMenuItem(value: null, child: Text('None')),
                ...cards.map((c) =>
                    DropdownMenuItem(value: c.id, child: Text(c.name))),
              ],
              onChanged: (v) => setState(() {
                _creditCardId = v;
                if (v != null) _loanId = null;
              }),
            ),
            const SizedBox(height: 16),
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Due Date'),
              subtitle: Text(DateFormat('yyyy-MM-dd').format(_dueDate)),
              trailing: const Icon(Icons.calendar_today),
              onTap: _pickDate,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _principalCtrl,
              decoration: const InputDecoration(
                  labelText: 'Principal Amount', prefixText: '\u20B9 '),
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              onChanged: (_) => setState(() {}),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _interestCtrl,
              decoration: const InputDecoration(
                  labelText: 'Interest Amount', prefixText: '\u20B9 '),
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              onChanged: (_) => setState(() {}),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _gstCtrl,
              decoration: const InputDecoration(
                  labelText: 'GST Amount', prefixText: '\u20B9 '),
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              onChanged: (_) => setState(() {}),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _feesCtrl,
              decoration: const InputDecoration(
                  labelText: 'Fees Amount', prefixText: '\u20B9 '),
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
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
                    const Text('Total',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    Text('\u20B9 ${_total.toStringAsFixed(2)}',
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Create EMI'),
            ),
          ],
        ),
      ),
    );
  }
}
