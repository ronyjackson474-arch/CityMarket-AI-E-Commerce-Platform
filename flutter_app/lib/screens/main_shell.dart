import 'package:flutter/material.dart';
import 'package:badges/badges.dart' as badges;
import 'package:provider/provider.dart';

import '../providers/app_provider.dart';
import '../theme/app_theme.dart';
import 'account/account_screen.dart';
import 'cart/cart_screen.dart';
import 'catalog/categories_screen.dart';
import 'home/home_screen.dart';
import 'wishlist/wishlist_screen.dart';

/// Root app shell with bottom navigation. Hosts the five top-level tabs.
class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _index = 0;

  late final List<Widget> _tabs = [
    const HomeScreen(),
    const CategoriesScreen(),
    const CartScreen(),
    const WishlistScreen(),
    const AccountScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppProvider>();
    return Scaffold(
      body: IndexedStack(
        index: _index,
        children: _tabs,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 16,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: SafeArea(
          child: Padding(
            padding:
                const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
            child: BottomNavigationBar(
              currentIndex: _index,
              onTap: (i) => setState(() => _index = i),
              type: BottomNavigationBarType.fixed,
              selectedFontSize: 11,
              unselectedFontSize: 11,
              selectedItemColor: AppTheme.primary,
              unselectedItemColor: AppTheme.textSecondary,
              showUnselectedLabels: true,
              backgroundColor: Colors.transparent,
              elevation: 0,
              items: [
                const BottomNavigationBarItem(
                  icon: Icon(Icons.home_outlined),
                  activeIcon: Icon(Icons.home_rounded),
                  label: 'Home',
                ),
                const BottomNavigationBarItem(
                  icon: Icon(Icons.grid_view_outlined),
                  activeIcon: Icon(Icons.grid_view_rounded),
                  label: 'Categories',
                ),
                BottomNavigationBarItem(
                  icon: _badge(
                    app.cartCount,
                    const Icon(Icons.shopping_cart_outlined),
                  ),
                  activeIcon: _badge(
                    app.cartCount,
                    const Icon(Icons.shopping_cart_rounded),
                    active: true,
                  ),
                  label: 'Cart',
                ),
                BottomNavigationBarItem(
                  icon: _badge(
                    app.wishlistCount,
                    const Icon(Icons.favorite_outline_rounded),
                  ),
                  activeIcon: _badge(
                    app.wishlistCount,
                    const Icon(Icons.favorite_rounded),
                    active: true,
                  ),
                  label: 'Wishlist',
                ),
                const BottomNavigationBarItem(
                  icon: Icon(Icons.person_outline_rounded),
                  activeIcon: Icon(Icons.person_rounded),
                  label: 'Account',
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _badge(int count, Widget icon, {bool active = false}) {
    if (count <= 0) return icon;
    return badges.Badge(
      position: badges.BadgePosition.topEnd(top: -6, end: -8),
      badgeStyle: badges.BadgeStyle(
        badgeColor: active ? AppTheme.primary : AppTheme.danger,
        elevation: 0,
      ),
      badgeContent: Text(
        '$count',
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
      child: icon,
    );
  }
}
