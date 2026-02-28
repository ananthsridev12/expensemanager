import 'package:flutter/material.dart';
import '../screens/dashboard/dashboard_screen.dart';
import '../screens/accounts/accounts_screen.dart';
import '../screens/categories/categories_screen.dart';
import '../screens/transactions/transactions_screen.dart';
import '../screens/credit_cards/credit_cards_screen.dart';
import '../screens/loans/loans_screen.dart';
import '../screens/emis/emis_screen.dart';
import '../screens/investments/investments_screen.dart';
import '../screens/reports/monthly_report_screen.dart';

class AppDrawer extends StatelessWidget {
  final String currentRoute;

  const AppDrawer({super.key, required this.currentRoute});

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          DrawerHeader(
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.primaryContainer,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Icon(Icons.account_balance_wallet,
                    size: 40,
                    color: Theme.of(context).colorScheme.onPrimaryContainer),
                const SizedBox(height: 8),
                Text('Expense Manager',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        color:
                            Theme.of(context).colorScheme.onPrimaryContainer)),
              ],
            ),
          ),
          _tile(context, 'Dashboard', Icons.dashboard, 'dashboard',
              const DashboardScreen()),
          _tile(context, 'Accounts', Icons.account_balance, 'accounts',
              const AccountsScreen()),
          _tile(context, 'Categories', Icons.category, 'categories',
              const CategoriesScreen()),
          _tile(context, 'Transactions', Icons.receipt_long, 'transactions',
              const TransactionsScreen()),
          const Divider(),
          _tile(context, 'Credit Cards', Icons.credit_card, 'credit_cards',
              const CreditCardsScreen()),
          _tile(context, 'Loans', Icons.real_estate_agent, 'loans',
              const LoansScreen()),
          _tile(
              context, 'EMIs', Icons.calendar_month, 'emis',
              const EmisScreen()),
          _tile(context, 'Investments', Icons.trending_up, 'investments',
              const InvestmentsScreen()),
          const Divider(),
          _tile(context, 'Monthly Report', Icons.bar_chart, 'reports',
              const MonthlyReportScreen()),
        ],
      ),
    );
  }

  Widget _tile(BuildContext context, String title, IconData icon, String route,
      Widget screen) {
    final selected = currentRoute == route;
    return ListTile(
      leading: Icon(icon),
      title: Text(title),
      selected: selected,
      onTap: () {
        Navigator.of(context).pop();
        if (!selected) {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => screen),
          );
        }
      },
    );
  }
}
