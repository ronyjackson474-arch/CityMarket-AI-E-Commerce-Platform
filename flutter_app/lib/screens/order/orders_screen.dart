import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/order.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/auth_guard.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/states.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  final _api = ApiService();
  List<Order> _orders = [];
  bool _loading = true;
  String? _error;

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
      final res = await _api.getOrders(page: 1);
      final list = res['data'] as List? ?? [];
      _orders = list
          .map((e) => Order.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoggedIn = context.watch<AppProvider>().isLoggedIn;
    return Scaffold(
      appBar: AppBar(title: const Text('My Orders')),
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
                    subtitle: 'Log in to view your orders.',
                    actionLabel: 'Log In',
                    onAction: () => AuthGuard.ensure(context),
                  ),
                ],
              )
            : _loading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? ErrorState(message: _error!, onRetry: _load)
                    : _orders.isEmpty
                        ? ListView(
                            children: [
                              const SizedBox(height: 80),
                              EmptyState(
                                icon: Icons.receipt_long_outlined,
                                title: 'No orders yet',
                                subtitle:
                                    'Your placed orders will appear here.',
                                actionLabel: 'Start Shopping',
                                onAction: () => Nav.toHome(context),
                              ),
                            ],
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.all(16),
                            itemCount: _orders.length,
                            separatorBuilder: (_, __) =>
                                const SizedBox(height: 12),
                            itemBuilder: (_, i) =>
                                _orderCard(_orders[i]),
                          ),
      ),
        ),
      ),
    );
  }

  Widget _orderCard(Order o) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () => Nav.toOrderDetail(context, o.orderNumber),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      'Order #${o.orderNumber}',
                      style: const TextStyle(
                          fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ),
                  _statusChip(o.status),
                ],
              ),
              const SizedBox(height: 4),
              Text(
                '${o.items.length} item(s) • ${Format.date(o.createdAt)}',
                style: const TextStyle(
                    fontSize: 12, color: AppTheme.textSecondary),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  ...o.items.take(3).map((item) => Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            width: 40,
                            height: 40,
                            color: const Color(0xFFF1F3F6),
                            child: const Icon(Icons.inventory_2_outlined,
                                size: 18, color: AppTheme.textHint),
                          ),
                        ),
                      )),
                  const Spacer(),
                  Text(
                    Format.currency(o.total),
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: AppTheme.primary),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: () =>
                      Nav.toOrderDetail(context, o.orderNumber),
                  child: const Text('Track Order'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _statusChip(String status) {
    final color = _statusColor(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        _statusLabel(status),
        style: TextStyle(
            color: color, fontSize: 11, fontWeight: FontWeight.w700),
      ),
    );
  }
}

Color _statusColor(String s) {
  switch (s.toLowerCase()) {
    case 'delivered':
      return AppTheme.success;
    case 'cancelled':
      return AppTheme.danger;
    case 'shipped':
    case 'out_for_delivery':
      return AppTheme.secondary;
    default:
      return AppTheme.accent;
  }
}

String _statusLabel(String s) {
  switch (s.toLowerCase()) {
    case 'out_for_delivery':
      return 'OUT FOR DELIVERY';
    default:
      return s.toUpperCase();
  }
}
