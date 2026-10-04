import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';
import '../../utils/nav.dart';
import '../../widgets/buttons.dart';

/// Success screen shown after an order is placed. Lets the user view the
/// order details or continue shopping.
class OrderPlacedScreen extends StatelessWidget {
  const OrderPlacedScreen({super.key, required this.orderNumber});

  final String orderNumber;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                width: 100,
                height: 100,
                decoration: BoxDecoration(
                  color: const Color(0xFFE8F5E9),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded,
                    color: AppTheme.success, size: 60),
              ),
              const SizedBox(height: 24),
              const Text(
                'Order Placed!',
                textAlign: TextAlign.center,
                style: TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textPrimary),
              ),
              const SizedBox(height: 8),
              Text(
                'Thank you for your purchase.',
                textAlign: TextAlign.center,
                style: TextStyle(
                    fontSize: 15, color: Colors.grey.shade600),
              ),
              const SizedBox(height: 6),
              Text(
                'Order #$orderNumber',
                textAlign: TextAlign.center,
                style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.primary),
              ),
              const SizedBox(height: 8),
              Text(
                'You can track your order from the Orders section.',
                textAlign: TextAlign.center,
                style: TextStyle(
                    fontSize: 13, color: Colors.grey.shade500),
              ),
              const SizedBox(height: 36),
              GradientButton(
                label: 'View Order',
                icon: Icons.receipt_long_rounded,
                onPressed: () {
                  Navigator.of(context).pop(); // pop this screen
                  Nav.toOrderDetail(context, orderNumber);
                },
              ),
              const SizedBox(height: 12),
              OutlinedButton(
                onPressed: () {
                  // pop everything back to root (Home tab)
                  Navigator.of(context)
                      .popUntil((route) => route.isFirst);
                },
                child: const Text('Continue Shopping'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
