import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/account_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';
import '../../widgets/empty_state.dart';
import 'account_form_screen.dart';

class AccountsScreen extends StatefulWidget {
  const AccountsScreen({super.key});

  @override
  State<AccountsScreen> createState() => _AccountsScreenState();
}

class _AccountsScreenState extends State<AccountsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() =>
        context.read<AccountProvider>().fetchAll());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Accounts')),
      drawer: const AppDrawer(currentRoute: 'accounts'),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const AccountFormScreen())),
        child: const Icon(Icons.add),
      ),
      body: Consumer<AccountProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.accounts.isEmpty) {
            return const LoadingIndicator();
          }
          if (provider.error != null && provider.accounts.isEmpty) {
            return ErrorBanner(
                message: provider.error!, onRetry: provider.fetchAll);
          }
          if (provider.accounts.isEmpty) {
            return const EmptyState(
                icon: Icons.account_balance,
                message: 'No accounts yet.\nTap + to create one.');
          }
          return RefreshIndicator(
            onRefresh: provider.fetchAll,
            child: _buildGroupedList(provider),
          );
        },
      ),
    );
  }

  Widget _buildGroupedList(AccountProvider provider) {
    final types = ['ASSET', 'LIABILITY', 'INCOME', 'EXPENSE', 'EQUITY'];
    return ListView(
      children: types.map((type) {
        final accounts = provider.byType(type);
        if (accounts.isEmpty) return const SizedBox.shrink();
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Text(type,
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      color: Theme.of(context).colorScheme.primary,
                      fontWeight: FontWeight.bold)),
            ),
            ...accounts.map((a) => ListTile(
                  title: Text(a.name),
                  subtitle: a.code != null ? Text(a.code!) : null,
                  trailing: AmountDisplay(
                      amount: a.displayBalance, colorize: true),
                  onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) => AccountFormScreen(account: a))),
                )),
            const Divider(),
          ],
        );
      }).toList(),
    );
  }
}
