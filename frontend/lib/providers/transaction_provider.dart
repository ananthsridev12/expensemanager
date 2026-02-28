import 'package:flutter/material.dart';
import '../models/transaction.dart';
import '../models/transaction_detail.dart';
import '../services/transaction_service.dart';

class TransactionProvider extends ChangeNotifier {
  final TransactionService _service;

  List<Transaction> _transactions = [];
  TransactionDetail? _detail;
  bool _isLoading = false;
  String? _error;

  TransactionProvider(this._service);

  List<Transaction> get transactions => _transactions;
  TransactionDetail? get detail => _detail;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> fetchTransactions({int limit = 50}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _transactions = await _service.getTransactions(limit: limit);
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchDetail(int id) async {
    _isLoading = true;
    _error = null;
    _detail = null;
    notifyListeners();
    try {
      _detail = await _service.getTransactionDetail(id);
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<int?> createTransaction(Map<String, dynamic> data) async {
    try {
      final id = await _service.createTransaction(data);
      await fetchTransactions();
      return id;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return null;
    }
  }
}
