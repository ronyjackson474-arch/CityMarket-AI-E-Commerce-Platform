/// CityMarket Order & Cart Models

class Order {
  final int id;
  final String orderNumber;
  final String status; // pending, confirmed, packed, shipped, delivered, cancelled, returned
  final double subtotal;
  final double discount;
  final double shipping;
  final double total;
  final String paymentMethod;
  final String paymentStatus;
  final List<OrderItem> items;
  final DateTime createdAt;

  Order({
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
      id: json['id'] as int,
      orderNumber: json['order_number'] as String,
      status: json['status'] as String,
      subtotal: double.parse(json['subtotal'].toString()),
      discount: double.parse((json['discount'] ?? 0).toString()),
      shipping: double.parse((json['shipping'] ?? 0).toString()),
      total: double.parse(json['total'].toString()),
      paymentMethod: json['payment_method'] as String? ?? 'cod',
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      items: (json['items'] as List<dynamic>?)
              ?.map((e) => OrderItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

class OrderItem {
  final int id;
  final int productId;
  final String productName;
  final double price;
  final int quantity;

  OrderItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.price,
    required this.quantity,
  });

  double get lineTotal => price * quantity;

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'] as int,
      productId: json['product_id'] as int,
      productName: json['product_name'] as String,
      price: double.parse(json['price'].toString()),
      quantity: json['quantity'] as int,
    );
  }
}

class Cart {
  final int id;
  final List<CartItem> items;

  Cart({required this.id, this.items = const []});

  double get subtotal =>
      items.fold(0, (sum, item) => sum + item.effectivePrice * item.quantity);

  int get itemCount => items.fold(0, (sum, item) => sum + item.quantity);

  factory Cart.fromJson(Map<String, dynamic> json) {
    return Cart(
      id: json['id'] as int,
      items: (json['items'] as List<dynamic>?)
              ?.map((e) => CartItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class CartItem {
  final int id;
  final int productId;
  final String productName;
  final String? productSlug;
  final double price;
  final double? salePrice;
  final int quantity;
  final String? productImage;

  CartItem({
    required this.id,
    required this.productId,
    required this.productName,
    this.productSlug,
    required this.price,
    this.salePrice,
    required this.quantity,
    this.productImage,
  });

  double get effectivePrice =>
      (salePrice != null && salePrice! > 0 && salePrice! < price)
          ? salePrice!
          : price;

  double get lineTotal => effectivePrice * quantity;

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      id: json['id'] as int,
      productId: json['product_id'] as int,
      productName: json['product_name'] ?? json['product']?['name'] ?? '',
      productSlug: json['product']?['slug'],
      price: double.parse((json['price'] ?? json['product']?['price'] ?? 0).toString()),
      salePrice: json['sale_price'] != null
          ? double.parse(json['sale_price'].toString())
          : (json['product']?['sale_price'] != null
              ? double.parse(json['product']['sale_price'].toString())
              : null),
      quantity: json['quantity'] as int? ?? 1,
      productImage: json['product']?['images']?[0]?['image_path'],
    );
  }
}

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

  Address({
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

  String get fullAddress {
    final parts = [addressLine1, addressLine2, city, state, pincode]
        .whereType<String>()
        .where((s) => s.isNotEmpty);
    return parts.join(', ');
  }

  factory Address.fromJson(Map<String, dynamic> json) {
    return Address(
      id: json['id'] as int,
      name: json['name'] as String,
      phone: json['phone'] as String,
      addressLine1: json['address_line1'] as String,
      addressLine2: json['address_line2'] as String?,
      city: json['city'] as String,
      state: json['state'] as String,
      pincode: json['pincode'] as String,
      isDefault: json['is_default'] as bool? ?? false,
    );
  }

  Map<String, dynamic> toJson() => {
        'name': name,
        'phone': phone,
        'address_line1': addressLine1,
        'address_line2': addressLine2,
        'city': city,
        'state': state,
        'pincode': pincode,
        'is_default': isDefault,
      };
}

class Review {
  final int id;
  final int rating;
  final String? title;
  final String? body;
  final bool isVerifiedPurchase;
  final String? userName;
  final DateTime createdAt;

  Review({
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
      id: json['id'] as int,
      rating: json['rating'] as int,
      title: json['title'] as String?,
      body: json['body'] as String?,
      isVerifiedPurchase: json['is_verified_purchase'] as bool? ?? false,
      userName: json['user']?['name'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}
