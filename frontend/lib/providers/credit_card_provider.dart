import 'package:flutter/material.dart';
import '../models/credit_card.dart';
import '../services/credit_card_service.dart';

class CreditCardProvider extends ChangeNotifier {
  final CreditCardService _service;

  List<CreditCard> _cards = [];
  bool _isLoading = false;
  String? _error;

  CreditCardProvider(this._service);

  List<CreditCard> get cards => _cards;
  bool get isLoading => _isLoading;
  String? get error => _error;

  CreditCard? findById(int id) {
    try {
      return _cards.firstWhere((c) => c.id == id);
    } catch (_) {
      return null;
    }
  }

  Future<void> fetchAll() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _cards = await _service.getCreditCards();
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createCreditCard(Map<String, dynamic> data) async {
    try {
      await _service.createCreditCard(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}
