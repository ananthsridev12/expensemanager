class AccountType {
  final int id;
  final String code;
  final String normalSide;

  AccountType({required this.id, required this.code, required this.normalSide});

  factory AccountType.fromJson(Map<String, dynamic> json) {
    return AccountType(
      id: int.parse(json['id'].toString()),
      code: json['code'] as String,
      normalSide: json['normal_side'] as String,
    );
  }
}
