import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';

/// Network image that shows a shimmer placeholder while loading and a
/// graceful fallback icon on error. Used everywhere product images appear.
class AppNetworkImage extends StatelessWidget {
  const AppNetworkImage(
    this.url, {
    super.key,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
  });

  final String? url;
  final double? width;
  final double? height;
  final BoxFit fit;
  final double? borderRadius;

  @override
  Widget build(BuildContext context) {
    final effectiveUrl = (url != null && url!.isNotEmpty)
        ? url!
        : null;

    Widget placeholder = Container(
      width: width,
      height: height,
      color: const Color(0xFFF1F3F6),
      child: const Center(
        child: Icon(Icons.image_outlined, color: Color(0xFFB0B7C3)),
      ),
    );

    if (effectiveUrl == null) {
      return _clip(placeholder);
    }

    return _clip(CachedNetworkImage(
      imageUrl: effectiveUrl,
      width: width,
      height: height,
      fit: fit,
      placeholder: (_, __) => Container(
        width: width,
        height: height,
        color: const Color(0xFFEEF0F4),
      ),
      errorWidget: (_, __, ___) => placeholder,
    ));
  }

  Widget _clip(Widget child) {
    if (borderRadius == null) return child;
    return ClipRRect(borderRadius: BorderRadius.circular(borderRadius!), child: child);
  }
}
