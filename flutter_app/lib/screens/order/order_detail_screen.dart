import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/order.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/buttons.dart';
import '../../widgets/states.dart';

class OrderDetailScreen extends StatefulWidget {
  const OrderDetailScreen({super.key, required this.orderNumber});

  final String orderNumber;

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  Order? _order;
  bool _loading = true;
  String? _error;
  bool _busy = false;

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
      final res = await ApiService().getOrder(widget.orderNumber);
      final data = (res['data'] as Map<String, dynamic>?) ?? {};
      _order = Order.fromJson(data);
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _cancel() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel order?'),
        content: const Text(
            'This action cannot be undone. Are you sure you want to cancel this order?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('No')),
          TextButton(
              onPressed: () => Navigator.pop(ctx, true),
              style: TextButton.styleFrom(foregroundColor: AppTheme.danger),
              child: const Text('Cancel Order')),
        ],
      ),
    );
    if (ok != true) return;
    setState(() => _busy = true);
    try {
      await ApiService().cancelOrder(widget.orderNumber);
      _toast('Order cancelled', success: true);
      await _load();
    } catch (e) {
      _toast(e.toString());
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _return() async {
    final reason = await showDialog<String>(
      context: context,
      builder: (ctx) {
        final ctrl = TextEditingController();
        return AlertDialog(
          title: const Text('Request Return'),
          content: TextField(
            controller: ctrl,
            maxLines: 3,
            decoration: const InputDecoration(
              hintText: 'Why are you returning this order?',
            ),
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
            TextButton(
              onPressed: () =>
                  Navigator.pop(ctx, ctrl.text.trim().isEmpty ? null : ctrl.text.trim()),
              child: const Text('Submit'),
            ),
          ],
        );
      },
    );
    if (reason == null) return;
    setState(() => _busy = true);
    try {
      await ApiService().requestReturn(widget.orderNumber, reason);
      _toast('Return requested', success: true);
      await _load();
    } catch (e) {
      _toast(e.toString());
    } finally {
      if (mounted) setState(() => _busy = false);
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
      appBar: AppBar(title: const Text('Order Details')),
      body: LoadingOverlay(
        isLoading: _busy,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ErrorState(message: _error!, onRetry: _load)
                : _order == null
                    ? const EmptyState(
                        icon: Icons.receipt_long_outlined,
                        title: 'Order not found',
                      )
                    : _body(),
      ),
    );
  }

  Widget _body() {
    final o = _order!;
    return ListView(
      padding: const EdgeInsets.all(16).copyWith(bottom: 100),
      children: [
        // status header
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: AppTheme.headerGradient,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Order #${o.orderNumber}',
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 4),
              Text(
                'Placed on ${Format.date(o.createdAt)}',
                style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 13),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  _statusChip(o.status),
                  const SizedBox(width: 8),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      o.paymentMethod.toUpperCase(),
                      style: const TextStyle(
                          color: Colors.white, fontSize: 11),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),
        // status timeline
        const Text('Order Status',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 12),
        _timeline(o.status),
        const SizedBox(height: 20),
        // items
        const Text('Items',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        ...o.items.map(_itemTile),
        const Divider(height: 32),
        // totals
        _totalsRow('Subtotal', o.subtotal),
        if (o.discount > 0)
          _totalsRow('Discount', -o.discount, color: AppTheme.success),
        _totalsRow('Shipping', o.shipping,
            free: o.shipping == 0),
        const Divider(),
        _totalsRow('Total', o.total, bold: true),
        const SizedBox(height: 16),
        // actions
        if (o.status.toLowerCase() == 'pending' ||
            o.status.toLowerCase() == 'processing')
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _cancel,
              icon: const Icon(Icons.close_rounded, size: 18),
              label: const Text('Cancel Order'),
              style: OutlinedButton.styleFrom(foregroundColor: AppTheme.danger),
            ),
          ),
        if (o.status.toLowerCase() == 'delivered')
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _return,
              icon: const Icon(Icons.undo_rounded, size: 18),
              label: const Text('Request Return'),
            ),
          ),
      ],
    );
  }

  Widget _itemTile(OrderItem item) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: ClipRRect(
        borderRadius: BorderRadius.circular(8),
        child: SizedBox(
          width: 50,
          height: 50,
          child: AppNetworkImage(item.productImage, fit: BoxFit.cover),
        ),
      ),
      title: Text(item.productName,
          maxLines: 1, overflow: TextOverflow.ellipsis),
      subtitle: Text(
          '${Format.currency(item.price)} × ${item.quantity}'),
      trailing: Text(Format.currency(item.lineTotal),
          style: const TextStyle(fontWeight: FontWeight.w600)),
      onTap: item.productSlug != null
          ? () => Nav.toProduct(context, item.productSlug!)
          : null,
    );
  }

  Widget _timeline(String status) {
    final steps = const ['pending', 'processing', 'shipped', 'delivered'];
    final idx = steps.indexOf(status.toLowerCase());
    final displayIdx = idx == -1 ? 0 : idx;
    return Column(
      children: List.generate(steps.length, (i) {
        final done = i <= displayIdx;
        final isLast = i == steps.length - 1;
        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Column(
              children: [
                Container(
                  width: 24,
                  height: 24,
                  decoration: BoxDecoration(
                    color: done ? AppTheme.primary : const Color(0xFFE0E3E8),
                    shape: BoxShape.circle,
                  ),
                  child: done
                      ? const Icon(Icons.check_rounded,
                          color: Colors.white, size: 14)
                      : null,
                ),
                if (!isLast)
                  Container(
                    width: 2,
                    height: 28,
                    color: done && i < displayIdx
                        ? AppTheme.primary
                        : const Color(0xFFE0E3E8),
                  ),
              ],
            ),
            const SizedBox(width: 12),
            Padding(
              padding: const EdgeInsets.only(top: 3),
              child: Text(
                _stepLabel(steps[i]),
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: done ? FontWeight.w600 : FontWeight.normal,
                  color: done ? AppTheme.textPrimary : AppTheme.textHint,
                ),
              ),
            ),
          ],
        );
      }),
    );
  }

  String _stepLabel(String s) {
    switch (s) {
      case 'pending':
        return 'Order Placed';
      case 'processing':
        return 'Processing';
      case 'shipped':
        return 'Shipped';
      case 'delivered':
        return 'Delivered';
      default:
        return s;
    }
  }

  Widget _statusChip(String status) {
    final color = _statusColor(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.2),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        status.toUpperCase(),
        style: const TextStyle(
            color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
      ),
    );
  }

  Widget _totalsRow(String label, num amount,
      {bool bold = false, bool free = false, Color? color}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                  fontWeight: bold ? FontWeight.bold : FontWeight.normal,
                  color: AppTheme.textSecondary,
                  fontSize: bold ? 16 : 14)),
          Text(
            free ? 'FREE' : Format.currency(amount),
            style: TextStyle(
                fontWeight: bold ? FontWeight.bold : FontWeight.w600,
                color: color ??
                    (bold ? AppTheme.primary : AppTheme.textPrimary),
                fontSize: bold ? 18 : 14),
          ),
        ],
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
