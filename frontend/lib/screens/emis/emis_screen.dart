import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/emi_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';
import '../../widgets/empty_state.dart';
import 'emi_form_screen.dart';

class EmisScreen extends StatefulWidget {
  const EmisScreen({super.key});

  @override
  State<EmisScreen> createState() => _EmisScreenState();
}

class _EmisScreenState extends State<EmisScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<EmiProvider>().fetchAll());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('EMIs')),
      drawer: const AppDrawer(currentRoute: 'emis'),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(
            context, MaterialPageRoute(builder: (_) => const EmiFormScreen())),
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          _buildFilterChips(),
          Expanded(
            child: Consumer<EmiProvider>(
              builder: (context, provider, _) {
                if (provider.isLoading && provider.emis.isEmpty) {
                  return const LoadingIndicator();
                }
                if (provider.error != null && provider.emis.isEmpty) {
                  return ErrorBanner(
                      message: provider.error!, onRetry: provider.fetchAll);
                }
                if (provider.emis.isEmpty) {
                  return const EmptyState(
                      icon: Icons.calendar_month,
                      message: 'No EMIs found.');
                }
                return RefreshIndicator(
                  onRefresh: provider.fetchAll,
                  child: ListView.builder(
                    itemCount: provider.emis.length,
                    itemBuilder: (context, i) {
                      final emi = provider.emis[i];
                      return Card(
                        child: ListTile(
                          title: Text(emi.linkedName),
                          subtitle: Text(
                              'Due: ${emi.dueDate}  •  P: ${formatINR(emi.principalAmount)} + I: ${formatINR(emi.interestAmount)}'),
                          trailing: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              AmountDisplay(amount: emi.totalAmount),
                              const SizedBox(height: 4),
                              Chip(
                                label: Text(
                                    emi.isOverdue ? 'OVERDUE' : emi.status,
                                    style: const TextStyle(fontSize: 10)),
                                backgroundColor: emi.isOverdue
                                    ? Colors.red.shade100
                                    : emi.isPending
                                        ? Colors.orange.shade100
                                        : Colors.green.shade100,
                                padding: EdgeInsets.zero,
                                visualDensity: VisualDensity.compact,
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
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChips() {
    final provider = context.watch<EmiProvider>();
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        children: [
          FilterChip(
            label: const Text('All'),
            selected: provider.statusFilter == null,
            onSelected: (_) => provider.setFilter(null),
          ),
          const SizedBox(width: 8),
          FilterChip(
            label: const Text('Pending'),
            selected: provider.statusFilter == 'PENDING',
            onSelected: (_) => provider.setFilter(
                provider.statusFilter == 'PENDING' ? null : 'PENDING'),
          ),
          const SizedBox(width: 8),
          FilterChip(
            label: const Text('Paid'),
            selected: provider.statusFilter == 'PAID',
            onSelected: (_) => provider
                .setFilter(provider.statusFilter == 'PAID' ? null : 'PAID'),
          ),
        ],
      ),
    );
  }
}
