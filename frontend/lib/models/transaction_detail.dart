import 'transaction.dart';
import 'journal_entry.dart';

class TransactionDetail {
  final Transaction transaction;
  final List<JournalEntry> entries;

  TransactionDetail({required this.transaction, required this.entries});

  factory TransactionDetail.fromJson(Map<String, dynamic> json) {
    return TransactionDetail(
      transaction: Transaction.fromJson(json['transaction']),
      entries: (json['entries'] as List)
          .map((e) => JournalEntry.fromJson(e))
          .toList(),
    );
  }
}
