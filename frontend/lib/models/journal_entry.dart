class JournalEntry {
  final int id;
  final int accountId;
  final String accountName;
  final int? categoryId;
  final String? categoryName;
  final String side;
  final double amount;
  final String? note;

  JournalEntry({
    required this.id,
    required this.accountId,
    required this.accountName,
    this.categoryId,
    this.categoryName,
    required this.side,
    required this.amount,
    this.note,
  });

  bool get isDebit => side == 'DEBIT';

  factory JournalEntry.fromJson(Map<String, dynamic> json) {
    return JournalEntry(
      id: int.parse(json['id'].toString()),
      accountId: int.parse(json['account_id'].toString()),
      accountName: json['account_name'] as String,
      categoryId: json['category_id'] != null
          ? int.parse(json['category_id'].toString())
          : null,
      categoryName: json['category_name'] as String?,
      side: json['side'] as String,
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0,
      note: json['note'] as String?,
    );
  }
}
