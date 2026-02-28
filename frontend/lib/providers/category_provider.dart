import 'package:flutter/material.dart';
import '../models/category.dart';
import '../services/category_service.dart';

class CategoryProvider extends ChangeNotifier {
  final CategoryService _service;

  List<Category> _categories = [];
  bool _isLoading = false;
  String? _error;

  CategoryProvider(this._service);

  List<Category> get categories => _categories;
  bool get isLoading => _isLoading;
  String? get error => _error;

  List<Category> byKind(String kind) =>
      _categories.where((c) => c.kind == kind && c.isActive).toList();

  List<Category> get activeCategories =>
      _categories.where((c) => c.isActive).toList();

  Future<void> fetchAll() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _categories = await _service.getCategories();
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createCategory(Map<String, dynamic> data) async {
    try {
      await _service.createCategory(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateCategory(Map<String, dynamic> data) async {
    try {
      await _service.updateCategory(data);
      await fetchAll();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}
