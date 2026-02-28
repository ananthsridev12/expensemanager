import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/investment_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';
import '../../widgets/empty_state.dart';
import 'investment_form_screen.dart';

class InvestmentsScreen extends StatefulWidget {
  const InvestmentsScreen({super.key});

  @override
  State<InvestmentsScreen> createState() => _InvestmentsScreenState();
}

class _InvestmentsScreenState extends State<InvestmentsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<InvestmentProvider>().fetchAll());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Investments')),
      drawer: const AppDrawer(currentRoute: 'investments'),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const InvestmentFormScreen())),
        child: const Icon(Icons.add),
      ),
      body: Consumer<InvestmentProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.investments.isEmpty) {
            return const LoadingIndicator();
          }
          if (provider.error != null && provider.investments.isEmpty) {
            return ErrorBanner(
                message: provider.error!, onRetry: provider.fetchAll);
          }
          if (provider.investments.isEmpty) {
            return const EmptyState(
                icon: Icons.trending_up,
                message: 'No investments yet.');
          }
          return RefreshIndicator(
            onRefresh: provider.fetchAll,
            child: ListView.builder(
              itemCount: provider.investments.length,
              itemBuilder: (context, i) {
                final inv = provider.investments[i];
                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      child: Text(inv.instrumentType.substring(0, 2).toUpperCase(),
                          style: const TextStyle(fontSize: 12)),
                    ),
                    title: Text(inv.name),
                    subtitle: Text(inv.instrumentType),
                    trailing:
                        AmountDisplay(amount: inv.currentBookBalance),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
