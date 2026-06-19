/// CityMarket Product Model
/// Maps to the Product entity in the REST API

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
  final Category? category;
  final Brand? brand;
  final List<ProductImage> images;
  final Map<String, dynamic>? specifications;

  Product({
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
    this.category,
    this.brand,
    this.images = const [],
    this.specifications,
  });

  double get effectivePrice =>
      (salePrice != null && salePrice! > 0 && salePrice! < price) ? salePrice! : price;

  int get discountPercent {
    if (salePrice != null && salePrice! > 0 && salePrice! < price) {
      return ((1 - salePrice! / price) * 100).round();
    }
    return 0;
  }

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      description: json['description'] as String?,
      price: double.parse(json['price'].toString()),
      salePrice: json['sale_price'] != null
          ? double.parse(json['sale_price'].toString())
          : null,
      stock: json['stock'] as int? ?? 0,
      rating: json['rating'] != null
          ? double.parse(json['rating'].toString())
          : null,
      reviewCount: json['review_count'] as int? ?? 0,
      isFeatured: json['is_featured'] as bool? ?? false,
      isFlashDeal: json['is_flash_deal'] as bool? ?? false,
      category: json['category'] != null
          ? Category.fromJson(json['category'] as Map<String, dynamic>)
          : null,
      brand: json['brand'] != null
          ? Brand.fromJson(json['brand'] as Map<String, dynamic>)
          : null,
      images: (json['images'] as List<dynamic>?)
              ?.map((e) => ProductImage.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      specifications: json['specifications'] as Map<String, dynamic>?,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'slug': slug,
        'price': price,
        'sale_price': salePrice,
        'stock': stock,
      };
}

class ProductImage {
  final int id;
  final String imagePath;
  final bool isPrimary;
  final int position;

  ProductImage({
    required this.id,
    required this.imagePath,
    this.isPrimary = false,
    this.position = 0,
  });

  factory ProductImage.fromJson(Map<String, dynamic> json) {
    return ProductImage(
      id: json['id'] as int,
      imagePath: json['image_path'] as String,
      isPrimary: json['is_primary'] as bool? ?? false,
      position: json['position'] as int? ?? 0,
    );
  }
}

class Category {
  final int id;
  final String name;
  final String slug;
  final String? icon;
  final String? image;
  final int? parentId;
  final List<Category> children;

  Category({
    required this.id,
    required this.name,
    required this.slug,
    this.icon,
    this.image,
    this.parentId,
    this.children = const [],
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      icon: json['icon'] as String?,
      image: json['image'] as String?,
      parentId: json['parent_id'] as int?,
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

  Brand({
    required this.id,
    required this.name,
    required this.slug,
    this.logo,
  });

  factory Brand.fromJson(Map<String, dynamic> json) {
    return Brand(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      logo: json['logo'] as String?,
    );
  }
}
