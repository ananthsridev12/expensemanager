class Transaction {
  final int id;
  final String txnType;
  final String txnDate;
  final String? description;
  final String status;
  final String? externalRef;
  final String createdAt;

  Transaction({
    required this.id,
    required this.txnType,
    required this.txnDate,
    this.description,
    required this.status,
    this.externalRef,
    required this.createdAt,
  });

  String get typeLabel => txnType.replaceAll('_', ' ').toUpperCase();

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      id: int.parse(json['id'].toString()),
      txnType: json['txn_type'] as String,
      txnDate: json['txn_date'] as String,
      description: json['description'] as String?,
      status: json['status'] as String? ?? 'POSTED',
      externalRef: json['external_ref'] as String?,
      createdAt: json['created_at'] as String? ?? '',
    );
  }
}
