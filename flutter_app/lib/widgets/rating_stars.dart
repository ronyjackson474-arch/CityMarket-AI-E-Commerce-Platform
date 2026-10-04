import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';

/// Row of 5 stars reflecting a rating value (0–5). Read-only.
class RatingStars extends StatelessWidget {
  const RatingStars({
    super.key,
    required this.rating,
    this.size = 14,
    this.ignoreGestures = true,
    this.color = const Color(0xFFFFB300),
  });

  final double rating;
  final double size;
  final bool ignoreGestures;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return RatingBarIndicator(
      rating: rating,
      itemBuilder: (_, __) => Icon(Icons.star_rounded, color: color),
      itemCount: 5,
      itemSize: size,
    );
  }
}

/// Interactive star picker used when posting a review.
class RatingPicker extends StatelessWidget {
  const RatingPicker({
    super.key,
    required this.rating,
    required this.onUpdate,
    this.size = 36,
  });

  final double rating;
  final ValueChanged<double> onUpdate;
  final double size;

  @override
  Widget build(BuildContext context) {
    return RatingBar(
      initialRating: rating,
      minRating: 1,
      maxRating: 5,
      allowHalfRating: false,
      itemSize: size,
      glow: false,
      tapOnlyMode: true,
      ratingWidget: RatingWidget(
        full: const Icon(Icons.star_rounded, color: Color(0xFFFFB300)),
        half: const Icon(Icons.star_half_rounded, color: Color(0xFFFFB300)),
        empty:
            const Icon(Icons.star_outline_rounded, color: Color(0xFFCBD5E1)),
      ),
      onRatingUpdate: onUpdate,
    );
  }
}
