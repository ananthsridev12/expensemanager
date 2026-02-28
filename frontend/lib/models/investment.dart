class Investment {
  final int id;
  final String name;
  final String instrumentType;
  final int assetAccountId;
  final int? incomeAccountId;
  final bool isActive;
  final double currentBookBalance;

  Investment({
    required this.id,
    required this.name,
    required this.instrumentType,
    required this.assetAccountId,
    this.incomeAccountId,
    required this.isActive,
    required this.currentBookBalance,
  });

  factory Investment.fromJson(Map<String, dynamic> json) {
    return Investment(
      id: int.parse(json['id'].toString()),
      name: json['name'] as String,
      instrumentType: json['instrument_type'] as String,
      assetAccountId: int.parse(json['asset_account_id'].toString()),
      incomeAccountId: json['income_account_id'] != null
          ? int.parse(json['income_account_id'].toString())
          : null,
      isActive: json['is_active'].toString() == '1',
      currentBookBalance:
          double.tryParse(json['current_book_balance']?.toString() ?? '0') ?? 0,
    );
  }
}
