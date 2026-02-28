import 'package:flutter/material.dart';
import '../models/dashboard_summary.dart';
import '../services/dashboard_service.dart';

class DashboardProvider extends ChangeNotifier {
  final DashboardService _service;

  DashboardSummary? _summary;
  bool _isLoading = false;
  String? _error;

  DashboardProvider(this._service);

  DashboardSummary? get summary => _summary;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> fetchSummary() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _summary = await _service.getSummary();
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }
}
