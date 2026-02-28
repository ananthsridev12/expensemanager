import 'package:flutter/material.dart';
import '../models/monthly_report.dart';
import '../services/report_service.dart';

class ReportProvider extends ChangeNotifier {
  final ReportService _service;

  MonthlyReport? _report;
  bool _isLoading = false;
  String? _error;

  ReportProvider(this._service);

  MonthlyReport? get report => _report;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> fetchReport({int? year, int? month}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _report = await _service.getMonthlyReport(year: year, month: month);
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }
}
