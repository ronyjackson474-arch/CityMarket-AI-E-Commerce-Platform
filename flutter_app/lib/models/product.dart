/// CityMarket catalog models — Product, Category, Brand, ProductImage.
///
/// Verified against `GET /api/products`, `/api/products/{slug}`, `/api/categories`,
/// `/api/products/featured`, `/api/products/flash-deals`.

import 'dart:convert';

class Product {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final double price;
  final double? salePrice;
  final int stock;
  final double? rating;
  final int reviewCount;
  final bool isFeatured;
  final bool isFlashDeal;
  final DateTime? flashDealEndsAt;
  final Category? category;
  final Brand? brand;
  final List<ProductImage> images;
  final Map<String, String> specifications;

  const Product({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    required this.price,
    this.salePrice,
    required this.stock,
    this.rating,
    required this.reviewCount,
    this.isFeatured = false,
    this.isFlashDeal = false,
    this.flashDealEndsAt,
    this.category,
    this.brand,
    this.images = const [],
    this.specifications = const {},
  });

  /// Lowest applicable price (sale if active, else price).
  double get effectivePrice =>
      (salePrice != null && salePrice! > 0 && salePrice! < price)
          ? salePrice!
          : price;

  bool get isOnSale =>
      salePrice != null && salePrice! > 0 && salePrice! < price;

  int get discountPercent {
    if (!isOnSale) return 0;
    return ((1 - salePrice! / price) * 100).round();
  }

  String? get primaryImage =>
      images.isEmpty ? null : (images.firstWhere((i) => i.isPrimary,
              orElse: () => images.first).imagePath);

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: _asInt(json['id']),
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String?,
      price: _asDouble(json['price']),
      salePrice: json['sale_price'] == null
          ? null
          : _asDouble(json['sale_price']),
      stock: _asInt(json['stock']),
      rating: json['rating'] == null ? null : _asDouble(json['rating']),
      reviewCount: _asInt(json['review_count']),
      isFeatured: json['is_featured'] as bool? ?? false,
      isFlashDeal: json['is_flash_deal'] as bool? ?? false,
      flashDealEndsAt: json['flash_deal_ends_at'] == null
          ? null
          : DateTime.tryParse(json['flash_deal_ends_at'] as String),
      category: json['category'] is Map
          ? Category.fromJson(json['category'] as Map<String, dynamic>)
          : null,
      brand: json['brand'] is Map
          ? Brand.fromJson(json['brand'] as Map<String, dynamic>)
          : null,
      images: (json['images'] as List<dynamic>?)
              ?.map((e) => ProductImage.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      // `specifications` comes back as a JSON string — decode it.
      specifications: _parseSpecs(json['specifications']),
    );
  }

  factory Product.empty() => const Product(
        id: 0,
        name: '',
        slug: '',
        price: 0,
        stock: 0,
        reviewCount: 0,
      );
}

/// API returns `specifications` as a JSON-encoded string (e.g.
/// `'{"Display":"6.7\" Super Retina XDR"}'`) OR an already-decoded map OR
/// the string `'[]'` when empty. Handle all cases.
Map<String, String> _parseSpecs(dynamic raw) {
  if (raw == null) return {};
  try {
    if (raw is Map) {
      return raw.map((k, v) => MapEntry('$k', '$v'));
    }
    if (raw is String) {
      if (raw.isEmpty || raw == '[]') return {};
      final decoded = jsonDecode(raw);
      if (decoded is Map) {
        return decoded.map((k, v) => MapEntry('$k', '$v'));
      }
    }
  } catch (_) {}
  return {};
}

class ProductImage {
  final int id;
  final String imagePath;
  final bool isPrimary;
  final int position;

  const ProductImage({
    required this.id,
    required this.imagePath,
    this.isPrimary = false,
    this.position = 0,
  });

  factory ProductImage.fromJson(Map<String, dynamic> json) {
    return ProductImage(
      id: _asInt(json['id']),
      imagePath: json['image_path'] as String? ?? '',
      isPrimary: json['is_primary'] as bool? ?? false,
      position: _asInt(json['position']),
    );
  }
}

class Category {
  final int id;
  final int? parentId;
  final String name;
  final String slug;
  final String? description;
  final String? icon;
  final String? image;
  final List<Category> children;

  const Category({
    required this.id,
    required this.name,
    required this.slug,
    this.parentId,
    this.description,
    this.icon,
    this.image,
    this.children = const [],
  });

  bool get hasChildren => children.isNotEmpty;

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: _asInt(json['id']),
      parentId: json['parent_id'] == null
          ? null
          : _asInt(json['parent_id']),
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String?,
      icon: json['icon'] as String?,
      image: json['image'] as String?,
      children: (json['children'] as List<dynamic>?)
              ?.map((e) => Category.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class Brand {
  final int id;
  final String name;
  final String slug;
  final String? logo;

  const Brand({
    required this.id,
    required this.name,
    required this.slug,
    this.logo,
  });

  factory Brand.fromJson(Map<String, dynamic> json) {
    return Brand(
      id: _asInt(json['id']),
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      logo: json['logo'] as String?,
    );
  }
}

// ── private helpers (shared in this file) ────────────────────────────
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
