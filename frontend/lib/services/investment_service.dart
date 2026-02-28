import '../models/investment.dart';
import 'api_client.dart';

class InvestmentService {
  final ApiClient _api;
  InvestmentService(this._api);

  Future<List<Investment>> getInvestments() async {
    final data = await _api.get('/api/investments');
    return (data['investments'] as List)
        .map((j) => Investment.fromJson(j))
        .toList();
  }

  Future<int> createInvestment(Map<String, dynamic> body) async {
    final data = await _api.post('/api/investments/create', body);
    return int.parse(data['investment_id'].toString());
  }
}
