import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/account.dart';
import '../providers/account_provider.dart';

class AccountDropdown extends StatelessWidget {
  final int? value;
  final String label;
  final String? filterType;
  final ValueChanged<int?> onChanged;
  final bool required;

  const AccountDropdown({
    super.key,
    this.value,
    required this.label,
    this.filterType,
    required this.onChanged,
    this.required = true,
  });

  @override
  Widget build(BuildContext context) {
    return Consumer<AccountProvider>(
      builder: (context, provider, _) {
        List<Account> accounts = provider.activeAccounts;
        if (filterType != null) {
          accounts = provider.byType(filterType!);
        }
        return DropdownButtonFormField<int>(
          initialValue: value,
          decoration: InputDecoration(labelText: label),
          isExpanded: true,
          items: accounts
              .map((a) => DropdownMenuItem(
                    value: a.id,
                    child: Text('${a.name} (${a.accountType})',
                        overflow: TextOverflow.ellipsis),
                  ))
              .toList(),
          onChanged: onChanged,
          validator: required
              ? (v) => v == null ? 'Please select an account' : null
              : null,
        );
      },
    );
  }
}
