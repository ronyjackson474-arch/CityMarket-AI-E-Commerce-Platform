import 'package:flutter/material.dart';

import '../../models/product.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/nav.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/shimmer.dart';
import '../../widgets/states.dart';

/// Full category browse screen. Lists top-level categories with their
/// children. [openAsPage] controls whether it renders with an AppBar
/// (when pushed from Home "See all") or as a bottom-nav tab (no AppBar).
class CategoriesScreen extends StatefulWidget {
  const CategoriesScreen({super.key, this.openAsPage = false});

  final bool openAsPage;

  @override
  State<CategoriesScreen> createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends State<CategoriesScreen> {
  late Future<List<Category>> _future;
  final _api = ApiService();

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<Category>> _load() async {
    final res = await _api.getCategories();
    final list = res['data'] as List? ?? [];
    return list
        .map((e) => Category.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<void> _refresh() async {
    setState(() => _future = _load());
    await _future;
  }

  @override
  Widget build(BuildContext context) {
    Widget body = RefreshIndicator(
      onRefresh: _refresh,
      child: FutureBuilder<List<Category>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return _Skeleton();
          }
          if (snap.hasError) {
            return ErrorState(
                message: snap.error.toString(), onRetry: _refresh);
          }
          final cats = snap.data ?? [];
          if (cats.isEmpty) {
            return const EmptyState(
              icon: Icons.category_outlined,
              title: 'No categories',
              subtitle: 'Check back soon.',
            );
          }
          return ListView.separated(
            padding: EdgeInsets.symmetric(
              horizontal: MediaQuery.of(context).size.width >= 1024
                  ? (MediaQuery.of(context).size.width - 800) / 2
                  : 16,
            ),
            itemCount: cats.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (context, i) {
              final c = cats[i];
              return _CategoryTile(
                category: c,
                onTap: () =>
                    Nav.toCategory(context, c.slug, name: c.name),
                onChild: (child) =>
                    Nav.toCategory(context, child.slug, name: child.name),
              );
            },
          );
        },
      ),
    );

    if (widget.openAsPage) {
      return Scaffold(
        appBar: AppBar(title: const Text('All Categories')),
        body: body,
      );
    }
    return Scaffold(
      appBar: AppBar(title: const Text('Categories')),
      body: body,
    );
  }
}

class _CategoryTile extends StatelessWidget {
  const _CategoryTile({
    required this.category,
    required this.onTap,
    required this.onChild,
  });

  final Category category;
  final VoidCallback onTap;
  final ValueChanged<Category> onChild;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: onTap,
              child: Row(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: SizedBox(
                      width: 56,
                      height: 56,
                      child: category.image != null &&
                              category.image!.isNotEmpty
                          ? AppNetworkImage(category.image, fit: BoxFit.cover)
                          : Container(
                              color: AppTheme.primaryLight,
                              child: const Icon(Icons.category_rounded,
                                  color: AppTheme.primary),
                            ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          category.name,
                          style: const TextStyle(
                              fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        if (category.description != null)
                          Text(
                            category.description!,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                                fontSize: 12,
                                color: AppTheme.textSecondary),
                          ),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right_rounded,
                      color: AppTheme.textHint),
                ],
              ),
            ),
            if (category.hasChildren) ...[
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: category.children
                    .map((c) => ActionChip(
                          label: Text(c.name),
                          onPressed: () => onChild(c),
                          backgroundColor: const Color(0xFFF1F3F6),
                        ))
                    .toList(),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _Skeleton extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade300,
      highlightColor: Colors.grey.shade100,
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: 6,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        physics: const NeverScrollableScrollPhysics(),
        itemBuilder: (_, __) => Container(
          height: 90,
          decoration: BoxDecoration(
            color: Colors.grey.shade300,
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
    );
  }
}
