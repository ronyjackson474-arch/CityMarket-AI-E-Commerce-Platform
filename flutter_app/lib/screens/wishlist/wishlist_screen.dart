import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/product.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/auth_guard.dart';
import '../../utils/nav.dart';
import '../../widgets/product_card.dart';
import '../../widgets/shimmer.dart';
import '../../widgets/states.dart';

class WishlistScreen extends StatefulWidget {
  const WishlistScreen({super.key});

  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  List<Product> _items = [];
  bool _loading = true;
  String? _error;
  final _api = ApiService();

  @override
  void initState() {
    super.initState();
    _maybeLoad();
  }

  Future<void> _maybeLoad() async {
    final app = context.read<AppProvider>();
    if (!app.isLoggedIn) {
      setState(() => _loading = false);
      return;
    }
    await _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await _api.getWishlist(page: 1);
      final list = res['data'] as List? ?? [];
      _items = list
          .map((e) => Product.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _remove(Product p) async {
    try {
      await _api.removeFromWishlist(p.id);
      setState(() => _items.removeWhere((e) => e.id == p.id));
      if (mounted) context.read<AppProvider>().refreshWishlistCount();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoggedIn = context.watch<AppProvider>().isLoggedIn;
    return Scaffold(
      appBar: AppBar(title: const Text('Wishlist')),
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 900),
          child: RefreshIndicator(
        onRefresh: _load,
        child: !isLoggedIn
            ? ListView(
                children: [
                  const SizedBox(height: 80),
                  EmptyState(
                    icon: Icons.lock_outline_rounded,
                    title: 'Please sign in',
                    subtitle: 'Log in to view your wishlist.',
                    actionLabel: 'Log In',
                    onAction: () => AuthGuard.ensure(context),
                  ),
                ],
              )
            : _loading
                ? const ProductGridSkeleton()
                : _error != null
                    ? ErrorState(message: _error!, onRetry: _load)
                    : _items.isEmpty
                        ? ListView(
                            children: [
                              const SizedBox(height: 80),
                              EmptyState(
                                icon: Icons.favorite_border_rounded,
                                title: 'Your wishlist is empty',
                                subtitle:
                                    'Tap the heart on products to save them here.',
                                actionLabel: 'Browse Products',
                                onAction: () => Nav.toHome(context),
                              ),
                            ],
                          )
                        : ProductGrid(
                            products: _items,
                            onTap: (p) => Nav.toProduct(context, p.slug),
                          ),
      ),
        ),
      ),
    );
  }
}
