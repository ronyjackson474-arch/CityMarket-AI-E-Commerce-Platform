/// CityMarket cart, order, address & review models — match real API shapes.
///
/// Verified shapes:
/// - Cart GET: `{"data":{"id":3,"user_id":2,"items":[{"id","cart_id","product_id",
///   "quantity","product":{...,"images":[...]}}]}, "summary":{"item_count","subtotal","shipping","total"}}`
/// - Cart add/update: `{"message": "...", "data": {...cart...}, "summary": {...}}`
/// - Orders: paginated list of order objects.

class Cart {
  final int id;
  final int? userId;
  final List<CartItem> items;
  final CartSummary summary;

  const Cart({
    required this.id,
    this.userId,
    this.items = const [],
    this.summary = const CartSummary(),
  });

  int get itemCount => summary.itemCount > 0
      ? summary.itemCount
      : items.fold(0, (s, i) => s + i.quantity);

  double get subtotal => summary.subtotal > 0
      ? summary.subtotal
      : items.fold(0.0, (s, i) => s + i.lineTotal);

  /// Parses `{"data": {...cart...}, "summary": {...}}` (GET /cart)
  /// OR `{"message": "...", "data": {...cart...}, "summary": {...}}` (mutations).
  factory Cart.fromJson(Map<String, dynamic> json) {
    final data = (json['data'] as Map<String, dynamic>?) ?? json;
    final summaryJson =
        (json['summary'] as Map<String, dynamic>?) ?? const <String, dynamic>{};
    return Cart(
      id: _asInt(data['id']),
      userId: data['user_id'] == null ? null : _asInt(data['user_id']),
      items: (data['items'] as List<dynamic>?)
              ?.map((e) => CartItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      summary: CartSummary.fromJson(summaryJson),
    );
  }

  static Cart empty() => const Cart(id: 0);
}

class CartSummary {
  final int itemCount;
  final double subtotal;
  final double discount;
  final double shipping;
  final double total;

  const CartSummary({
    this.itemCount = 0,
    this.subtotal = 0,
    this.discount = 0,
    this.shipping = 0,
    this.total = 0,
  });

  factory CartSummary.fromJson(Map<String, dynamic> json) {
    return CartSummary(
      itemCount: _asInt(json['item_count']),
      subtotal: _asDouble(json['subtotal']),
      discount: _asDouble(json['discount']),
      shipping: _asDouble(json['shipping']),
      total: _asDouble(json['total']),
    );
  }
}

class CartItem {
  final int id;
  final int cartId;
  final int productId;
  final int quantity;
  final Product product;

  const CartItem({
    required this.id,
    required this.cartId,
    required this.productId,
    required this.quantity,
    required this.product,
  });

  /// In cart payloads, the product's price is its **base** price; we compute
  /// effective price via the same sale-price rule as the Product model.
  double get effectivePrice => product.effectivePrice;

  double get lineTotal => effectivePrice * quantity;

  factory CartItem.fromJson(Map<String, dynamic> json) {
    final productJson =
        (json['product'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    return CartItem(
      id: _asInt(json['id']),
      cartId: _asInt(json['cart_id']),
      productId: _asInt(json['product_id']),
      quantity: _asInt(json['quantity']),
      product: Product.fromJson(productJson),
    );
  }
}

// ── Orders ───────────────────────────────────────────────────────────

class Order {
  final int id;
  final String orderNumber;
  final String status;
  final double subtotal;
  final double discount;
  final double shipping;
  final double total;
  final String paymentMethod;
  final String paymentStatus;
  final List<OrderItem> items;
  final DateTime createdAt;

  const Order({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.subtotal,
    required this.discount,
    required this.shipping,
    required this.total,
    required this.paymentMethod,
    required this.paymentStatus,
    this.items = const [],
    required this.createdAt,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: _asInt(json['id']),
      orderNumber: json['order_number'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      subtotal: _asDouble(json['subtotal']),
      discount: _asDouble(json['discount']),
      shipping: _asDouble(json['shipping']),
      total: _asDouble(json['total']),
      paymentMethod: json['payment_method'] as String? ?? 'cod',
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      items: (json['items'] as List<dynamic>?)
              ?.map((e) => OrderItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ??
          DateTime.now(),
    );
  }
}

class OrderItem {
  final int id;
  final int productId;
  final String productName;
  final double price;
  final int quantity;
  final String? productImage;
  final String? productSlug;

  const OrderItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.price,
    required this.quantity,
    this.productImage,
    this.productSlug,
  });

  double get lineTotal => price * quantity;

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: _asInt(json['id']),
      productId: _asInt(json['product_id']),
      productName: json['product_name'] as String? ??
          (json['product']?['name'] as String?) ??
          '',
      price: _asDouble(json['price']),
      quantity: _asInt(json['quantity']),
      productImage: (json['product']?['images']?[0]?['image_path']) as String?,
      productSlug: (json['product']?['slug']) as String?,
    );
  }
}

// ── Addresses ────────────────────────────────────────────────────────

class Address {
  final int id;
  final String name;
  final String phone;
  final String addressLine1;
  final String? addressLine2;
  final String city;
  final String state;
  final String pincode;
  final bool isDefault;

  const Address({
    required this.id,
    required this.name,
    required this.phone,
    required this.addressLine1,
    this.addressLine2,
    required this.city,
    required this.state,
    required this.pincode,
    this.isDefault = false,
  });

  String get fullAddress =>
      [addressLine1, addressLine2, city, state, pincode]
          .whereType<String>()
          .where((s) => s.isNotEmpty)
          .join(', ');

  factory Address.fromJson(Map<String, dynamic> json) {
    return Address(
      id: _asInt(json['id']),
      name: json['name'] as String? ?? '',
      phone: json['phone'] as String? ?? '',
      addressLine1: json['address_line1'] as String? ?? '',
      addressLine2: json['address_line2'] as String?,
      city: json['city'] as String? ?? '',
      state: json['state'] as String? ?? '',
      pincode: json['pincode'] as String? ?? '',
      isDefault: json['is_default'] as bool? ?? false,
    );
  }

  Map<String, dynamic> toJson() => {
        'name': name,
        'phone': phone,
        'address_line1': addressLine1,
        if (addressLine2 != null) 'address_line2': addressLine2,
        'city': city,
        'state': state,
        'pincode': pincode,
        'is_default': isDefault,
      };

  Address copyWith({
    int? id,
    String? name,
    String? phone,
    String? addressLine1,
    String? addressLine2,
    String? city,
    String? state,
    String? pincode,
    bool? isDefault,
  }) =>
      Address(
        id: id ?? this.id,
        name: name ?? this.name,
        phone: phone ?? this.phone,
        addressLine1: addressLine1 ?? this.addressLine1,
        addressLine2: addressLine2 ?? this.addressLine2,
        city: city ?? this.city,
        state: state ?? this.state,
        pincode: pincode ?? this.pincode,
        isDefault: isDefault ?? this.isDefault,
      );
}

// ── Reviews ──────────────────────────────────────────────────────────

class Review {
  final int id;
  final int rating;
  final String? title;
  final String? body;
  final bool isVerifiedPurchase;
  final String? userName;
  final DateTime createdAt;

  const Review({
    required this.id,
    required this.rating,
    this.title,
    this.body,
    this.isVerifiedPurchase = false,
    this.userName,
    required this.createdAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: _asInt(json['id']),
      rating: _asInt(json['rating']),
      title: json['title'] as String?,
      body: json['body'] as String?,
      isVerifiedPurchase: json['is_verified_purchase'] as bool? ?? false,
      userName: (json['user'] is Map)
          ? (json['user']['name'] as String?)
          : (json['user_name'] as String?),
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ??
          DateTime.now(),
    );
  }
}

// ── shared helpers ───────────────────────────────────────────────────
int _asInt(dynamic v) {
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v) ?? 0;
  return 0;
}

double _asDouble(dynamic v) {
  if (v is double) return v;
  if (v is num) return v.toDouble();
  if (v is String) return double.tryParse(v) ?? 0;
  return 0;
}
