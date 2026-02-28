class Loan {
  final int id;
  final String name;
  final String? lender;
  final int principalAccountId;
  final int interestExpenseAccountId;
  final int? chargesExpenseAccountId;
  final double sanctionAmount;
  final String? startDate;
  final String? endDate;
  final double interestRateAnnual;
  final bool isActive;
  final double principalBalance;

  Loan({
    required this.id,
    required this.name,
    this.lender,
    required this.principalAccountId,
    required this.interestExpenseAccountId,
    this.chargesExpenseAccountId,
    required this.sanctionAmount,
    this.startDate,
    this.endDate,
    required this.interestRateAnnual,
    required this.isActive,
    required this.principalBalance,
  });

  factory Loan.fromJson(Map<String, dynamic> json) {
    return Loan(
      id: int.parse(json['id'].toString()),
      name: json['name'] as String,
      lender: json['lender'] as String?,
      principalAccountId:
          int.parse(json['principal_account_id'].toString()),
      interestExpenseAccountId:
          int.parse(json['interest_expense_account_id'].toString()),
      chargesExpenseAccountId: json['charges_expense_account_id'] != null
          ? int.parse(json['charges_expense_account_id'].toString())
          : null,
      sanctionAmount:
          double.tryParse(json['sanction_amount']?.toString() ?? '0') ?? 0,
      startDate: json['start_date'] as String?,
      endDate: json['end_date'] as String?,
      interestRateAnnual: double.tryParse(
              json['interest_rate_annual']?.toString() ?? '0') ??
          0,
      isActive: json['is_active'].toString() == '1',
      principalBalance:
          double.tryParse(json['principal_balance']?.toString() ?? '0') ?? 0,
    );
  }
}
