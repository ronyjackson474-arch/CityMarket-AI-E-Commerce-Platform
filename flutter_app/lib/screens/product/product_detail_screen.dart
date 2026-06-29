import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';

import '../../models/order.dart';
import '../../models/product.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/auth_guard.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/buttons.dart';
import '../../widgets/rating_stars.dart';
import '../../widgets/states.dart';

class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  final _api = ApiService();
  final _pageCtrl = PageController();

  Product? _product;
  List<Review> _reviews = [];
  bool _loading = true;
  bool _adding = false;
  bool _wishlisted = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await _api.getProduct(widget.slug);
      final data = res['data'] as Map<String, dynamic>;
      _product = Product.fromJson(data);
      // reviews (non-fatal)
      try {
        final revRes = await _api.getProductReviews(widget.slug);
        final rList = revRes['data'] as List? ?? [];
        _reviews = rList
            .map((e) => Review.fromJson(e as Map<String, dynamic>))
            .toList();
      } catch (_) {
        _reviews = [];
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _toggleWishlist() async {
    if (!await AuthGuard.ensure(context)) return;
    final p = _product;
    if (p == null) return;
    try {
      if (_wishlisted) {
        await _api.removeFromWishlist(p.id);
      } else {
        await _api.addToWishlist(p.id);
      }
      setState(() => _wishlisted = !_wishlisted);
      if (mounted) {
        context.read<AppProvider>().refreshWishlistCount();
      }
    } catch (e) {
      _toast(e.toString());
    }
  }

  Future<void> _addToCart() async {
    if (!await AuthGuard.ensure(context)) return;
    final p = _product;
    if (p == null) return;
    setState(() => _adding = true);
    try {
      await _api.addToCart(p.id);
      if (mounted) context.read<AppProvider>().refreshCartCount();
      _toast('Added to cart 🛒', success: true);
    } catch (e) {
      _toast(e.toString());
    } finally {
      if (mounted) setState(() => _adding = false);
    }
  }

  void _toast(String msg, {bool success = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        behavior: SnackBarBehavior.floating,
        backgroundColor:
            success ? AppTheme.success : AppTheme.danger,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: _loading
          ? const _Loading()
          : _error != null
              ? ErrorState(message: _error!, onRetry: _load)
              : _content(context),
      bottomNavigationBar:
          _loading || _product == null ? null : _bottomBar(context),
    );
  }

  Widget _content(BuildContext context) {
    final p = _product!;
    final images = p.images.isEmpty
        ? [const ProductImage(id: 0, imagePath: '', isPrimary: true)]
        : p.images;
    return CustomScrollView(
      slivers: [
        // ── Image carousel with gradient app bar ──────────────────────
        SliverAppBar(
          expandedHeight: 360,
          pinned: true,
          stretch: true,
          backgroundColor: Colors.white,
          foregroundColor: AppTheme.textPrimary,
          elevation: 0,
          leading: Padding(
            padding: const EdgeInsets.all(8),
            child: _CircleAction(
                icon: Icons.arrow_back_rounded,
                onTap: () => Navigator.pop(context)),
          ),
          actions: [
            Padding(
              padding: const EdgeInsets.all(8),
              child: _CircleAction(
                icon: _wishlisted
                    ? Icons.favorite_rounded
                    : Icons.favorite_border_rounded,
                iconColor:
                    _wishlisted ? AppTheme.danger : AppTheme.textPrimary,
                onTap: _toggleWishlist,
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(right: 8, top: 8, bottom: 8),
              child: _CircleAction(
                icon: Icons.share_outlined,
                onTap: () {
                  // best-effort share text
                  ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Check out ${p.name}!')));
                },
              ),
            ),
          ],
          flexibleSpace: FlexibleSpaceBar(
            background: Stack(
              fit: StackFit.expand,
              children: [
                PageView.builder(
                  controller: _pageCtrl,
                  itemCount: images.length,
                  itemBuilder: (_, i) => AppNetworkImage(
                    images[i].imagePath,
                    fit: BoxFit.contain,
                  ),
                ),
                Positioned(
                  bottom: 12,
                  left: 0,
                  right: 0,
                  child: Center(
                    child: SmoothPageIndicator(
                      controller: _pageCtrl,
                      count: images.length,
                      effect: const WormEffect(
                        dotWidth: 7,
                        dotHeight: 7,
                        activeDotColor: AppTheme.primary,
                        dotColor: Color(0xFFD9DCE2),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        SliverToBoxAdapter(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _header(p),
              if (p.specifications.isNotEmpty) _specs(p),
              _description(p),
              _reviewsSection(p),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ],
    );
  }

  // ── Sections ────────────────────────────────────────────────────────

  Widget _header(Product p) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (p.brand != null)
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: AppTheme.primaryLight,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                p.brand!.name,
                style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.primaryDark),
              ),
            ),
          const SizedBox(height: 10),
          Text(
            p.name,
            style: const TextStyle(
                fontSize: 22, fontWeight: FontWeight.bold, height: 1.3),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              if (p.rating != null) ...[
                RatingStars(rating: p.rating!, size: 16),
                const SizedBox(width: 6),
                Text(
                  '${p.rating!.toStringAsFixed(1)}  •  ${p.reviewCount} reviews',
                  style: const TextStyle(
                      fontSize: 13, color: AppTheme.textSecondary),
                ),
              ] else
                const Text('No ratings yet',
                    style: TextStyle(
                        fontSize: 13, color: AppTheme.textSecondary)),
              const Spacer(),
              if (p.stock > 0)
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE8F5E9),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Text(
                    'In Stock',
                    style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.success),
                  ),
                )
              else
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFDECEC),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Text(
                    'Out of Stock',
                    style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.danger),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                Format.currency(p.effectivePrice),
                style: const TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primary),
              ),
              if (p.isOnSale) ...[
                const SizedBox(width: 8),
                Padding(
                  padding: const EdgeInsets.only(bottom: 3),
                  child: Text(
                    Format.currency(p.price),
                    style: const TextStyle(
                        fontSize: 15,
                        color: AppTheme.textHint,
                        decoration: TextDecoration.lineThrough),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    gradient: AppTheme.dealGradient,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '${p.discountPercent}% OFF',
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ],
          ),
          const Divider(height: 32),
        ],
      ),
    );
  }

  Widget _specs(Product p) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(
              title: 'Specifications',
              padding: EdgeInsets.symmetric(vertical: 8)),
          ...p.specifications.entries.map(
            (e) => Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    width: 120,
                    child: Text(e.key,
                        style: const TextStyle(
                            color: AppTheme.textSecondary, fontSize: 13)),
                  ),
                  Expanded(
                    child: Text(e.value,
                        style: const TextStyle(
                            fontWeight: FontWeight.w500, fontSize: 13)),
                  ),
                ],
              ),
            ),
          ),
          const Divider(height: 32),
        ],
      ),
    );
  }

  Widget _description(Product p) {
    if (p.description == null || p.description!.trim().isEmpty) {
      return const SizedBox.shrink();
    }
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Description',
            style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: AppTheme.textPrimary),
          ),
          const SizedBox(height: 8),
          Text(
            p.description!,
            style:
                const TextStyle(fontSize: 14, color: AppTheme.textSecondary, height: 1.6),
          ),
          const Divider(height: 32),
        ],
      ),
    );
  }

  Widget _reviewsSection(Product p) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text(
                'Reviews',
                style: TextStyle(
                    fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(width: 8),
              Text('(${p.reviewCount})',
                  style: const TextStyle(color: AppTheme.textSecondary)),
              const Spacer(),
              if (p.reviewCount > 0)
                TextButton(
                  onPressed: () => Nav.toReviews(context, p.slug,
                      productName: p.name),
                  child: const Text('See all'),
                ),
            ],
          ),
          const SizedBox(height: 8),
          if (_reviews.isEmpty)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFF7F8FA),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Row(
                children: [
                  Icon(Icons.reviews_outlined,
                      color: AppTheme.textHint, size: 20),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'No reviews yet. Be the first to review!',
                      style: TextStyle(
                          fontSize: 13, color: AppTheme.textSecondary),
                    ),
                  ),
                ],
              ),
            )
          else
            ..._reviews.take(2).map(_reviewCard),
        ],
      ),
    );
  }

  Widget _reviewCard(Review r) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF7F8FA),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 14,
                backgroundColor: AppTheme.primaryLight,
                child: Text(
                  (r.userName ?? 'U').isNotEmpty
                      ? (r.userName ?? 'U')[0].toUpperCase()
                      : 'U',
                  style: const TextStyle(
                      color: AppTheme.primary, fontSize: 12),
                ),
              ),
              const SizedBox(width: 8),
              Text(r.userName ?? 'Anonymous',
                  style: const TextStyle(fontWeight: FontWeight.w600)),
              const Spacer(),
              RatingStars(rating: r.rating.toDouble(), size: 12),
            ],
          ),
          if (r.title != null && r.title!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(r.title!,
                style: const TextStyle(fontWeight: FontWeight.w600)),
          ],
          if (r.body != null && r.body!.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(r.body!,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                    fontSize: 13, color: AppTheme.textSecondary)),
          ],
          const SizedBox(height: 4),
          Text(Format.timeAgo(r.createdAt),
              style: const TextStyle(
                  fontSize: 11, color: AppTheme.textHint)),
        ],
      ),
    );
  }

  // ── Bottom action bar ───────────────────────────────────────────────

  Widget _bottomBar(BuildContext context) {
    return SafeArea(
      child: Container(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 12,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: Row(
          children: [
            IconButton(
              onPressed: _toggleWishlist,
              icon: Icon(
                _wishlisted
                    ? Icons.favorite_rounded
                    : Icons.favorite_border_rounded,
                color: _wishlisted ? AppTheme.danger : AppTheme.textSecondary,
              ),
              tooltip: 'Wishlist',
            ),
            const SizedBox(width: 8),
            Expanded(
              child: GradientButton(
                label: _product!.stock > 0 ? 'Add to Cart' : 'Out of Stock',
                icon: Icons.add_shopping_cart_rounded,
                loading: _adding,
                onPressed: _product!.stock > 0 ? _addToCart : null,
                height: 48,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CircleAction extends StatelessWidget {
  const _CircleAction(
      {required this.icon, required this.onTap, this.iconColor});
  final IconData icon;
  final VoidCallback onTap;
  final Color? iconColor;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white.withValues(alpha: 0.9),
      shape: const CircleBorder(),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: SizedBox(
          width: 40,
          height: 40,
          child: Icon(icon, size: 20, color: iconColor ?? AppTheme.textPrimary),
        ),
      ),
    );
  }
}

class _Loading extends StatelessWidget {
  const _Loading();

  @override
  Widget build(BuildContext context) {
    return const Center(child: CircularProgressIndicator());
  }
}
