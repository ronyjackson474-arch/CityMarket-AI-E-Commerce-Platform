import 'package:flutter/foundation.dart';

import '../models/order.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';

/// App-wide state: authentication + cart/wishlist badge counts + cache of the
/// logged-in user profile.
///
/// Screens fetch their own list data via FutureBuilder, but global things that
/// several screens need at once (auth state, cart count, wishlist count) live
/// here so the UI stays in sync.
class AppProvider extends ChangeNotifier {
  AppProvider({ApiService? api}) : _api = api ?? ApiService();

  final ApiService _api;

  ApiService get api => _api;

  // ── Auth state ────────────────────────────────────────────────────
  User? _user;
  bool _isLoggedIn = false;
  bool _initialized = false;

  User? get user => _user;
  bool get isLoggedIn => _isLoggedIn;
  bool get initialized => _initialized;

  // ── Badge counts ──────────────────────────────────────────────────
  int _cartCount = 0;
  int _wishlistCount = 0;

  int get cartCount => _cartCount;
  int get wishlistCount => _wishlistCount;

  // ── Boot ──────────────────────────────────────────────────────────

  /// Restore the session from SharedPreferences if a token exists.
  Future<void> init() async {
    final token = await StorageService.getToken();
    if (token != null && token.isNotEmpty) {
      _api.setToken(token);
      try {
        final res = await _api.me();
        final data = res['data'] as Map<String, dynamic>?;
        if (data != null) {
          _user = User.fromJson(data);
          _isLoggedIn = true;
          await _refreshCounts();
        }
      } catch (_) {
        // Token expired / invalid — clear it.
        await StorageService.clearAll();
        _api.clearToken();
      }
    }
    _initialized = true;
    notifyListeners();
  }

  // ── Auth actions ──────────────────────────────────────────────────

  Future<void> login(String email, String password) async {
    final res = await _api.login(email, password);
    final ar = AuthResponse.fromJson(res);
    await StorageService.saveToken(ar.token);
    await StorageService.saveProfile(name: ar.user.name, email: ar.user.email);
    _user = ar.user;
    _isLoggedIn = true;
    await _refreshCounts();
    notifyListeners();
  }

  Future<void> register(String name, String email, String password,
      {String? phone}) async {
    final res =
        await _api.register(name, email, password, phone: phone);
    final ar = AuthResponse.fromJson(res);
    await StorageService.saveToken(ar.token);
    await StorageService.saveProfile(name: ar.user.name, email: ar.user.email);
    _user = ar.user;
    _isLoggedIn = true;
    await _refreshCounts();
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await _api.logout();
    } catch (_) {
      // Even if the server call fails we still clear local state.
    }
    await StorageService.clearAll();
    _api.clearToken();
    _user = null;
    _isLoggedIn = false;
    _cartCount = 0;
    _wishlistCount = 0;
    notifyListeners();
  }

  /// Refresh the cached user profile.
  Future<void> refreshUser() async {
    if (!_isLoggedIn) return;
    try {
      final res = await _api.me();
      final data = res['data'] as Map<String, dynamic>?;
      if (data != null) {
        _user = User.fromJson(data);
        await StorageService.saveProfile(
            name: _user!.name, email: _user!.email);
        notifyListeners();
      }
    } catch (_) {}
  }

  // ── Cart / wishlist counts ────────────────────────────────────────

  Future<void> refreshCartCount() async {
    if (!_isLoggedIn) return;
    try {
      final res = await _api.getCart();
      final cart = Cart.fromJson(res);
      _cartCount = cart.itemCount;
      notifyListeners();
    } catch (_) {}
  }

  Future<void> refreshWishlistCount() async {
    if (!_isLoggedIn) return;
    try {
      final res = await _api.getWishlist();
      final list = res['data'];
      if (list is List) {
        _wishlistCount = list.length;
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> _refreshCounts() async {
    await Future.wait([refreshCartCount(), refreshWishlistCount()]);
  }
}
