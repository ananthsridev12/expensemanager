class Category {
  final int id;
  final String kind;
  final String name;
  final int? parentCategoryId;
  final bool isActive;

  Category({
    required this.id,
    required this.kind,
    required this.name,
    this.parentCategoryId,
    required this.isActive,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: int.parse(json['id'].toString()),
      kind: json['kind'] as String,
      name: json['name'] as String,
      parentCategoryId: json['parent_category_id'] != null
          ? int.parse(json['parent_category_id'].toString())
          : null,
      isActive: json['is_active'].toString() == '1',
    );
  }
}
