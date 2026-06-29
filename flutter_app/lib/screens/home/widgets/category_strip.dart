import 'package:flutter/material.dart';

import '../../../models/product.dart';
import '../../../theme/app_theme.dart';
import '../../../widgets/app_network_image.dart';

/// Horizontal scrolling strip of top-level categories with circular
/// image thumbnails.
class CategoryStrip extends StatelessWidget {
  const CategoryStrip({
    super.key,
    required this.categories,
    required this.onCategory,
  });

  final List<Category> categories;
  final ValueChanged<Category> onCategory;

  @override
  Widget build(BuildContext context) {
    if (categories.isEmpty) {
      return const SizedBox(
          height: 110,
          child: Center(child: Text('No categories yet')));
    }
    return SizedBox(
      height: 112,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: categories.length,
        separatorBuilder: (_, __) => const SizedBox(width: 14),
        itemBuilder: (context, i) {
          final c = categories[i];
          return GestureDetector(
            onTap: () => onCategory(c),
            child: SizedBox(
              width: 72,
              child: Column(
                children: [
                  Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                          color: const Color(0xFFEEF0F4), width: 1),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: (c.image != null && c.image!.isNotEmpty)
                          ? AppNetworkImage(c.image, fit: BoxFit.cover)
                          : Container(
                              color: AppTheme.primaryLight,
                              child: Center(
                                child: Text(
                                  (c.icon != null && c.icon!.isNotEmpty)
                                      ? c.icon!
                                      : (c.name.isNotEmpty
                                          ? c.name[0].toUpperCase()
                                          : '?'),
                                  style: const TextStyle(
                                      fontSize: 24,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.primary),
                                ),
                              ),
                            ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    c.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        fontSize: 12, fontWeight: FontWeight.w500),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
