class Emi {
  final int id;
  final int? loanId;
  final int? creditCardId;
  final String dueDate;
  final double principalAmount;
  final double interestAmount;
  final double gstAmount;
  final double feesAmount;
  final double totalAmount;
  final int? paymentTransactionId;
  final String status;
  final String? loanName;
  final String? creditCardName;

  Emi({
    required this.id,
    this.loanId,
    this.creditCardId,
    required this.dueDate,
    required this.principalAmount,
    required this.interestAmount,
    required this.gstAmount,
    required this.feesAmount,
    required this.totalAmount,
    this.paymentTransactionId,
    required this.status,
    this.loanName,
    this.creditCardName,
  });

  bool get isPending => status == 'PENDING';

  bool get isOverdue =>
      isPending && DateTime.tryParse(dueDate)?.isBefore(DateTime.now()) == true;

  String get linkedName => loanName ?? creditCardName ?? 'Unknown';

  factory Emi.fromJson(Map<String, dynamic> json) {
    return Emi(
      id: int.parse(json['id'].toString()),
      loanId: json['loan_id'] != null
          ? int.parse(json['loan_id'].toString())
          : null,
      creditCardId: json['credit_card_id'] != null
          ? int.parse(json['credit_card_id'].toString())
          : null,
      dueDate: json['due_date'] as String,
      principalAmount:
          double.tryParse(json['principal_amount']?.toString() ?? '0') ?? 0,
      interestAmount:
          double.tryParse(json['interest_amount']?.toString() ?? '0') ?? 0,
      gstAmount:
          double.tryParse(json['gst_amount']?.toString() ?? '0') ?? 0,
      feesAmount:
          double.tryParse(json['fees_amount']?.toString() ?? '0') ?? 0,
      totalAmount:
          double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0,
      paymentTransactionId: json['payment_transaction_id'] != null
          ? int.parse(json['payment_transaction_id'].toString())
          : null,
      status: json['status'] as String? ?? 'PENDING',
      loanName: json['loan_name'] as String?,
      creditCardName: json['credit_card_name'] as String?,
    );
  }
}
