import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/transaction_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/empty_state.dart';
import 'transaction_detail_screen.dart';
import 'transaction_form_screen.dart';

class TransactionsScreen extends StatefulWidget {
  const TransactionsScreen({super.key});

  @override
  State<TransactionsScreen> createState() => _TransactionsScreenState();
}

class _TransactionsScreenState extends State<TransactionsScreen> {
  String? _typeFilter;

  @override
  void initState() {
    super.initState();
    Future.microtask(() =>
        context.read<TransactionProvider>().fetchTransactions(limit: 100));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Transactions')),
      drawer: const AppDrawer(currentRoute: 'transactions'),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await Navigator.push(context,
              MaterialPageRoute(builder: (_) => const TransactionFormScreen()));
          if (mounted) {
            context.read<TransactionProvider>().fetchTransactions(limit: 100);
          }
        },
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          _buildFilterChips(),
          Expanded(
            child: Consumer<TransactionProvider>(
              builder: (context, provider, _) {
                if (provider.isLoading && provider.transactions.isEmpty) {
                  return const LoadingIndicator();
                }
                if (provider.error != null && provider.transactions.isEmpty) {
                  return ErrorBanner(
                      message: provider.error!,
                      onRetry: () => provider.fetchTransactions(limit: 100));
                }
                final filtered = _typeFilter == null
                    ? provider.transactions
                    : provider.transactions
                        .where((t) => t.txnType == _typeFilter)
                        .toList();
                if (filtered.isEmpty) {
                  return const EmptyState(
                      icon: Icons.receipt_long,
                      message: 'No transactions found.');
                }
                return RefreshIndicator(
                  onRefresh: () => provider.fetchTransactions(limit: 100),
                  child: ListView.builder(
                    itemCount: filtered.length,
                    itemBuilder: (context, i) {
                      final txn = filtered[i];
                      return ListTile(
                        title: Text(txn.description ?? txn.typeLabel),
                        subtitle: Text('${txn.txnDate}  •  ${txn.typeLabel}'),
                        trailing: Chip(
                          label: Text(txn.status,
                              style: const TextStyle(fontSize: 11)),
                          backgroundColor:
                              txn.status == 'POSTED'
                                  ? Colors.green.shade50
                                  : Colors.orange.shade50,
                        ),
                        onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) =>
                                    TransactionDetailScreen(id: txn.id))),
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
    const types = [
      'income',
      'expense_cash_or_bank',
      'transfer',
      'credit_card_purchase',
      'credit_card_payment',
      'loan_emi_payment',
      'card_emi_payment',
      'investment_buy',
    ];
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        children: [
          FilterChip(
            label: const Text('All'),
            selected: _typeFilter == null,
            onSelected: (_) => setState(() => _typeFilter = null),
          ),
          const SizedBox(width: 8),
          ...types.map((t) => Padding(
                padding: const EdgeInsets.only(right: 8),
                child: FilterChip(
                  label: Text(t.replaceAll('_', ' '),
                      style: const TextStyle(fontSize: 11)),
                  selected: _typeFilter == t,
                  onSelected: (_) =>
                      setState(() => _typeFilter = _typeFilter == t ? null : t),
                ),
              )),
        ],
      ),
    );
  }
}
