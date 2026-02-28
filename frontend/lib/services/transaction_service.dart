import '../models/transaction.dart';
import '../models/transaction_detail.dart';
import 'api_client.dart';

class TransactionService {
  final ApiClient _api;
  TransactionService(this._api);

  Future<List<Transaction>> getTransactions({int limit = 50}) async {
    final data = await _api.get('/api/transactions',
        queryParams: {'limit': '$limit'});
    return (data['transactions'] as List)
        .map((j) => Transaction.fromJson(j))
        .toList();
  }

  Future<TransactionDetail> getTransactionDetail(int id) async {
    final data =
        await _api.get('/api/transactions/view', queryParams: {'id': '$id'});
    return TransactionDetail.fromJson(data);
  }

  Future<int> createTransaction(Map<String, dynamic> body) async {
    final data = await _api.post('/api/transactions/create', body);
    return int.parse(data['transaction_id'].toString());
  }
}
