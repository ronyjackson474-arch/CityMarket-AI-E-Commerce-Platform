/// CityMarket API Service
/// Handles all REST API communication with the Laravel backend
///
/// Usage:
/// ```dart
/// final api = ApiService();
/// await api.login('user@example.com', 'password');
/// final products = await api.getProducts();
/// ```

import 'dart:convert';
import 'package:http/http.dart as http;

class ApiService {
  // Configure this in production
  static const String baseUrl = 'https://citymarket-ai-e-comm-2sb7p3.drytis.dev/api';

  String? _token;

  void setToken(String token) {
    _token = token;
  }

  String? get token => _token;

  bool get isAuthenticated => _token != null;

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Future<Map<String, dynamic>> _request(
    String method,
    String endpoint, {
    Map<String, dynamic>? body,
    Map<String, String>? queryParams,
  }) async {
    final uri = Uri.parse('$baseUrl$endpoint')
        .replace(queryParameters: queryParams);

    final response = await http.send(http.Request(method, uri)
      ..headers.addAll(_headers)
      ..body = body != null ? jsonEncode(body) : '');

    final responseBody = await response.stream.bytesToString();

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonDecode(responseBody) as Map<String, dynamic>;
    } else if (response.statusCode == 401) {
      throw ApiException('Unauthorized', 401);
    } else if (response.statusCode == 422) {
      final errors = jsonDecode(responseBody);
      throw ValidationException(errors['message'] ?? 'Validation error', errors['errors'] ?? {});
    } else {
      throw ApiException('Request failed: ${response.statusCode}', response.statusCode);
    }
  }

  // ===== Auth =====

  Future<Map<String, dynamic>> login(String email, String password) async {
    final result = await _request('POST', '/auth/login', body: {
      'email': email,
      'password': password,
    });
    _token = result['token'] as String;
    return result;
  }

  Future<Map<String, dynamic>> register(
    String name,
    String email,
    String password, {
    String? phone,
  }) async {
    final result = await _request('POST', '/auth/register', body: {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': password,
      if (phone != null) 'phone': phone,
    });
    _token = result['token'] as String;
    return result;
  }

  Future<void> logout() async {
    await _request('POST', '/auth/logout');
    _token = null;
  }

  Future<Map<String, dynamic>> me() => _request('GET', '/auth/me');

  // ===== Categories =====

  Future<Map<String, dynamic>> getCategories() => _request('GET', '/categories');

  // ===== Products =====

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
    if (categorySlug != null) params['category_slug'] = categorySlug;
    if (brand != null) params['brand'] = brand.toString();
    if (priceMin != null) params['price_min'] = priceMin.toString();
    if (priceMax != null) params['price_max'] = priceMax.toString();
    if (minRating != null) params['min_rating'] = minRating.toString();
    if (search != null) params['search'] = search;
    if (sort != null) params['sort'] = sort;
    if (onSale != null && onSale) params['on_sale'] = '1';

    return _request('GET', '/products', queryParams: params);
  }

  Future<Map<String, dynamic>> getFeaturedProducts() =>
      _request('GET', '/products/featured');

  Future<Map<String, dynamic>> getFlashDeals() =>
      _request('GET', '/products/flash-deals');

  Future<Map<String, dynamic>> getProduct(String slug) =>
      _request('GET', '/products/$slug');

  // ===== Cart =====

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

  // ===== Orders =====

  Future<Map<String, dynamic>> getOrders() => _request('GET', '/orders');

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
        if (couponCode != null) 'coupon_code': couponCode,
      });

  Future<Map<String, dynamic>> cancelOrder(String orderNumber) =>
      _request('POST', '/orders/$orderNumber/cancel');

  Future<Map<String, dynamic>> requestReturn(
          String orderNumber, String reason, {String? description}) =>
      _request('POST', '/orders/$orderNumber/return', body: {
        'reason': reason,
        if (description != null) 'description': description,
      });

  // ===== Wishlist =====

  Future<Map<String, dynamic>> getWishlist() => _request('GET', '/wishlist');

  Future<Map<String, dynamic>> addToWishlist(int productId) =>
      _request('POST', '/wishlist/add', body: {'product_id': productId});

  Future<Map<String, dynamic>> removeFromWishlist(int productId) =>
      _request('POST', '/wishlist/remove', body: {'product_id': productId});

  // ===== Reviews =====

  Future<Map<String, dynamic>> getProductReviews(String slug) =>
      _request('GET', '/products/$slug/reviews');

  Future<Map<String, dynamic>> postReview(
          String slug, int rating, {String? title, String? body}) =>
      _request('POST', '/products/$slug/reviews',
          body: {'rating': rating, if (title != null) 'title': title, if (body != null) 'body': body});

  // ===== Addresses =====

  Future<Map<String, dynamic>> getAddresses() => _request('GET', '/addresses');

  Future<Map<String, dynamic>> addAddress(Map<String, dynamic> address) =>
      _request('POST', '/addresses', body: address);

  Future<Map<String, dynamic>> updateAddress(int id, Map<String, dynamic> address) =>
      _request('PUT', '/addresses/$id', body: address);

  Future<void> deleteAddress(int id) => _request('DELETE', '/addresses/$id');

  // ===== Wallet =====

  Future<Map<String, dynamic>> getWallet() => _request('GET', '/wallet');

  Future<Map<String, dynamic>> getWalletTransactions() =>
      _request('GET', '/wallet/transactions');

  Future<Map<String, dynamic>> applyReferral(String code) =>
      _request('POST', '/referrals/apply', body: {'referral_code': code});

  // ===== AI Features =====

  Future<Map<String, dynamic>> aiSearch(String query) =>
      _request('POST', '/ai/search', body: {'query': query});

  Future<Map<String, dynamic>> chatSend(String message, {int? conversationId}) =>
      _request('POST', '/chat/send', body: {
        'message': message,
        if (conversationId != null) 'conversation_id': conversationId,
      });

  Future<Map<String, dynamic>> getConversations() =>
      _request('GET', '/chat/conversations');

  Future<Map<String, dynamic>> getRecommendations({int limit = 10}) =>
      _request('GET', '/recommendations', queryParams: {'limit': limit.toString()});
}

// ===== Exceptions =====

class ApiException implements Exception {
  final String message;
  final int statusCode;

  ApiException(this.message, this.statusCode);

  @override
  String toString() => message;
}

class ValidationException implements Exception {
  final String message;
  final Map<String, dynamic> errors;

  ValidationException(this.message, this.errors);

  @override
  String toString() => message;
}
