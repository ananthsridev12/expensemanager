import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/category_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/loading_indicator.dart';
import '../../widgets/error_banner.dart';
import '../../widgets/empty_state.dart';
import 'category_form_screen.dart';

class CategoriesScreen extends StatefulWidget {
  const CategoriesScreen({super.key});

  @override
  State<CategoriesScreen> createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends State<CategoriesScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() =>
        context.read<CategoryProvider>().fetchAll());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Categories')),
      drawer: const AppDrawer(currentRoute: 'categories'),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const CategoryFormScreen())),
        child: const Icon(Icons.add),
      ),
      body: Consumer<CategoryProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.categories.isEmpty) {
            return const LoadingIndicator();
          }
          if (provider.error != null && provider.categories.isEmpty) {
            return ErrorBanner(
                message: provider.error!, onRetry: provider.fetchAll);
          }
          if (provider.categories.isEmpty) {
            return const EmptyState(
                icon: Icons.category,
                message: 'No categories yet.\nTap + to create one.');
          }
          return RefreshIndicator(
            onRefresh: provider.fetchAll,
            child: _buildGroupedList(provider),
          );
        },
      ),
    );
  }

  Widget _buildGroupedList(CategoryProvider provider) {
    final kinds = ['INCOME', 'EXPENSE', 'TRANSFER', 'INVESTMENT', 'LOAN'];
    return ListView(
      children: kinds.map((kind) {
        final cats = provider.byKind(kind);
        if (cats.isEmpty) return const SizedBox.shrink();
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Text(kind,
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      color: Theme.of(context).colorScheme.primary,
                      fontWeight: FontWeight.bold)),
            ),
            ...cats.map((c) => ListTile(
                  title: Text(c.name),
                  trailing: c.isActive
                      ? null
                      : const Chip(label: Text('Inactive')),
                  onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) =>
                              CategoryFormScreen(category: c))),
                )),
            const Divider(),
          ],
        );
      }).toList(),
    );
  }
}
