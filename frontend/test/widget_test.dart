import 'package:flutter_test/flutter_test.dart';
import 'package:expense_manager/main.dart';

void main() {
  testWidgets('App launches', (WidgetTester tester) async {
    await tester.pumpWidget(const ExpenseManagerApp());
    expect(find.text('Dashboard'), findsOneWidget);
  });
}
