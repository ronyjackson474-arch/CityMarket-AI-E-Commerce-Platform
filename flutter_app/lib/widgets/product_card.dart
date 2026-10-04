import 'package:flutter/material.dart';
import 'package:flutter_staggered_animations/flutter_staggered_animations.dart';

import '../models/product.dart';
import '../theme/app_theme.dart';
import '../utils/format.dart';
import 'app_network_image.dart';

/// A single product tile used in grids. Shows image, name, rating, price
/// with sale discount badge and a quick add-to-cart FAB.
class ProductCard extends StatelessWidget {
  const ProductCard({
    super.key,
    required this.product,
    this.onTap,
    this.onAddToCart,
    this.compact = false,
  });

  final Product product;
  final VoidCallback? onTap;
  final VoidCallback? onAddToCart;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    return AnimationConfiguration.staggeredGrid(
      position: 0,
      duration: const Duration(milliseconds: 300),
      columnCount: 2,
      child: ScaleAnimation(
        scale: 0.9,
        child: FadeInAnimation(
          child: Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            child: InkWell(
              borderRadius: BorderRadius.circular(16),
              onTap: onTap,
              child: Padding(
                padding: const EdgeInsets.all(8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // ── image + badges ─────────────────────────────
                    Stack(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: AspectRatio(
                            aspectRatio: 1,
                            child: AppNetworkImage(
                              product.primaryImage,
                              fit: BoxFit.cover,
                            ),
                          ),
                        ),
                        if (product.isOnSale)
                          Positioned(
                            top: 6,
                            left: 6,
                            child: _DiscountBadge(
                                percent: product.discountPercent),
                          ),
                        if (product.isFlashDeal)
                          Positioned(
                            top: 6,
                            right: 6,
                            child: _MiniTag(
                              label: 'FLASH',
                              color: AppTheme.danger,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    // ── name ──────────────────────────────────────
                    Text(
                      product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        height: 1.3,
                        color: AppTheme.textPrimary,
                      ),
                    ),
                    if (!compact) ...[
                      const SizedBox(height: 4),
                      // brand + rating row
                      Row(
                        children: [
                          if (product.brand != null)
                            Expanded(
                              child: Text(
                                product.brand!.name,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                            ),
                          if (product.rating != null)
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.star_rounded,
                                    size: 13, color: Color(0xFFFFB300)),
                                const SizedBox(width: 2),
                                Text(
                                  product.rating!.toStringAsFixed(1),
                                  style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: AppTheme.textSecondary),
                                ),
                              ],
                            ),
                        ],
                      ),
                    ],
                    const Spacer(),
                    // ── price ─────────────────────────────────────
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          Format.currency(product.effectivePrice),
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primary,
                          ),
                        ),
                        if (product.isOnSale) ...[
                          const SizedBox(width: 6),
                          Padding(
                            padding: const EdgeInsets.only(bottom: 2),
                            child: Text(
                              Format.currency(product.price),
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppTheme.textHint,
                                decoration: TextDecoration.lineThrough,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _DiscountBadge extends StatelessWidget {
  const _DiscountBadge({required this.percent});
  final int percent;

  @override
  Widget build(BuildContext context) {
    if (percent <= 0) return const SizedBox.shrink();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
      decoration: BoxDecoration(
        gradient: AppTheme.dealGradient,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        '-$percent%',
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}

class _MiniTag extends StatelessWidget {
  const _MiniTag({required this.label, required this.color});
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 9,
          fontWeight: FontWeight.bold,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

/// A responsive 2-column grid of [ProductCard]s with staggered entrance
/// animation. Used across Home, Category, Search, Wishlist screens.
class ProductGrid extends StatelessWidget {
  const ProductGrid({
    super.key,
    required this.products,
    this.onTap,
    this.onAddToCart,
    this.shrinkWrap = false,
    this.padding = const EdgeInsets.all(16),
    this.physics,
  });

  final List<Product> products;
  final void Function(Product)? onTap;
  final void Function(Product)? onAddToCart;
  final bool shrinkWrap;
  final EdgeInsetsGeometry padding;
  final ScrollPhysics? physics;

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    // Phone: 2 | Tablet: 3 | Desktop 1024+: 4 | Wide 1400+: 5
    final int columns = width >= 1400
        ? 5
        : width >= 1024
            ? 4
            : width >= 600
                ? 3
                : 2;
    // Centered max width on desktop so cards don't stretch absurdly.
    final double maxGridWidth = width >= 1024 ? 1200 : double.infinity;
    final EdgeInsets gridPadding = EdgeInsets.only(
      left: width >= 1024 ? (width - 1200 > 0 ? (width - 1200) / 2 : 32) : padding.left,
      right: width >= 1024 ? (width - 1200 > 0 ? (width - 1200) / 2 : 32) : padding.right,
      top: padding.top,
      bottom: padding.bottom,
    );
    return AnimationLimiter(
      child: Center(
        child: ConstrainedBox(
          constraints: BoxConstraints(maxWidth: maxGridWidth),
          child: GridView.builder(
            shrinkWrap: shrinkWrap,
            physics: physics,
            padding: gridPadding,
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: columns,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 0.68,
            ),
            itemCount: products.length,
            itemBuilder: (context, i) {
              final p = products[i];
              return ProductCard(
                product: p,
                onTap: onTap != null ? () => onTap!(p) : null,
              );
            },
          ),
        ),
      ),
    );
  }
}
