import 'package:flutter/material.dart';
import '../models/emi.dart';
import '../services/emi_service.dart';

class EmiProvider extends ChangeNotifier {
  final EmiService _service;

  List<Emi> _emis = [];
  bool _isLoading = false;
  String? _error;
  String? _statusFilter;

  EmiProvider(this._service);

  List<Emi> get emis => _emis;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String? get statusFilter => _statusFilter;

  void setFilter(String? status) {
    _statusFilter = status;
    fetchAll();
  }

  Future<void> fetchAll() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _emis = await _service.getEmis(status: _statusFilter);
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createEmi(Map<String, dynamic> data) async {
    try {
      await _service.createEmi(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> markPaid(int emiId, int paymentTransactionId) async {
    try {
      await _service.markPaid(emiId, paymentTransactionId);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}
