import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/user.dart';
import '../../providers/app_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/auth_guard.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/buttons.dart';
import '../auth/login_screen.dart';

/// Profile / account hub. Shows a guest banner when logged out, otherwise a
/// user card + links to orders, wallet, addresses, settings.
class AccountScreen extends StatelessWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppProvider>();
    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async => app.refreshUser(),
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              app.isLoggedIn ? _loggedIn(app) : _guest(context),
            ],
          ),
        ),
      ),
    );
  }

  // ── Logged in ──────────────────────────────────────────────────────

  Widget _loggedIn(AppProvider app) {
    return Builder(builder: (context) {
      return Column(
        children: [
          // header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(20, 24, 20, 28),
            decoration: const BoxDecoration(
                gradient: AppTheme.headerGradient,
                borderRadius:
                    BorderRadius.vertical(bottom: Radius.circular(28))),
            child: Column(
              children: [
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 3),
                  ),
                  child: ClipOval(
                    child: (app.user?.avatar != null &&
                            app.user!.avatar!.isNotEmpty)
                        ? AppNetworkImage(app.user!.avatar, fit: BoxFit.cover)
                        : Center(
                            child: Text(
                              _initials(app.user),
                              style: const TextStyle(
                                  fontSize: 30,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primary),
                            ),
                          ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  app.user?.name ?? 'User',
                  style: const TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(
                  app.user?.email ?? '',
                  style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.9), fontSize: 13),
                ),
                if (app.user?.phone != null &&
                    app.user!.phone!.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    app.user!.phone!,
                    style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.9), fontSize: 13),
                  ),
                ],
                const SizedBox(height: 16),
                SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => Nav.toEditProfile(context),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Colors.white70),
                      foregroundColor: Colors.white,
                    ),
                    icon: const Icon(Icons.edit_rounded, size: 16),
                    label: const Text('Edit Profile',
                        style: TextStyle(fontSize: 13)),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          // menu
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              children: [
                _menuSection('Shopping', [
                  _MenuItem(
                    icon: Icons.receipt_long_rounded,
                    label: 'My Orders',
                    onTap: () => Nav.toOrders(context),
                  ),
                  _MenuItem(
                    icon: Icons.favorite_rounded,
                    label: 'Wishlist',
                    onTap: () => Nav.toWishlist(context),
                  ),
                  _MenuItem(
                    icon: Icons.shopping_cart_rounded,
                    label: 'My Cart',
                    onTap: () => _goToCart(context),
                  ),
                ]),
                const SizedBox(height: 12),
                _menuSection('Account', [
                  _MenuItem(
                    icon: Icons.account_balance_wallet_rounded,
                    label: 'Wallet & Referrals',
                    onTap: () => Nav.toWallet(context),
                  ),
                  _MenuItem(
                    icon: Icons.location_on_rounded,
                    label: 'Addresses',
                    onTap: () => Nav.toAddresses(context),
                  ),
                  _MenuItem(
                    icon: Icons.support_agent_rounded,
                    label: 'AI Assistant',
                    onTap: () => Nav.toAiChat(context),
                  ),
                  _MenuItem(
                    icon: Icons.info_outline_rounded,
                    label: 'About',
                    onTap: () => Nav.toAbout(context),
                  ),
                ]),
                const SizedBox(height: 16),
                // logout
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: OutlinedButton.icon(
                    onPressed: () => _confirmLogout(context),
                    icon: const Icon(Icons.logout_rounded,
                        color: AppTheme.danger, size: 20),
                    label: const Text('Log Out',
                        style: TextStyle(color: AppTheme.danger)),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size.fromHeight(48),
                      side: const BorderSide(color: AppTheme.danger),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  'CityMarket v1.0.0',
                  style: TextStyle(
                      color: Colors.grey.shade400, fontSize: 12),
                ),
                const SizedBox(height: 16),
              ],
            ),
          ),
        ],
      );
    });
  }

  void _goToCart(BuildContext context) {
    // Cart is a bottom-nav tab — pop to root.
    Navigator.of(context).popUntil((r) => r.isFirst);
  }

  void _confirmLogout(BuildContext context) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Log Out'),
        content: const Text('Are you sure you want to log out?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: AppTheme.danger),
            child: const Text('Log Out'),
          ),
        ],
      ),
    );
    if (ok == true && context.mounted) {
      await context.read<AppProvider>().logout();
    }
  }

  // ── Guest ──────────────────────────────────────────────────────────

  Widget _guest(BuildContext context) {
    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(20, 32, 20, 36),
          decoration: const BoxDecoration(
              gradient: AppTheme.headerGradient,
              borderRadius:
                  BorderRadius.vertical(bottom: Radius.circular(28))),
          child: Column(
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: const BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.person_outline_rounded,
                    color: AppTheme.primary, size: 44),
              ),
              const SizedBox(height: 16),
              const Text(
                'Welcome to CityMarket',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 6),
              Text(
                'Sign in for a personalised shopping experience',
                textAlign: TextAlign.center,
                style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.9), fontSize: 13),
              ),
              const SizedBox(height: 20),
              GradientButton(
                label: 'Sign In',
                icon: Icons.login_rounded,
                onPressed: () => Navigator.push(context,
                    MaterialPageRoute(builder: (_) => const LoginScreen())),
              ),
              const SizedBox(height: 10),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              _menuSection('Explore', [
                _MenuItem(
                  icon: Icons.support_agent_rounded,
                  label: 'AI Assistant',
                  onTap: () => Nav.toAiChat(context),
                ),
                _MenuItem(
                  icon: Icons.auto_awesome_rounded,
                  label: 'AI Search',
                  onTap: () => Nav.toAiSearch(context),
                ),
                _MenuItem(
                  icon: Icons.info_outline_rounded,
                  label: 'About',
                  onTap: () => Nav.toAbout(context),
                ),
              ]),
            ],
          ),
        ),
      ],
    );
  }

  // ── helpers ────────────────────────────────────────────────────────

  Widget _menuSection(String title, List<_MenuItem> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 8),
          child: Text(title,
              style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textSecondary)),
        ),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: [
              for (int i = 0; i < items.length; i++) ...[
                items[i],
                if (i < items.length - 1)
                  const Divider(height: 1, indent: 56),
              ],
            ],
          ),
        ),
      ],
    );
  }

  String _initials(User? u) {
    if (u == null || u.name.isEmpty) return '?';
    final parts = u.name.split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return parts[0][0].toUpperCase();
  }
}

class _MenuItem extends StatelessWidget {
  const _MenuItem(
      {required this.icon, required this.label, required this.onTap});
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      onTap: onTap,
      leading: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: AppTheme.primaryLight.withValues(alpha: 0.4),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, color: AppTheme.primary, size: 20),
      ),
      title: Text(label, style: const TextStyle(fontSize: 15)),
      trailing: const Icon(Icons.chevron_right_rounded,
          color: AppTheme.textHint, size: 22),
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.zero),
    );
  }
}
