import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/credit_card_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';
import '../../widgets/empty_state.dart';
import 'credit_card_form_screen.dart';

class CreditCardsScreen extends StatefulWidget {
  const CreditCardsScreen({super.key});

  @override
  State<CreditCardsScreen> createState() => _CreditCardsScreenState();
}

class _CreditCardsScreenState extends State<CreditCardsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<CreditCardProvider>().fetchAll());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Credit Cards')),
      drawer: const AppDrawer(currentRoute: 'credit_cards'),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const CreditCardFormScreen())),
        child: const Icon(Icons.add),
      ),
      body: Consumer<CreditCardProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.cards.isEmpty) {
            return const LoadingIndicator();
          }
          if (provider.error != null && provider.cards.isEmpty) {
            return ErrorBanner(
                message: provider.error!, onRetry: provider.fetchAll);
          }
          if (provider.cards.isEmpty) {
            return const EmptyState(
                icon: Icons.credit_card,
                message: 'No credit cards yet.');
          }
          return RefreshIndicator(
            onRefresh: provider.fetchAll,
            child: ListView.builder(
              itemCount: provider.cards.length,
              itemBuilder: (context, i) {
                final card = provider.cards[i];
                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(card.name,
                                style: Theme.of(context).textTheme.titleMedium),
                            AmountDisplay(amount: card.principalBalance.abs()),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Limit: ${formatINR(card.limitAmount)}',
                                style: Theme.of(context).textTheme.bodySmall),
                            Text(
                                'Available: ${formatINR(card.availableLimit)}',
                                style: Theme.of(context).textTheme.bodySmall),
                          ],
                        ),
                        const SizedBox(height: 8),
                        LinearProgressIndicator(
                          value: card.utilization.clamp(0, 1),
                          backgroundColor: Colors.grey.shade200,
                          color: card.utilization > 0.8
                              ? Colors.red
                              : card.utilization > 0.5
                                  ? Colors.orange
                                  : Colors.green,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${(card.utilization * 100).toStringAsFixed(1)}% utilized  •  Bill: ${card.billingDay}  •  Due: ${card.dueDay}',
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
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
