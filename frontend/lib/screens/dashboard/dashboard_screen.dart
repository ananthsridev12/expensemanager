import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/dashboard_provider.dart';
import '../../providers/report_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';
import '../../widgets/summary_card.dart';
import '../transactions/transaction_form_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<DashboardProvider>().fetchSummary();
      final now = DateTime.now();
      context.read<ReportProvider>().fetchReport(
          year: now.year, month: now.month);
    });
  }

  Future<void> _refresh() async {
    final now = DateTime.now();
    await Future.wait([
      context.read<DashboardProvider>().fetchSummary(),
      context.read<ReportProvider>().fetchReport(
          year: now.year, month: now.month),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Dashboard')),
      drawer: const AppDrawer(currentRoute: 'dashboard'),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          await Navigator.push(context,
              MaterialPageRoute(builder: (_) => const TransactionFormScreen()));
          if (mounted) _refresh();
        },
        icon: const Icon(Icons.add),
        label: const Text('Transaction'),
      ),
      body: Consumer2<DashboardProvider, ReportProvider>(
        builder: (context, dashProvider, reportProvider, _) {
          if (dashProvider.isLoading && dashProvider.summary == null) {
            return const LoadingIndicator();
          }
          if (dashProvider.error != null && dashProvider.summary == null) {
            return ErrorBanner(
                message: dashProvider.error!, onRetry: _refresh);
          }
          final summary = dashProvider.summary;
          final report = reportProvider.report;
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView(
              padding: const EdgeInsets.only(bottom: 80),
              children: [
                // Net Worth
                if (summary != null) ...[
                  SummaryCard(
                    title: 'Net Worth',
                    value: formatINR(summary.netWorth),
                    icon: Icons.account_balance_wallet,
                    color: Colors.teal,
                  ),
                  Row(
                    children: [
                      Expanded(
                        child: SummaryCard(
                          title: 'Assets',
                          value: formatINR(summary.totalAssets),
                          icon: Icons.arrow_upward,
                          color: Colors.green,
                        ),
                      ),
                      Expanded(
                        child: SummaryCard(
                          title: 'Liabilities',
                          value: formatINR(summary.totalLiabilities.abs()),
                          icon: Icons.arrow_downward,
                          color: Colors.red,
                        ),
                      ),
                    ],
                  ),
                ],

                // This month
                if (report != null) ...[
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: Text('This Month',
                        style: Theme.of(context).textTheme.titleMedium),
                  ),
                  Row(
                    children: [
                      Expanded(
                        child: SummaryCard(
                          title: 'Income',
                          value: formatINR(report.totalIncome),
                          icon: Icons.trending_up,
                          color: Colors.green,
                        ),
                      ),
                      Expanded(
                        child: SummaryCard(
                          title: 'Expense',
                          value: formatINR(report.totalExpense),
                          icon: Icons.trending_down,
                          color: Colors.red,
                        ),
                      ),
                    ],
                  ),
                  SummaryCard(
                    title: 'Net Savings',
                    value: formatINR(report.netSavings),
                    icon: Icons.savings,
                    color: report.netSavings >= 0 ? Colors.green : Colors.red,
                  ),
                ],

                // Upcoming EMIs
                if (summary != null &&
                    summary.upcomingEmiDues.isNotEmpty) ...[
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: Text('Upcoming EMIs',
                        style: Theme.of(context).textTheme.titleMedium),
                  ),
                  ...summary.upcomingEmiDues.map((emi) => Card(
                        child: ListTile(
                          leading: const Icon(Icons.calendar_month),
                          title: Text('Due: ${emi.dueDate}'),
                          trailing: AmountDisplay(amount: emi.totalAmount),
                        ),
                      )),
                ],

                // Balance by type
                if (summary != null) ...[
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: Text('Balances by Type',
                        style: Theme.of(context).textTheme.titleMedium),
                  ),
                  ...summary.balancesByType.map((b) => Card(
                        child: ListTile(
                          title: Text(b.accountType),
                          trailing: AmountDisplay(
                              amount: b.total, colorize: true),
                        ),
                      )),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}
