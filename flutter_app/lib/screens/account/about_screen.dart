import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('About')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            Center(
              child: Container(
                width: 90,
                height: 90,
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  borderRadius: BorderRadius.circular(22),
                ),
                child: const Icon(Icons.storefront_rounded,
                    color: Colors.white, size: 48),
              ),
            ),
            const SizedBox(height: 18),
            const Center(
              child: Text(
                'CityMarket',
                style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textPrimary),
              ),
            ),
            const SizedBox(height: 4),
            const Center(
              child: Text('Version 1.0.0',
                  style:
                      TextStyle(color: AppTheme.textSecondary, fontSize: 13)),
            ),
            const SizedBox(height: 8),
            const Center(
              child: Text(
                'Shop smarter with AI',
                style: TextStyle(
                    fontStyle: FontStyle.italic,
                    color: AppTheme.textSecondary),
              ),
            ),
            const SizedBox(height: 28),
            const Text('About',
                style: TextStyle(
                    fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            const Text(
              'CityMarket is an AI-powered e-commerce marketplace bringing you '
              'the best deals on electronics, fashion, home goods, and more. '
              'Our AI shopping assistant helps you find exactly what you need '
              'using natural language.',
              style: TextStyle(
                  color: AppTheme.textSecondary, height: 1.6, fontSize: 14),
            ),
            const SizedBox(height: 24),
            const Text('Features',
                style: TextStyle(
                    fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            ..._features.map((f) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.check_circle_rounded,
                          color: AppTheme.success, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(f,
                            style: const TextStyle(
                                fontSize: 14,
                                color: AppTheme.textSecondary)),
                      ),
                    ],
                  ),
                )),
            const SizedBox(height: 28),
            const Divider(),
            const SizedBox(height: 12),
            ListTile(
              leading: const Icon(Icons.privacy_tip_outlined,
                  color: AppTheme.textSecondary),
              title: const Text('Privacy Policy'),
              trailing: const Icon(Icons.chevron_right_rounded,
                  color: AppTheme.textHint),
              onTap: () {},
            ),
            ListTile(
              leading: const Icon(Icons.description_outlined,
                  color: AppTheme.textSecondary),
              title: const Text('Terms & Conditions'),
              trailing: const Icon(Icons.chevron_right_rounded,
                  color: AppTheme.textHint),
              onTap: () {},
            ),
            ListTile(
              leading: const Icon(Icons.support_outlined,
                  color: AppTheme.textSecondary),
              title: const Text('Contact Support'),
              trailing: const Icon(Icons.chevron_right_rounded,
                  color: AppTheme.textHint),
              onTap: () {},
            ),
          ],
        ),
      ),
    );
  }

  static const _features = [
    'Browse thousands of products across categories',
    'AI-powered natural language product search',
    'Real-time flash deals with countdown timers',
    'Secure cart, checkout, and order tracking',
    'Wishlist, wallet, and referral rewards',
    'Write and read verified product reviews',
    'Personalised recommendations powered by AI',
    '24/7 AI shopping assistant chat',
  ];
}
