import 'package:flutter/material.dart';

import '../screens/account/account_screen.dart';
import '../screens/account/addresses_screen.dart';
import '../screens/account/edit_profile_screen.dart';
import '../screens/account/wallet_screen.dart';
import '../screens/account/about_screen.dart';
import '../screens/account/notifications_screen.dart';
import '../screens/auth/forgot_password_screen.dart';
import '../screens/auth/reset_password_screen.dart';
import '../screens/ai/ai_chat_screen.dart';
import '../screens/ai/ai_search_screen.dart';
import '../screens/cart/checkout_screen.dart';
import '../screens/cart/order_placed_screen.dart';
import '../screens/catalog/category_products_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/order/order_detail_screen.dart';
import '../screens/order/orders_screen.dart';
import '../screens/product/product_detail_screen.dart';
import '../screens/product/reviews_screen.dart';
import '../screens/wishlist/wishlist_screen.dart';

/// Centralized named-route helpers so screens don't import each other.
/// All push methods accept the destination's required args explicitly.
class Nav {
  Nav._();

  static Future<void> toProduct(BuildContext c, String slug) =>
      Navigator.push(
        c,
        MaterialPageRoute(
          builder: (_) => ProductDetailScreen(slug: slug),
        ),
      );

  static Future<void> toCategory(BuildContext c, String slug,
          {String? name}) =>
      Navigator.push(
        c,
        MaterialPageRoute(
          builder: (_) => CategoryProductsScreen(
            categorySlug: slug,
            title: name ?? 'Category',
          ),
        ),
      );

  static Future<void> toCheckout(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const CheckoutScreen()),
      );

  static Future<void> toOrderPlaced(BuildContext c, String orderNumber) =>
      Navigator.push(
        c,
        MaterialPageRoute(
          builder: (_) => OrderPlacedScreen(orderNumber: orderNumber),
          fullscreenDialog: true,
        ),
      );

  static Future<void> toOrderDetail(BuildContext c, String orderNumber) =>
      Navigator.push(
        c,
        MaterialPageRoute(
          builder: (_) => OrderDetailScreen(orderNumber: orderNumber),
        ),
      );

  static Future<void> toOrders(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const OrdersScreen()),
      );

  static Future<void> toReviews(BuildContext c, String slug,
          {String? productName}) =>
      Navigator.push(
        c,
        MaterialPageRoute(
          builder: (_) => ReviewsScreen(slug: slug, productName: productName),
        ),
      );

  static Future<void> toWishlist(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const WishlistScreen()),
      );

  static Future<void> toWallet(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const WalletScreen()),
      );

  static Future<void> toAddresses(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const AddressesScreen()),
      );

  static Future<void> toEditProfile(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const EditProfileScreen()),
      );

  static Future<void> toAbout(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const AboutScreen()),
      );

  static Future<void> toNotifications(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const NotificationsScreen()),
      );

  static Future<void> toForgotPassword(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const ForgotPasswordScreen()),
      );

  static Future<void> toResetPassword(BuildContext c,
          {String email = '', String token = ''}) =>
      Navigator.push(
        c,
        MaterialPageRoute(
          builder: (_) => ResetPasswordScreen(email: email, token: token),
        ),
      );

  static Future<void> toAiSearch(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const AiSearchScreen()),
      );

  static Future<void> toAiChat(BuildContext c) => Navigator.push(
        c,
        MaterialPageRoute(builder: (_) => const AiChatScreen()),
      );

  /// Jump to the Home tab (index 0) — pops back to root.
  static void toHome(BuildContext c) {
    Navigator.of(c).popUntil((route) => route.isFirst);
  }
}
