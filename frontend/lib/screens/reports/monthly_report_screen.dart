import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:fl_chart/fl_chart.dart';
import '../../providers/report_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/amount_display.dart';
import '../../widgets/summary_card.dart';
import '../../widgets/empty_state.dart';

class MonthlyReportScreen extends StatefulWidget {
  const MonthlyReportScreen({super.key});

  @override
  State<MonthlyReportScreen> createState() => _MonthlyReportScreenState();
}

class _MonthlyReportScreenState extends State<MonthlyReportScreen> {
  late int _year;
  late int _month;

  static const _months = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
  ];

  static const _chartColors = [
    Colors.blue,
    Colors.red,
    Colors.green,
    Colors.orange,
    Colors.purple,
    Colors.teal,
    Colors.pink,
    Colors.amber,
    Colors.indigo,
    Colors.brown,
  ];

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _year = now.year;
    _month = now.month;
    _fetchReport();
  }

  void _fetchReport() {
    context.read<ReportProvider>().fetchReport(year: _year, month: _month);
  }

  void _changeMonth(int delta) {
    setState(() {
      _month += delta;
      if (_month > 12) {
        _month = 1;
        _year++;
      } else if (_month < 1) {
        _month = 12;
        _year--;
      }
    });
    _fetchReport();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Monthly Report')),
      drawer: const AppDrawer(currentRoute: 'reports'),
      body: Column(
        children: [
          // Month picker
          Card(
            margin: const EdgeInsets.all(16),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  IconButton(
                    icon: const Icon(Icons.chevron_left),
                    onPressed: () => _changeMonth(-1),
                  ),
                  Text('${_months[_month - 1]} $_year',
                      style: Theme.of(context).textTheme.titleMedium),
                  IconButton(
                    icon: const Icon(Icons.chevron_right),
                    onPressed: () => _changeMonth(1),
                  ),
                ],
              ),
            ),
          ),

          Expanded(
            child: Consumer<ReportProvider>(
              builder: (context, provider, _) {
                if (provider.isLoading) return const LoadingIndicator();
                if (provider.error != null) {
                  return ErrorBanner(
                      message: provider.error!, onRetry: _fetchReport);
                }
                final report = provider.report;
                if (report == null) {
                  return const EmptyState(
                      message: 'No data for this month.');
                }
                return ListView(
                  children: [
                    // Summary cards
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
                      color: report.netSavings >= 0
                          ? Colors.green
                          : Colors.red,
                    ),

                    // Pie chart
                    if (report.expenseByCategory.isNotEmpty) ...[
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                        child: Text('Expense Breakdown',
                            style:
                                Theme.of(context).textTheme.titleMedium),
                      ),
                      SizedBox(
                        height: 220,
                        child: PieChart(
                          PieChartData(
                            sectionsSpace: 2,
                            centerSpaceRadius: 40,
                            sections: report.expenseByCategory
                                .asMap()
                                .entries
                                .map((entry) {
                              final i = entry.key;
                              final cat = entry.value;
                              return PieChartSectionData(
                                value: cat.total,
                                color: _chartColors[
                                    i % _chartColors.length],
                                title: '',
                                radius: 60,
                              );
                            }).toList(),
                          ),
                        ),
                      ),

                      // Legend
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          children: report.expenseByCategory
                              .asMap()
                              .entries
                              .map((entry) {
                            final i = entry.key;
                            final cat = entry.value;
                            return Padding(
                              padding:
                                  const EdgeInsets.symmetric(vertical: 4),
                              child: Row(
                                children: [
                                  Container(
                                    width: 16,
                                    height: 16,
                                    decoration: BoxDecoration(
                                      color: _chartColors[
                                          i % _chartColors.length],
                                      borderRadius:
                                          BorderRadius.circular(4),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                      child:
                                          Text(cat.categoryName)),
                                  AmountDisplay(amount: cat.total),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ),
                    ],
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
