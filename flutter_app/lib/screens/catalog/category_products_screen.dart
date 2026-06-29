import 'package:flutter/material.dart';

import '../../models/product.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/nav.dart';
import '../../widgets/product_card.dart';
import '../../widgets/shimmer.dart';
import '../../widgets/states.dart';

/// Product listing for a category (or any filter set). Supports search,
/// price range, on-sale toggle, rating filter, and sort.
class CategoryProductsScreen extends StatefulWidget {
  const CategoryProductsScreen({
    super.key,
    this.categorySlug,
    this.title = 'Products',
    this.searchQuery,
    this.brandId,
  });

  final String? categorySlug;
  final String title;
  final String? searchQuery;
  final int? brandId;

  @override
  State<CategoryProductsScreen> createState() =>
      _CategoryProductsScreenState();
}

class _CategoryProductsScreenState extends State<CategoryProductsScreen> {
  final _api = ApiService();
  final _scrollCtrl = ScrollController();

  List<Product> _products = [];
  bool _loading = false;
  bool _loadingMore = false;
  bool _hasMore = true;
  int _page = 1;
  String? _error;

  // Filters
  String _sort = 'newest';
  double? _priceMin;
  double? _priceMax;
  bool _onSale = false;
  double? _minRating;
  RangeValues _priceRange = const RangeValues(0, 200000);

  @override
  void initState() {
    super.initState();
    _load();
    _scrollCtrl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollCtrl.removeListener(_onScroll);
    _scrollCtrl.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >=
        _scrollCtrl.position.maxScrollExtent - 200) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
      _page = 1;
      _hasMore = true;
    });
    try {
      _products = await _fetchPage(1);
      _hasMore = _products.length >= 20;
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || !_hasMore || _loading) return;
    setState(() => _loadingMore = true);
    try {
      final next = _page + 1;
      final items = await _fetchPage(next);
      if (items.isEmpty) {
        _hasMore = false;
      } else {
        _page = next;
        _products = [..._products, ...items];
      }
    } catch (_) {
      _hasMore = false;
    } finally {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  Future<List<Product>> _fetchPage(int page) async {
    final res = await _api.getProducts(
      categorySlug: widget.categorySlug,
      brand: widget.brandId,
      search: widget.searchQuery,
      sort: _sort,
      priceMin: _priceMin,
      priceMax: _priceMax,
      minRating: _minRating,
      onSale: _onSale ? true : null,
      page: page,
      perPage: 20,
    );
    final data = res['data'] as List? ?? [];
    return data
        .map((e) => Product.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  void _applySort(String sort) {
    if (_sort == sort) return;
    _sort = sort;
    _load();
  }

  void _applyFilters() {
    _priceMin = _priceRange.start > 0 ? _priceRange.start : null;
    _priceMax = _priceRange.end < 200000 ? _priceRange.end : null;
    _load();
  }

  void _openFilterSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheet) {
          return Padding(
            padding: EdgeInsets.fromLTRB(
                20, 16, 20, MediaQuery.of(ctx).viewInsets.bottom + 24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE0E3E8),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                const Text('Filters',
                    style: TextStyle(
                        fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                const Text('Price Range',
                    style:
                        TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                RangeSlider(
                  values: _priceRange,
                  min: 0,
                  max: 200000,
                  divisions: 20,
                  labels: RangeLabels(
                    '₹${_priceRange.start.round()}',
                    '₹${_priceRange.end.round()}',
                  ),
                  activeColor: AppTheme.primary,
                  onChanged: (v) => setSheet(() => _priceRange = v),
                ),
                const SizedBox(height: 8),
                const Text('Minimum Rating',
                    style:
                        TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                Wrap(
                  spacing: 8,
                  children: [null, 3.0, 4.0].map((r) {
                    final selected = _minRating == r;
                    return ChoiceChip(
                      label: Text(r == null ? 'Any' : '${r}+ ⭐'),
                      selected: selected,
                      selectedColor: AppTheme.primaryLight,
                      onSelected: (_) => setSheet(() => _minRating = r),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 8),
                SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('On Sale only'),
                  value: _onSale,
                  activeColor: AppTheme.primary,
                  onChanged: (v) => setSheet(() => _onSale = v),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          setSheet(() {
                            _priceRange =
                                const RangeValues(0, 200000);
                            _onSale = false;
                            _minRating = null;
                          });
                        },
                        child: const Text('Reset'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () {
                          Navigator.pop(ctx);
                          _applyFilters();
                        },
                        child: const Text('Apply'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title),
        actions: [
          IconButton(
            icon: const Icon(Icons.tune_rounded),
            onPressed: _openFilterSheet,
            tooltip: 'Filters',
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.sort_rounded),
            onSelected: _applySort,
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'newest', child: Text('Newest')),
              PopupMenuItem(
                  value: 'price_asc', child: Text('Price: Low to High')),
              PopupMenuItem(
                  value: 'price_desc', child: Text('Price: High to Low')),
              PopupMenuItem(value: 'rating', child: Text('Top Rated')),
            ],
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) return const ProductGridSkeleton();
    if (_error != null) {
      return ErrorState(message: _error!, onRetry: _load);
    }
    if (_products.isEmpty) {
      return const EmptyState(
        icon: Icons.inventory_2_outlined,
        title: 'No products found',
        subtitle: 'Try adjusting filters or check back later.',
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        controller: _scrollCtrl,
        padding: const EdgeInsets.only(bottom: 24),
        children: [
          ProductGrid(
            products: _products,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16),
            onTap: (p) => Nav.toProduct(context, p.slug),
          ),
          if (_loadingMore)
            const Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (!_hasMore)
            const Padding(
              padding: EdgeInsets.all(16),
              child: Center(
                child: Text('You\'ve reached the end',
                    style: TextStyle(
                        color: AppTheme.textSecondary, fontSize: 13)),
              ),
            ),
        ],
      ),
    );
  }
}
