import '../models/category.dart';
import 'api_client.dart';

class CategoryService {
  final ApiClient _api;
  CategoryService(this._api);

  Future<List<Category>> getCategories() async {
    final data = await _api.get('/api/categories');
    return (data['categories'] as List)
        .map((j) => Category.fromJson(j))
        .toList();
  }

  Future<int> createCategory(Map<String, dynamic> body) async {
    final data = await _api.post('/api/categories/create', body);
    return int.parse(data['category_id'].toString());
  }

  Future<bool> updateCategory(Map<String, dynamic> body) async {
    final data = await _api.post('/api/categories/update', body);
    return data['updated'] == true;
  }
}
