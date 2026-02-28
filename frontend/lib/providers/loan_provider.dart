import 'package:flutter/material.dart';
import '../models/loan.dart';
import '../services/loan_service.dart';

class LoanProvider extends ChangeNotifier {
  final LoanService _service;

  List<Loan> _loans = [];
  bool _isLoading = false;
  String? _error;

  LoanProvider(this._service);

  List<Loan> get loans => _loans;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Loan? findById(int id) {
    try {
      return _loans.firstWhere((l) => l.id == id);
    } catch (_) {
      return null;
    }
  }

  Future<void> fetchAll() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _loans = await _service.getLoans();
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createLoan(Map<String, dynamic> data) async {
    try {
      await _service.createLoan(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}
