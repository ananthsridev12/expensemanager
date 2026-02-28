import '../models/account_type.dart';
import '../models/account.dart';
import 'api_client.dart';

class AccountService {
  final ApiClient _api;
  AccountService(this._api);

  Future<List<AccountType>> getAccountTypes() async {
    final data = await _api.getNoUser('/api/account-types');
    return (data['account_types'] as List)
        .map((j) => AccountType.fromJson(j))
        .toList();
  }

  Future<List<Account>> getAccounts() async {
    final data = await _api.get('/api/accounts', queryParams: {'limit': '200'});
    return (data['accounts'] as List)
        .map((j) => Account.fromJson(j))
        .toList();
  }

  Future<int> createAccount(Map<String, dynamic> body) async {
    final data = await _api.post('/api/accounts/create', body);
    return int.parse(data['account_id'].toString());
  }

  Future<bool> updateAccount(Map<String, dynamic> body) async {
    final data = await _api.post('/api/accounts/update', body);
    return data['updated'] == true;
  }
}
