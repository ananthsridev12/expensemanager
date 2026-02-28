class Account {
  final int id;
  final String name;
  final String? code;
  final String currency;
  final int accountTypeId;
  final String accountType;
  final int? parentAccountId;
  final bool isActive;
  final double netDebitBalance;
  final Map<String, dynamic>? metadata;

  Account({
    required this.id,
    required this.name,
    this.code,
    required this.currency,
    required this.accountTypeId,
    required this.accountType,
    this.parentAccountId,
    required this.isActive,
    required this.netDebitBalance,
    this.metadata,
  });

  double get displayBalance {
    if (accountType == 'LIABILITY' ||
        accountType == 'INCOME' ||
        accountType == 'EQUITY') {
      return -netDebitBalance;
    }
    return netDebitBalance;
  }

  factory Account.fromJson(Map<String, dynamic> json) {
    return Account(
      id: int.parse(json['id'].toString()),
      name: json['name'] as String,
      code: json['code'] as String?,
      currency: json['currency'] as String? ?? 'INR',
      accountTypeId: int.parse(json['account_type_id'].toString()),
      accountType: json['account_type'] as String,
      parentAccountId: json['parent_account_id'] != null
          ? int.parse(json['parent_account_id'].toString())
          : null,
      isActive: json['is_active'].toString() == '1',
      netDebitBalance:
          double.tryParse(json['net_debit_balance']?.toString() ?? '0') ?? 0,
      metadata: json['metadata'] is Map
          ? json['metadata'] as Map<String, dynamic>
          : null,
    );
  }
}
