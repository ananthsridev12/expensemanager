import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/account.dart';
import '../../providers/account_provider.dart';

class AccountFormScreen extends StatefulWidget {
  final Account? account;

  const AccountFormScreen({super.key, this.account});

  @override
  State<AccountFormScreen> createState() => _AccountFormScreenState();
}

class _AccountFormScreenState extends State<AccountFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _nameCtrl;
  late TextEditingController _codeCtrl;
  int? _accountTypeId;
  int? _parentAccountId;
  bool _isActive = true;
  bool _saving = false;

  bool get isEditing => widget.account != null;

  @override
  void initState() {
    super.initState();
    _nameCtrl = TextEditingController(text: widget.account?.name ?? '');
    _codeCtrl = TextEditingController(text: widget.account?.code ?? '');
    _accountTypeId = widget.account?.accountTypeId;
    _parentAccountId = widget.account?.parentAccountId;
    _isActive = widget.account?.isActive ?? true;
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _codeCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final provider = context.read<AccountProvider>();
    final data = {
      'name': _nameCtrl.text.trim(),
      'account_type_id': _accountTypeId,
      if (_codeCtrl.text.trim().isNotEmpty) 'code': _codeCtrl.text.trim(),
      if (_parentAccountId != null) 'parent_account_id': _parentAccountId,
    };

    bool success;
    if (isEditing) {
      data['id'] = widget.account!.id;
      data['is_active'] = _isActive ? 1 : 0;
      success = await provider.updateAccount(data);
    } else {
      success = await provider.createAccount(data);
    }

    if (!mounted) return;
    setState(() => _saving = false);
    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(isEditing ? 'Account updated' : 'Account created')));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(provider.error ?? 'Failed to save')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<AccountProvider>();
    return Scaffold(
      appBar: AppBar(
          title: Text(isEditing ? 'Edit Account' : 'New Account')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'Account Name'),
              validator: (v) =>
                  v == null || v.trim().isEmpty ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _codeCtrl,
              decoration:
                  const InputDecoration(labelText: 'Code (optional)'),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              initialValue: _accountTypeId,
              decoration: const InputDecoration(labelText: 'Account Type'),
              items: provider.accountTypes
                  .map((t) => DropdownMenuItem(
                      value: t.id, child: Text(t.code)))
                  .toList(),
              onChanged: (v) => setState(() => _accountTypeId = v),
              validator: (v) => v == null ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              initialValue: _parentAccountId,
              decoration:
                  const InputDecoration(labelText: 'Parent Account (optional)'),
              isExpanded: true,
              items: [
                const DropdownMenuItem(value: null, child: Text('None')),
                ...provider.activeAccounts.map((a) => DropdownMenuItem(
                    value: a.id, child: Text(a.name))),
              ],
              onChanged: (v) => setState(() => _parentAccountId = v),
            ),
            if (isEditing) ...[
              const SizedBox(height: 16),
              SwitchListTile(
                title: const Text('Active'),
                value: _isActive,
                onChanged: (v) => setState(() => _isActive = v),
              ),
            ],
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2))
                  : Text(isEditing ? 'Update' : 'Create'),
            ),
          ],
        ),
      ),
    );
  }
}
