import '../models/monthly_report.dart';
import 'api_client.dart';

class ReportService {
  final ApiClient _api;
  ReportService(this._api);

  Future<MonthlyReport> getMonthlyReport({int? year, int? month}) async {
    final params = <String, String>{};
    if (year != null) params['year'] = '$year';
    if (month != null) params['month'] = '$month';
    final data = await _api.get('/api/reports/monthly', queryParams: params);
    return MonthlyReport.fromJson(data);
  }
}
