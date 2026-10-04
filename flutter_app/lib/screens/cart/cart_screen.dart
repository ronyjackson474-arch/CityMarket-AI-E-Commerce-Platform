import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/order.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/auth_guard.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/buttons.dart';
import '../../widgets/states.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  Cart? _cart;
  bool _loading = true;
  String? _error;
  bool _mutating = false;

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
      final res = await ApiService().getCart();
      _cart = Cart.fromJson(res);
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _updateQty(CartItem item, int delta) async {
    final newQty = item.quantity + delta;
    if (newQty < 1) return;
    setState(() => _mutating = true);
    try {
      final res = await ApiService().updateCartItem(item.id, newQty);
      _cart = Cart.fromJson(res);
      context.read<AppProvider>().refreshCartCount();
    } catch (e) {
      _toast(e.toString());
    } finally {
      if (mounted) setState(() => _mutating = false);
    }
  }

  Future<void> _remove(CartItem item) async {
    setState(() => _mutating = true);
    try {
      final res = await ApiService().removeFromCart(item.id);
      _cart = Cart.fromJson(res);
      context.read<AppProvider>().refreshCartCount();
      _toast('Removed from cart', success: true);
    } catch (e) {
      _toast(e.toString());
    } finally {
      if (mounted) setState(() => _mutating = false);
    }
  }

  void _toast(String msg, {bool success = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        behavior: SnackBarBehavior.floating,
        backgroundColor: success ? AppTheme.success : AppTheme.danger,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Cart'),
        actions: [
          if (_cart != null && _cart!.items.isNotEmpty)
            TextButton(
              onPressed: _mutating ? null : _clearCart,
              child: const Text('Clear'),
            ),
        ],
      ),
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 900),
          child: RefreshIndicator(
            onRefresh: _load,
            child: _buildBody(context),
          ),
        ),
      ),
      bottomNavigationBar:
          _cart != null && _cart!.items.isNotEmpty ? _checkoutBar() : null,
    );
  }

  Widget _buildBody(BuildContext context) {
    final isLoggedIn = context.watch<AppProvider>().isLoggedIn;
    if (!isLoggedIn) return _loginPrompt();
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return ErrorState(message: _error!, onRetry: _load);
    }
    if (_cart == null || _cart!.items.isEmpty) {
      return ListView(
        children: [
          const SizedBox(height: 80),
          EmptyState(
            icon: Icons.shopping_cart_outlined,
            title: 'Your cart is empty',
            subtitle: 'Looks like you haven\'t added anything yet.',
            actionLabel: 'Start Shopping',
            onAction: () => Nav.toHome(context),
          ),
        ],
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16).copyWith(bottom: 120),
      itemCount: _cart!.items.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (_, i) => _cartItem(_cart!.items[i]),
    );
  }

  Widget _loginPrompt() {
    return ListView(
      children: [
        const SizedBox(height: 80),
        EmptyState(
          icon: Icons.lock_outline_rounded,
          title: 'Please sign in',
          subtitle: 'Log in to view and manage your cart.',
          actionLabel: 'Log In',
          onAction: () => AuthGuard.ensure(context),
        ),
      ],
    );
  }

  Widget _cartItem(CartItem item) {
    return Dismissible(
      key: ValueKey(item.id),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        decoration: BoxDecoration(
          color: AppTheme.danger,
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Icon(Icons.delete_outline_rounded, color: Colors.white),
      ),
      confirmDismiss: (_) async {
        await _remove(item);
        return false; // we already update via API
      },
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: Column(
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  GestureDetector(
                    onTap: () => Nav.toProduct(context, item.product.slug),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: SizedBox(
                        width: 84,
                        height: 84,
                        child: AppNetworkImage(item.product.primaryImage,
                            fit: BoxFit.cover),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item.product.name,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                              fontSize: 14, fontWeight: FontWeight.w600),
                        ),
                        if (item.product.brand != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            item.product.brand!.name,
                            style: const TextStyle(
                                fontSize: 12,
                                color: AppTheme.textSecondary),
                          ),
                        ],
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            Text(
                              Format.currency(item.effectivePrice),
                              style: const TextStyle(
                                  color: AppTheme.primary,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 15),
                            ),
                            if (item.product.isOnSale) ...[
                              const SizedBox(width: 6),
                              Text(
                                Format.currency(item.product.price),
                                style: const TextStyle(
                                    fontSize: 12,
                                    color: AppTheme.textHint,
                                    decoration:
                                        TextDecoration.lineThrough),
                              ),
                            ],
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              _cartItemBottom(item),
            ],
          ),
        ),
      ),
    );
  }

  Widget _cartItemBottom(CartItem item) {
    return Row(
      children: [
        QuantityStepper(
          quantity: item.quantity,
          onDecrease: () => _updateQty(item, -1),
          onIncrease: () => _updateQty(item, 1),
        ),
        const Spacer(),
        Text(
          Format.currency(item.lineTotal),
          style: const TextStyle(
              fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
        ),
      ],
    );
  }

  Future<void> _clearCart() async {
    setState(() => _mutating = true);
    try {
      await ApiService().clearCart();
      _cart = Cart.empty();
      context.read<AppProvider>().refreshCartCount();
      _toast('Cart cleared', success: true);
    } catch (e) {
      _toast(e.toString());
    } finally {
      if (mounted) setState(() => _mutating = false);
    }
  }

  Widget _checkoutBar() {
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
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Subtotal',
                    style: TextStyle(color: AppTheme.textSecondary)),
                Text(
                  Format.currency(_cart!.subtotal),
                  style: const TextStyle(
                      fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 8),
            if (_cart!.summary.shipping > 0)
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Shipping',
                        style: TextStyle(color: AppTheme.textSecondary)),
                    Text(Format.currency(_cart!.summary.shipping),
                        style: const TextStyle(
                            fontWeight: FontWeight.w600,
                            color: AppTheme.textSecondary)),
                  ],
                ),
              ),
            GradientButton(
              label: 'Checkout',
              icon: Icons.shopping_bag_rounded,
              loading: _mutating,
              onPressed: () => Nav.toCheckout(context),
            ),
          ],
        ),
      ),
    );
  }
}
