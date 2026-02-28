import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/category.dart';
import '../providers/category_provider.dart';

class CategoryDropdown extends StatelessWidget {
  final int? value;
  final String label;
  final String? filterKind;
  final ValueChanged<int?> onChanged;
  final bool required;

  const CategoryDropdown({
    super.key,
    this.value,
    this.label = 'Category',
    this.filterKind,
    required this.onChanged,
    this.required = false,
  });

  @override
  Widget build(BuildContext context) {
    return Consumer<CategoryProvider>(
      builder: (context, provider, _) {
        List<Category> categories = provider.activeCategories;
        if (filterKind != null) {
          categories = provider.byKind(filterKind!);
        }
        return DropdownButtonFormField<int>(
          initialValue: value,
          decoration: InputDecoration(labelText: label),
          isExpanded: true,
          items: [
            const DropdownMenuItem(value: null, child: Text('None')),
            ...categories.map((c) => DropdownMenuItem(
                  value: c.id,
                  child: Text('${c.name} (${c.kind})',
                      overflow: TextOverflow.ellipsis),
                )),
          ],
          onChanged: onChanged,
          validator: required
              ? (v) => v == null ? 'Please select a category' : null
              : null,
        );
      },
    );
  }
}
