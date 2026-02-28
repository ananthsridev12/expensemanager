import '../models/emi.dart';
import 'api_client.dart';

class EmiService {
  final ApiClient _api;
  EmiService(this._api);

  Future<List<Emi>> getEmis({String? status}) async {
    final params = <String, String>{};
    if (status != null) params['status'] = status;
    final data = await _api.get('/api/emis', queryParams: params);
    return (data['emis'] as List).map((j) => Emi.fromJson(j)).toList();
  }

  Future<int> createEmi(Map<String, dynamic> body) async {
    final data = await _api.post('/api/emis/create', body);
    return int.parse(data['emi_id'].toString());
  }

  Future<bool> markPaid(int emiId, int paymentTransactionId) async {
    final data = await _api.post('/api/emis/mark-paid', {
      'emi_id': emiId,
      'payment_transaction_id': paymentTransactionId,
    });
    return data['updated'] == true;
  }
}
