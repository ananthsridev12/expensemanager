import 'emi.dart';

class BalanceByType {
  final String accountType;
  final double total;

  BalanceByType({required this.accountType, required this.total});

  factory BalanceByType.fromJson(Map<String, dynamic> json) {
    return BalanceByType(
      accountType: json['account_type'] as String,
      total: double.tryParse(json['total']?.toString() ?? '0') ?? 0,
    );
  }
}

class DashboardSummary {
  final List<BalanceByType> balancesByType;
  final List<Emi> upcomingEmiDues;

  DashboardSummary({
    required this.balancesByType,
    required this.upcomingEmiDues,
  });

  double get totalAssets =>
      balancesByType
          .where((b) => b.accountType == 'ASSET')
          .fold(0.0, (sum, b) => sum + b.total);

  double get totalLiabilities =>
      balancesByType
          .where((b) => b.accountType == 'LIABILITY')
          .fold(0.0, (sum, b) => sum + b.total);

  double get netWorth => totalAssets - totalLiabilities.abs();

  factory DashboardSummary.fromJson(Map<String, dynamic> json) {
    return DashboardSummary(
      balancesByType: (json['balances_by_type'] as List? ?? [])
          .map((e) => BalanceByType.fromJson(e))
          .toList(),
      upcomingEmiDues: (json['upcoming_emi_dues'] as List? ?? [])
          .map((e) => Emi.fromJson(e))
          .toList(),
    );
  }
}
