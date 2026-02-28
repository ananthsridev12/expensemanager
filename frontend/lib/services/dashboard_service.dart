import '../models/dashboard_summary.dart';
import 'api_client.dart';

class DashboardService {
  final ApiClient _api;
  DashboardService(this._api);

  Future<DashboardSummary> getSummary() async {
    final data = await _api.get('/api/dashboard/summary');
    return DashboardSummary.fromJson(data);
  }
}
