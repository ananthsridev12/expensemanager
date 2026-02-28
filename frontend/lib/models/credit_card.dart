class CreditCard {
  final int id;
  final String name;
  final double limitAmount;
  final int billingDay;
  final int dueDay;
  final int principalAccountId;
  final int interestExpenseAccountId;
  final int gstExpenseAccountId;
  final int? feeExpenseAccountId;
  final bool isActive;
  final double principalBalance;
  final double availableLimit;

  CreditCard({
    required this.id,
    required this.name,
    required this.limitAmount,
    required this.billingDay,
    required this.dueDay,
    required this.principalAccountId,
    required this.interestExpenseAccountId,
    required this.gstExpenseAccountId,
    this.feeExpenseAccountId,
    required this.isActive,
    required this.principalBalance,
    required this.availableLimit,
  });

  double get utilization =>
      limitAmount > 0 ? (principalBalance.abs() / limitAmount) : 0;

  factory CreditCard.fromJson(Map<String, dynamic> json) {
    return CreditCard(
      id: int.parse(json['id'].toString()),
      name: json['name'] as String,
      limitAmount:
          double.tryParse(json['limit_amount']?.toString() ?? '0') ?? 0,
      billingDay: int.parse(json['billing_day'].toString()),
      dueDay: int.parse(json['due_day'].toString()),
      principalAccountId:
          int.parse(json['principal_account_id'].toString()),
      interestExpenseAccountId:
          int.parse(json['interest_expense_account_id'].toString()),
      gstExpenseAccountId:
          int.parse(json['gst_expense_account_id'].toString()),
      feeExpenseAccountId: json['fee_expense_account_id'] != null
          ? int.parse(json['fee_expense_account_id'].toString())
          : null,
      isActive: json['is_active'].toString() == '1',
      principalBalance:
          double.tryParse(json['principal_balance']?.toString() ?? '0') ?? 0,
      availableLimit:
          double.tryParse(json['available_limit']?.toString() ?? '0') ?? 0,
    );
  }
}
