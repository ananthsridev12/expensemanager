import '../models/loan.dart';
import 'api_client.dart';

class LoanService {
  final ApiClient _api;
  LoanService(this._api);

  Future<List<Loan>> getLoans() async {
    final data = await _api.get('/api/loans');
    return (data['loans'] as List).map((j) => Loan.fromJson(j)).toList();
  }

  Future<int> createLoan(Map<String, dynamic> body) async {
    final data = await _api.post('/api/loans/create', body);
    return int.parse(data['loan_id'].toString());
  }
}
