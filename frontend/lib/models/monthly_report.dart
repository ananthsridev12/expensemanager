class ExpenseByCategory {
  final int? categoryId;
  final String categoryName;
  final double total;

  ExpenseByCategory({
    this.categoryId,
    required this.categoryName,
    required this.total,
  });

  factory ExpenseByCategory.fromJson(Map<String, dynamic> json) {
    return ExpenseByCategory(
      categoryId: json['category_id'] != null
          ? int.parse(json['category_id'].toString())
          : null,
      categoryName: json['category_name'] as String? ?? 'Uncategorized',
      total: double.tryParse(json['total']?.toString() ?? '0') ?? 0,
    );
  }
}

class MonthlyReport {
  final int year;
  final int month;
  final double totalIncome;
  final double totalExpense;
  final List<ExpenseByCategory> expenseByCategory;

  MonthlyReport({
    required this.year,
    required this.month,
    required this.totalIncome,
    required this.totalExpense,
    required this.expenseByCategory,
  });

  double get netSavings => totalIncome - totalExpense;

  factory MonthlyReport.fromJson(Map<String, dynamic> json) {
    final period = json['period'] as Map<String, dynamic>? ?? {};
    final summary = json['summary'] as Map<String, dynamic>? ?? {};
    return MonthlyReport(
      year: int.tryParse(period['year']?.toString() ?? '') ?? DateTime.now().year,
      month: int.tryParse(period['month']?.toString() ?? '') ?? DateTime.now().month,
      totalIncome:
          double.tryParse(summary['total_income']?.toString() ?? '0') ?? 0,
      totalExpense:
          double.tryParse(summary['total_expense']?.toString() ?? '0') ?? 0,
      expenseByCategory: (json['expense_by_category'] as List? ?? [])
          .map((e) => ExpenseByCategory.fromJson(e))
          .toList(),
    );
  }
}
