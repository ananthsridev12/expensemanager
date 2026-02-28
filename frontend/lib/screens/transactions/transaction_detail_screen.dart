import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/transaction_provider.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';

class TransactionDetailScreen extends StatefulWidget {
  final int id;

  const TransactionDetailScreen({super.key, required this.id});

  @override
  State<TransactionDetailScreen> createState() =>
      _TransactionDetailScreenState();
}

class _TransactionDetailScreenState extends State<TransactionDetailScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() =>
        context.read<TransactionProvider>().fetchDetail(widget.id));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Transaction Detail')),
      body: Consumer<TransactionProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading) return const LoadingIndicator();
          if (provider.error != null) {
            return ErrorBanner(
                message: provider.error!,
                onRetry: () => provider.fetchDetail(widget.id));
          }
          final detail = provider.detail;
          if (detail == null) {
            return const Center(child: Text('Transaction not found'));
          }
          final txn = detail.transaction;
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Chip(label: Text(txn.typeLabel)),
                          Chip(
                            label: Text(txn.status),
                            backgroundColor: Colors.green.shade50,
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text('Date: ${txn.txnDate}'),
                      if (txn.description != null) ...[
                        const SizedBox(height: 8),
                        Text(txn.description!,
                            style: Theme.of(context).textTheme.bodyLarge),
                      ],
                      if (txn.externalRef != null) ...[
                        const SizedBox(height: 8),
                        Text('Ref: ${txn.externalRef}',
                            style: TextStyle(color: Colors.grey.shade600)),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text('Journal Entries',
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              ...detail.entries.map((e) => Card(
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: e.isDebit
                            ? Colors.red.shade50
                            : Colors.green.shade50,
                        child: Text(e.isDebit ? 'DR' : 'CR',
                            style: TextStyle(
                                color: e.isDebit
                                    ? Colors.red.shade700
                                    : Colors.green.shade700,
                                fontWeight: FontWeight.bold,
                                fontSize: 12)),
                      ),
                      title: Text(e.accountName),
                      subtitle: e.categoryName != null
                          ? Text(e.categoryName!)
                          : null,
                      trailing: AmountDisplay(amount: e.amount),
                    ),
                  )),
            ],
          );
        },
      ),
    );
  }
}
