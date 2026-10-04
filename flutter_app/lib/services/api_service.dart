/// CityMarket API service — HTTP layer for the Laravel REST backend.
///
/// All endpoints auto-unwrap the standard `{"data": ...}` envelope (or
/// `{"message": ..., "data": ...}` for mutations) and throw typed
/// [ApiException] / [ValidationException] for non-2xx responses.
/// The bearer token is stored in-memory and mirrored to SharedPreferences
/// by the caller (StorageService).
library;

import 'dart:convert';
import 'package:http/http.dart' as http;

import '../constants/app_constants.dart';

class ApiService {
  ApiService();

  String? _token;

  /// Current bearer token (null when logged out).
  String? get token => _token;

  bool get isAuthenticated => _token != null && _token!.isNotEmpty;

  void setToken(String? token) => _token = token;

  void clearToken() => _token = null;

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_token != null && _token!.isNotEmpty)
          'Authorization': 'Bearer $_token',
      };

  Uri _uri(String endpoint, [Map<String, String>? qp]) {
    final base = '${AppConstants.apiBaseUrl}$endpoint';
    final parsed = Uri.parse(base);
    if (qp == null || qp.isEmpty) return parsed;
    // Merge any existing query params in the endpoint path.
    return parsed.replace(queryParameters: {
      ...parsed.queryParameters,
      ...qp.map((k, v) => MapEntry(k, v)),
    });
  }

  /// Core request method. Returns the parsed JSON body.
  Future<Map<String, dynamic>> _request(
    String method,
    String endpoint, {
    Map<String, dynamic>? body,
    Map<String, String>? queryParams,
  }) async {
    final req = http.Request(method, _uri(endpoint, queryParams))
      ..headers.addAll(_headers);
    if (body != null) {
      req.body = jsonEncode(body);
    }

    http.StreamedResponse response;
    try {
      response = await http.Client().send(req);
    } catch (e) {
      throw ApiException('Network error: ${e.toString()}', 0);
    }

    final responseBody = await response.stream.bytesToString();

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (responseBody.isEmpty) return {};
      return jsonDecode(responseBody) as Map<String, dynamic>;
    }

    // Error handling — try to surface the server's message.
    String serverMessage = 'Request failed (${response.statusCode})';
    Map<String, dynamic>? errors;
    try {
      if (responseBody.isNotEmpty) {
        final decoded = jsonDecode(responseBody);
        if (decoded is Map<String, dynamic>) {
          serverMessage = decoded['message'] as String? ?? serverMessage;
          errors = decoded['errors'] as Map<String, dynamic>?;
        }
      }
    } catch (_) {}

    if (response.statusCode == 401) {
      throw ApiException(serverMessage, 401);
    } else if (response.statusCode == 422) {
      throw ValidationException(serverMessage, errors ?? {});
    } else if (response.statusCode == 404) {
      throw ApiException('Not found', 404);
    } else {
      throw ApiException(serverMessage, response.statusCode);
    }
  }

  // ── Auth ──────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> login(String email, String password) async {
    final res = await _request('POST', '/auth/login', body: {
      'email': email,
      'password': password,
    });
    final data = res['data'] as Map<String, dynamic>? ?? res;
    final token = data['token'] as String?;
    if (token != null) _token = token;
    return res;
  }

  Future<Map<String, dynamic>> register(
    String name,
    String email,
    String password, {
    String? phone,
  }) async {
    final res = await _request('POST', '/auth/register', body: {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': password,
      if (phone != null && phone.isNotEmpty) 'phone': phone,
    });
    final data = res['data'] as Map<String, dynamic>? ?? res;
    final token = data['token'] as String?;
    if (token != null) _token = token;
    return res;
  }

  Future<void> logout() async {
    try {
      await _request('POST', '/auth/logout');
    } finally {
      _token = null;
    }
  }

  Future<Map<String, dynamic>> me() => _request('GET', '/auth/me');

  /// Update the authenticated user's profile (name, phone). Hits the
  /// Sanctum profile-update endpoint.
  Future<Map<String, dynamic>> updateProfile(
          {String? name, String? phone}) =>
      _request('PUT', '/auth/profile', body: {
        if (name != null) 'name': name,
        if (phone != null) 'phone': phone,
      });

  // ── Categories ────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getCategories() => _request('GET', '/categories');

  // ── Products ──────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getProducts({
    int? category,
    String? categorySlug,
    int? brand,
    double? priceMin,
    double? priceMax,
    double? minRating,
    String? search,
    String? sort,
    bool? onSale,
    int perPage = 20,
    int page = 1,
  }) {
    final params = <String, String>{
      'per_page': perPage.toString(),
      'page': page.toString(),
    };
    if (category != null) params['category'] = category.toString();
    if (categorySlug != null && categorySlug.isNotEmpty) {
      params['category_slug'] = categorySlug;
    }
    if (brand != null) params['brand'] = brand.toString();
    if (priceMin != null) params['price_min'] = priceMin.toString();
    if (priceMax != null) params['price_max'] = priceMax.toString();
    if (minRating != null) params['min_rating'] = minRating.toString();
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (sort != null && sort.isNotEmpty) params['sort'] = sort;
    if (onSale != null && onSale) params['on_sale'] = '1';
    return _request('GET', '/products', queryParams: params);
  }

  Future<Map<String, dynamic>> getFeaturedProducts() =>
      _request('GET', '/products/featured');

  Future<Map<String, dynamic>> getFlashDeals() =>
      _request('GET', '/products/flash-deals');

  Future<Map<String, dynamic>> getProduct(String slug) =>
      _request('GET', '/products/$slug');

  Future<Map<String, dynamic>> getRecommendations({int limit = 10}) =>
      _request('GET', '/recommendations',
          queryParams: {'limit': limit.toString()});

  // ── Cart ──────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getCart() => _request('GET', '/cart');

  Future<Map<String, dynamic>> addToCart(int productId, {int quantity = 1}) =>
      _request('POST', '/cart/add', body: {
        'product_id': productId,
        'quantity': quantity,
      });

  Future<Map<String, dynamic>> updateCartItem(int cartItemId, int quantity) =>
      _request('POST', '/cart/update', body: {
        'cart_item_id': cartItemId,
        'quantity': quantity,
      });

  Future<Map<String, dynamic>> removeFromCart(int cartItemId) =>
      _request('POST', '/cart/remove', body: {'cart_item_id': cartItemId});

  Future<void> clearCart() => _request('POST', '/cart/clear');

  // ── Orders ────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getOrders({int page = 1}) =>
      _request('GET', '/orders', queryParams: {'page': page.toString()});

  Future<Map<String, dynamic>> getOrder(String orderNumber) =>
      _request('GET', '/orders/$orderNumber');

  Future<Map<String, dynamic>> createOrder({
    required Map<String, dynamic> shippingAddress,
    required String paymentMethod,
    String? couponCode,
  }) =>
      _request('POST', '/orders', body: {
        'shipping_address': shippingAddress,
        'payment_method': paymentMethod,
        if (couponCode != null && couponCode.isNotEmpty)
          'coupon_code': couponCode,
      });

  Future<void> cancelOrder(String orderNumber) =>
      _request('POST', '/orders/$orderNumber/cancel');

  Future<void> requestReturn(String orderNumber, String reason,
          {String? description}) =>
      _request('POST', '/orders/$orderNumber/return',
          body: {'reason': reason, if (description != null) 'description': description});

  // ── Wishlist ──────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getWishlist({int page = 1}) =>
      _request('GET', '/wishlist', queryParams: {'page': page.toString()});

  Future<Map<String, dynamic>> addToWishlist(int productId) =>
      _request('POST', '/wishlist/add', body: {'product_id': productId});

  Future<Map<String, dynamic>> removeFromWishlist(int productId) =>
      _request('POST', '/wishlist/remove', body: {'product_id': productId});

  // ── Reviews ───────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getProductReviews(String slug,
          {int page = 1}) =>
      _request('GET', '/products/$slug/reviews',
          queryParams: {'page': page.toString()});

  Future<Map<String, dynamic>> postReview(String slug, int rating,
          {String? title, String? body}) =>
      _request('POST', '/products/$slug/reviews', body: {
        'rating': rating,
        if (title != null) 'title': title,
        if (body != null) 'body': body,
      });

  // ── Addresses ─────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getAddresses() => _request('GET', '/addresses');

  Future<Map<String, dynamic>> addAddress(Map<String, dynamic> address) =>
      _request('POST', '/addresses', body: address);

  Future<Map<String, dynamic>> updateAddress(
          int id, Map<String, dynamic> address) =>
      _request('PUT', '/addresses/$id', body: address);

  Future<void> deleteAddress(int id) => _request('DELETE', '/addresses/$id');

  // ── Wallet ────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getWallet() => _request('GET', '/wallet');

  Future<Map<String, dynamic>> getWalletTransactions({int page = 1}) =>
      _request('GET', '/wallet/transactions',
          queryParams: {'page': page.toString()});

  Future<Map<String, dynamic>> applyReferral(String code) =>
      _request('POST', '/referrals/apply', body: {'referral_code': code});

  // ── AI ────────────────────────────────────────────────────────────

  /// Public (no auth) natural-language search.
  Future<Map<String, dynamic>> aiSearch(String query) =>
      _request('POST', '/ai/search', body: {'query': query});

  Future<Map<String, dynamic>> chatSend(String message,
          {int? conversationId}) =>
      _request('POST', '/chat/send', body: {
        'message': message,
        if (conversationId != null) 'conversation_id': conversationId,
      });

  Future<Map<String, dynamic>> getConversations() =>
      _request('GET', '/chat/conversations');

  Future<Map<String, dynamic>> getConversationMessages(int conversationId) =>
      _request('GET', '/chat/conversations/$conversationId/messages');

  // ── Related products / cart count / coupons ─────────────────────────

  Future<Map<String, dynamic>> getRelatedProducts(String slug) =>
      _request('GET', '/products/$slug/related');

  Future<Map<String, dynamic>> getCartCount() => _request('GET', '/cart/count');

  Future<Map<String, dynamic>> validateCoupon(String code,
          {double? subtotal}) =>
      _request('POST', '/coupons/validate', body: {
        'code': code,
        if (subtotal != null) 'subtotal': subtotal,
      });

  // ── Notifications ───────────────────────────────────────────────────

  Future<Map<String, dynamic>> getNotifications() =>
      _request('GET', '/notifications');

  Future<void> markNotificationRead(int id) =>
      _request('POST', '/notifications/$id/read');

  Future<void> markAllNotificationsRead() =>
      _request('POST', '/notifications/read-all');

  // ── Account security ────────────────────────────────────────────────

  Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) =>
      _request('POST', '/account/change-password', body: {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': newPasswordConfirmation,
      });

  // ── Auth recovery ───────────────────────────────────────────────────

  Future<Map<String, dynamic>> forgotPassword(String email) =>
      _request('POST', '/auth/forgot-password', body: {'email': email});

  Future<Map<String, dynamic>> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) =>
      _request('POST', '/auth/reset-password', body: {
        'email': email,
        'token': token,
        'password': password,
        'password_confirmation': passwordConfirmation,
      });
}

// ── Exceptions ───────────────────────────────────────────────────────

class ApiException implements Exception {
  final String message;
  final int statusCode;
  const ApiException(this.message, this.statusCode);
  @override
  String toString() => message;
}

class ValidationException implements Exception {
  final String message;
  final Map<String, dynamic> errors;
  const ValidationException(this.message, this.errors);

  /// Flatten validation errors into a single human-readable string.
  String get flatErrors {
    if (errors.isEmpty) return message;
    return errors.values
        .expand((e) => e is List ? e : [e])
        .map((e) => e.toString())
        .join('\n');
  }

  @override
  String toString() => flatErrors;
}
