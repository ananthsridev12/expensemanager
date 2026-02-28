import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/loan_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';
import '../../widgets/empty_state.dart';
import 'loan_form_screen.dart';

class LoansScreen extends StatefulWidget {
  const LoansScreen({super.key});

  @override
  State<LoansScreen> createState() => _LoansScreenState();
}

class _LoansScreenState extends State<LoansScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<LoanProvider>().fetchAll());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Loans')),
      drawer: const AppDrawer(currentRoute: 'loans'),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const LoanFormScreen())),
        child: const Icon(Icons.add),
      ),
      body: Consumer<LoanProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.loans.isEmpty) {
            return const LoadingIndicator();
          }
          if (provider.error != null && provider.loans.isEmpty) {
            return ErrorBanner(
                message: provider.error!, onRetry: provider.fetchAll);
          }
          if (provider.loans.isEmpty) {
            return const EmptyState(
                icon: Icons.real_estate_agent,
                message: 'No loans yet.');
          }
          return RefreshIndicator(
            onRefresh: provider.fetchAll,
            child: ListView.builder(
              itemCount: provider.loans.length,
              itemBuilder: (context, i) {
                final loan = provider.loans[i];
                return Card(
                  child: ListTile(
                    title: Text(loan.name),
                    subtitle: Text(
                        '${loan.lender ?? ""}  •  ${loan.interestRateAnnual}% p.a.'),
                    trailing: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        AmountDisplay(amount: loan.principalBalance.abs()),
                        Text('of ${formatINR(loan.sanctionAmount)}',
                            style: Theme.of(context).textTheme.bodySmall),
                      ],
                    ),
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
