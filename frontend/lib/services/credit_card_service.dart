import '../models/credit_card.dart';
import 'api_client.dart';

class CreditCardService {
  final ApiClient _api;
  CreditCardService(this._api);

  Future<List<CreditCard>> getCreditCards() async {
    final data = await _api.get('/api/credit-cards');
    return (data['credit_cards'] as List)
        .map((j) => CreditCard.fromJson(j))
        .toList();
  }

  Future<int> createCreditCard(Map<String, dynamic> body) async {
    final data = await _api.post('/api/credit-cards/create', body);
    return int.parse(data['credit_card_id'].toString());
  }
}
