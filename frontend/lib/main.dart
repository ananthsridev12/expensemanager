import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'config/theme.dart';
import 'services/api_client.dart';
import 'services/account_service.dart';
import 'services/category_service.dart';
import 'services/transaction_service.dart';
import 'services/credit_card_service.dart';
import 'services/loan_service.dart';
import 'services/emi_service.dart';
import 'services/investment_service.dart';
import 'services/dashboard_service.dart';
import 'services/report_service.dart';

import 'providers/account_provider.dart';
import 'providers/category_provider.dart';
import 'providers/transaction_provider.dart';
import 'providers/credit_card_provider.dart';
import 'providers/loan_provider.dart';
import 'providers/emi_provider.dart';
import 'providers/investment_provider.dart';
import 'providers/dashboard_provider.dart';
import 'providers/report_provider.dart';

import 'screens/dashboard/dashboard_screen.dart';

void main() {
  runApp(const ExpenseManagerApp());
}

class ExpenseManagerApp extends StatelessWidget {
  const ExpenseManagerApp({super.key});

  @override
  Widget build(BuildContext context) {
    final api = ApiClient();

    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
            create: (_) => AccountProvider(AccountService(api))),
        ChangeNotifierProvider(
            create: (_) => CategoryProvider(CategoryService(api))),
        ChangeNotifierProvider(
            create: (_) => TransactionProvider(TransactionService(api))),
        ChangeNotifierProvider(
            create: (_) => CreditCardProvider(CreditCardService(api))),
        ChangeNotifierProvider(
            create: (_) => LoanProvider(LoanService(api))),
        ChangeNotifierProvider(
            create: (_) => EmiProvider(EmiService(api))),
        ChangeNotifierProvider(
            create: (_) => InvestmentProvider(InvestmentService(api))),
        ChangeNotifierProvider(
            create: (_) => DashboardProvider(DashboardService(api))),
        ChangeNotifierProvider(
            create: (_) => ReportProvider(ReportService(api))),
      ],
      child: MaterialApp(
        title: 'Expense Manager',
        theme: appTheme,
        home: const DashboardScreen(),
        debugShowCheckedModeBanner: false,
      ),
    );
  }
}
