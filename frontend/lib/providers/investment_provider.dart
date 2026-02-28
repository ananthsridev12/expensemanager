import 'package:flutter/material.dart';
import '../models/investment.dart';
import '../services/investment_service.dart';

class InvestmentProvider extends ChangeNotifier {
  final InvestmentService _service;

  List<Investment> _investments = [];
  bool _isLoading = false;
  String? _error;

  InvestmentProvider(this._service);

  List<Investment> get investments => _investments;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Investment? findById(int id) {
    try {
      return _investments.firstWhere((i) => i.id == id);
    } catch (_) {
      return null;
    }
  }

  Future<void> fetchAll() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _investments = await _service.getInvestments();
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createInvestment(Map<String, dynamic> data) async {
    try {
      await _service.createInvestment(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}
