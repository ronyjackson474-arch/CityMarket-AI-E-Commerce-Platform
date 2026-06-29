import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/product.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/product_card.dart';
import '../../widgets/shimmer.dart';
import '../../widgets/states.dart';
import '../catalog/categories_screen.dart';
import 'widgets/category_strip.dart';
import 'widgets/countdown_timer.dart';
import 'widgets/home_search_bar.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<HomeData> _future;
  final _api = ApiService();
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _future = _loadHome();
  }

  Future<HomeData> _loadHome() async {
    final categories = _safe(() => _api.getCategories());
    final flash = _safe(() => _api.getFlashDeals());
    final featured = _safe(() => _api.getFeaturedProducts());
    final recommendations = _safe(() => _api.getRecommendations());
    final results =
        await Future.wait([categories, flash, featured, recommendations]);
    return HomeData(
      categories: results[0],
      flashDeals: results[1],
      featured: results[2],
      recommendations: results[3],
    );
  }

  Future<Map<String, dynamic>> _safe(
      Future<Map<String, dynamic>> Function() fn) async {
    try {
      return await fn();
    } catch (_) {
      return const {'data': <dynamic>[]};
    }
  }

  Future<void> _refresh() async {
    setState(() => _future = _loadHome());
    await _future;
    if (mounted) {
      context.read<AppProvider>().refreshCartCount();
      context.read<AppProvider>().refreshWishlistCount();
    }
  }

  void _onSearch(String query) {
    if (query.trim().isEmpty) return;
    Navigator.push(
      context,
      MaterialPageRoute(
          builder: (_) => _SearchResultsScreen(query: query.trim())),
    );
  }

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppProvider>();
    final name = app.user?.name.split(' ').first ?? 'Shopper';
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _refresh,
          child: CustomScrollView(
            slivers: [
              // ── Premium dark header ────────────────────────────────────
              SliverToBoxAdapter(
                child: Container(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
                  decoration: const BoxDecoration(
                    gradient: AppTheme.headerGradient,
                    borderRadius: BorderRadius.vertical(
                        bottom: Radius.circular(24)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Hello, $name! 👋',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 22,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'What are you shopping today?',
                                  style: TextStyle(
                                    color: Colors.white.withValues(alpha: 0.85),
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            onPressed: () => Nav.toAiChat(context),
                            icon: const Icon(Icons.support_agent_rounded,
                                color: Colors.white),
                            tooltip: 'AI Assistant',
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      HomeSearchBar(
                        controller: _searchCtrl,
                        onSubmitted: _onSearch,
                        onTapAi: () => Nav.toAiSearch(context),
                      ),
                    ],
                  ),
                ),
              ),

              // ── Hero carousel ───────────────────────────────────────────
              SliverToBoxAdapter(
                child: _HeroCarousel(onTap: () => Nav.toShop(context)),
              ),

              // ── Trust badges ────────────────────────────────────────────
              SliverToBoxAdapter(child: _buildTrustBadges()),

              // ── AI promo banner ─────────────────────────────────────────
              SliverToBoxAdapter(
                child: _AiPromoBanner(onTap: () => Nav.toAiSearch(context)),
              ),

              // ── Body ────────────────────────────────────────────────────
              FutureBuilder<HomeData>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const SliverToBoxAdapter(
                        child: ProductGridSkeleton());
                  }
                  if (snap.hasError) {
                    return SliverFillRemaining(
                      child: ErrorState(
                        message: snap.error.toString(),
                        onRetry: _refresh,
                      ),
                    );
                  }

                  final hd = snap.data!;
                  final categories = (hd.categories['data'] as List? ?? [])
                      .map((e) =>
                          Category.fromJson(e as Map<String, dynamic>))
                      .toList()
                      .cast<Category>();
                  final flash = (hd.flashDeals['data'] as List?)
                          ?.map((e) =>
                              Product.fromJson(e as Map<String, dynamic>))
                          .toList() ??
                      <Product>[];
                  final featured = (hd.featured['data'] as List?)
                          ?.map((e) =>
                              Product.fromJson(e as Map<String, dynamic>))
                          .toList() ??
                      <Product>[];
                  final recommended = (hd.recommendations['data'] as List?)
                          ?.map((e) =>
                              Product.fromJson(e as Map<String, dynamic>))
                          .toList() ??
                      <Product>[];

                  return MultiSliverBody(
                    categories: categories,
                    flashDeals: flash.cast<Product>(),
                    featured: featured.cast<Product>(),
                    recommendations: recommended.cast<Product>(),
                    onProduct: (p) => Nav.toProduct(context, p.slug),
                    onCategory: (c) =>
                        Nav.toCategory(context, c.slug, name: c.name),
                    onSeeAllCategories: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (_) =>
                                const CategoriesScreen(openAsPage: true))),
                  );
                },
              ),

              const SliverToBoxAdapter(child: SizedBox(height: 24)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTrustBadges() {
    const badges = [
      (icon: Icons.local_shipping_rounded, label: 'Free Delivery', color: Color(0xFF16A34A)),
      (icon: Icons.verified_user_rounded, label: 'Secure Pay', color: Color(0xFF1565C0)),
      (icon: Icons.assignment_return_rounded, label: 'Easy Returns', color: Color(0xFFEF6C00)),
      (icon: Icons.headset_mic_rounded, label: '24/7 Support', color: Color(0xFF7B1FA2)),
    ];
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: badges
            .map((b) => _TrustBadge(
                  icon: b.icon,
                  label: b.label,
                  color: b.color,
                ))
            .toList(),
      ),
    );
  }
}

// ── Hero Carousel ────────────────────────────────────────────────────
/// Auto-rotating carousel with 3 slides, matching the website design.
class _HeroCarousel extends StatefulWidget {
  const _HeroCarousel({required this.onTap});
  final VoidCallback onTap;

  @override
  State<_HeroCarousel> createState() => _HeroCarouselState();
}

class _HeroCarouselState extends State<_HeroCarousel> {
  final _controller = PageController();
  int _current = 0;
  Timer? _timer;

  static const _slides = [
    (
      badge: 'MEGA SALE',
      title: 'Up to 70% OFF',
      subtitle: 'Electronics, Fashion, Home & more',
      emoji: '🛍️',
      gradient: AppTheme.heroGradient1,
    ),
    (
      badge: 'PREMIUM PICKS',
      title: 'Beauty & Fragrance',
      subtitle: 'Discover premium beauty brands',
      emoji: '💄',
      gradient: AppTheme.heroGradient2,
    ),
    (
      badge: 'NEW ARRIVALS',
      title: 'Fashion Forward',
      subtitle: 'Latest trends in apparel & accessories',
      emoji: '👗',
      gradient: AppTheme.heroGradient3,
    ),
  ];

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 5), (_) {
      if (!mounted) return;
      _current = (_current + 1) % _slides.length;
      _controller.animateToPage(
        _current,
        duration: const Duration(milliseconds: 600),
        curve: Curves.easeOutCubic,
      );
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 8, 16, 4),
      child: Column(
        children: [
          SizedBox(
            height: 180,
            child: PageView.builder(
              controller: _controller,
              itemCount: _slides.length,
              onPageChanged: (i) => setState(() => _current = i),
              itemBuilder: (context, i) {
                final s = _slides[i];
                return GestureDetector(
                  onTap: widget.onTap,
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 2),
                    decoration: BoxDecoration(
                      gradient: s.gradient,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Stack(
                      children: [
                        Positioned(
                          right: 8,
                          top: 10,
                          child: Text(
                            s.emoji,
                            style: const TextStyle(
                                fontSize: 80, color: Colors.white24),
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.25),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  s.badge,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 1,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 10),
                              Text(
                                s.title,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 26,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                s.subtitle,
                                style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.85),
                                  fontSize: 13,
                                ),
                              ),
                              const SizedBox(height: 14),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 16, vertical: 8),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Text(
                                  'Shop Now →',
                                  style: TextStyle(
                                    color: AppTheme.textPrimary,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 8),
          // Dots
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(_slides.length, (i) {
              final active = i == _current;
              return AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: active ? 24 : 8,
                height: 8,
                decoration: BoxDecoration(
                  color: active ? AppTheme.primary : AppTheme.divider,
                  borderRadius: BorderRadius.circular(4),
                ),
              );
            }),
          ),
        ],
      ),
    );
  }
}

// ── AI Promo Banner ──────────────────────────────────────────────────
class _AiPromoBanner extends StatelessWidget {
  const _AiPromoBanner({required this.onTap});
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 8, 16, 4),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
              colors: [Color(0xFF6D28D9), Color(0xFF9333EA), Color(0xFFDC2626)],
            ),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.auto_awesome_rounded,
                    color: Colors.white, size: 26),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'AI Shopping Assistant',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text(
                      '"best running shoes under 2000"',
                      style: TextStyle(color: Colors.white70, fontSize: 12),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right_rounded, color: Colors.white70),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Trust Badge ──────────────────────────────────────────────────────
class _TrustBadge extends StatelessWidget {
  const _TrustBadge({
    required this.icon,
    required this.label,
    required this.color,
  });
  final IconData icon;
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, size: 18, color: color),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: const TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w600,
            color: AppTheme.textSecondary,
          ),
        ),
      ],
    );
  }
}

// ── Multi Section Body ───────────────────────────────────────────────
class MultiSliverBody extends StatelessWidget {
  const MultiSliverBody({
    super.key,
    required this.categories,
    required this.flashDeals,
    required this.featured,
    required this.recommendations,
    required this.onProduct,
    required this.onCategory,
    required this.onSeeAllCategories,
  });

  final List<Category> categories;
  final List<Product> flashDeals;
  final List<Product> featured;
  final List<Product> recommendations;
  final ValueChanged<Product> onProduct;
  final ValueChanged<Category> onCategory;
  final VoidCallback onSeeAllCategories;

  @override
  Widget build(BuildContext context) {
    return SliverList(
      delegate: SliverChildListDelegate.fixed([
        const SectionHeader(title: 'Shop by Category'),
        CategoryStrip(categories: categories, onCategory: onCategory),
        const SizedBox(height: 8),
        if (flashDeals.isNotEmpty) ...[
          const SectionHeader(
              title: '⚡ Flash Deals', subtitle: 'Hurry, limited time!'),
          SizedBox(height: 244, child: _flashDealRow(flashDeals)),
          const SizedBox(height: 8),
        ],
        if (featured.isNotEmpty) ...[
          const SectionHeader(title: '✨ Featured Products'),
          _HorizontalProductScroll(products: featured, onProduct: onProduct),
          const SizedBox(height: 8),
        ],
        if (recommendations.isNotEmpty) ...[
          const SectionHeader(
              title: '🎯 Recommended for You',
              subtitle: 'Picked by AI based on your activity'),
          _HorizontalProductScroll(
              products: recommendations, onProduct: onProduct),
          const SizedBox(height: 8),
        ],
        const SizedBox(height: 16),
      ]),
    );
  }

  Widget _flashDealRow(List<Product> deals) {
    return ListView.separated(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: deals.length,
      separatorBuilder: (_, __) => const SizedBox(width: 12),
      itemBuilder: (context, i) {
        final p = deals[i];
        return SizedBox(
          width: 152,
          child: _FlashDealCard(product: p, onTap: () => onProduct(p)),
        );
      },
    );
  }
}

class _FlashDealCard extends StatelessWidget {
  const _FlashDealCard({required this.product, required this.onTap});
  final Product product;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Stack(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: AspectRatio(
                      aspectRatio: 1,
                      child: AppNetworkImage(product.primaryImage),
                    ),
                  ),
                  Positioned(
                    top: 4,
                    left: 4,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        gradient: AppTheme.dealGradient,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        '-${product.discountPercent}%',
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                product.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                    fontSize: 12, fontWeight: FontWeight.w600),
              ),
              Row(
                children: [
                  Text(
                    Format.currency(product.effectivePrice),
                    style: const TextStyle(
                        color: AppTheme.primary,
                        fontSize: 13,
                        fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(width: 4),
                  if (product.isOnSale)
                    Text(
                      Format.currency(product.price),
                      style: const TextStyle(
                          fontSize: 10,
                          color: AppTheme.textHint,
                          decoration: TextDecoration.lineThrough),
                    ),
                ],
              ),
              const Spacer(),
              if (product.flashDealEndsAt != null)
                CountdownTimer(endsAt: product.flashDealEndsAt!),
            ],
          ),
        ),
      ),
    );
  }
}

class _HorizontalProductScroll extends StatelessWidget {
  const _HorizontalProductScroll(
      {required this.products, required this.onProduct});
  final List<Product> products;
  final ValueChanged<Product> onProduct;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 262,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: products.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (context, i) {
          final p = products[i];
          return SizedBox(
            width: 158,
            child: ProductCard(product: p, onTap: () => onProduct(p)),
          );
        },
      ),
    );
  }
}

/// Reusable section header.
class SectionHeader extends StatelessWidget {
  const SectionHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.onSeeAll,
  });
  final String title;
  final String? subtitle;
  final VoidCallback? onSeeAll;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(
                        fontSize: 17, fontWeight: FontWeight.bold)),
                if (subtitle != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 2),
                    child: Text(subtitle!,
                        style: const TextStyle(
                            fontSize: 12, color: AppTheme.textSecondary)),
                  ),
              ],
            ),
          ),
          if (onSeeAll != null)
            TextButton(
              onPressed: onSeeAll,
              style: TextButton.styleFrom(
                foregroundColor: AppTheme.primary,
                padding: const EdgeInsets.symmetric(horizontal: 8),
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('See All',
                      style:
                          TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                  SizedBox(width: 2),
                  Icon(Icons.chevron_right_rounded, size: 18),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

// ── Home data bundle ─────────────────────────────────────────────────
class HomeData {
  const HomeData({
    required this.categories,
    required this.flashDeals,
    required this.featured,
    required this.recommendations,
  });

  final Map<String, dynamic> categories;
  final Map<String, dynamic> flashDeals;
  final Map<String, dynamic> featured;
  final Map<String, dynamic> recommendations;
}

// ── Search results ───────────────────────────────────────────────────
class _SearchResultsScreen extends StatefulWidget {
  const _SearchResultsScreen({required this.query});
  final String query;

  @override
  State<_SearchResultsScreen> createState() => _SearchResultsScreenState();
}

class _SearchResultsScreenState extends State<_SearchResultsScreen> {
  late Future<List<Product>> _future;

  @override
  void initState() {
    super.initState();
    _future = _search();
  }

  Future<List<Product>> _search() async {
    final res =
        await ApiService().getProducts(search: widget.query, perPage: 40);
    final data = res['data'] as List? ?? [];
    return data
        .map((e) => Product.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('"${widget.query}"')),
      body: FutureBuilder<List<Product>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const ProductGridSkeleton();
          }
          if (snap.hasError) {
            return ErrorState(
                message: snap.error.toString(),
                onRetry: () => setState(() => _future = _search()));
          }
          final items = snap.data ?? [];
          if (items.isEmpty) {
            return const EmptyState(
              icon: Icons.search_off_rounded,
              title: 'No products found',
              subtitle: 'Try a different search term.',
            );
          }
          return ProductGrid(
            products: items,
            onTap: (p) => Nav.toProduct(context, p.slug),
          );
        },
      ),
    );
  }
}
