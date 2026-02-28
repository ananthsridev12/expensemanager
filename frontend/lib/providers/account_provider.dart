import 'package:flutter/material.dart';
import '../models/account.dart';
import '../models/account_type.dart';
import '../services/account_service.dart';

class AccountProvider extends ChangeNotifier {
  final AccountService _service;

  List<Account> _accounts = [];
  List<AccountType> _accountTypes = [];
  bool _isLoading = false;
  String? _error;

  AccountProvider(this._service);

  List<Account> get accounts => _accounts;
  List<AccountType> get accountTypes => _accountTypes;
  bool get isLoading => _isLoading;
  String? get error => _error;

  List<Account> byType(String typeCode) =>
      _accounts.where((a) => a.accountType == typeCode && a.isActive).toList();

  List<Account> get activeAccounts =>
      _accounts.where((a) => a.isActive).toList();

  Account? findById(int id) {
    try {
      return _accounts.firstWhere((a) => a.id == id);
    } catch (_) {
      return null;
    }
  }

  Future<void> fetchAll() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final results = await Future.wait([
        _service.getAccountTypes(),
        _service.getAccounts(),
      ]);
      _accountTypes = results[0] as List<AccountType>;
      _accounts = results[1] as List<Account>;
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createAccount(Map<String, dynamic> data) async {
    try {
      await _service.createAccount(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateAccount(Map<String, dynamic> data) async {
    try {
      await _service.updateAccount(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}
