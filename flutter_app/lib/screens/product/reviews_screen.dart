import 'package:flutter/material.dart';

import '../../models/order.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/format.dart';
import '../../utils/auth_guard.dart';
import '../../widgets/rating_stars.dart';
import '../../widgets/states.dart';

/// Full reviews list for a product + a "Write a review" sheet.
class ReviewsScreen extends StatefulWidget {
  const ReviewsScreen({super.key, required this.slug, this.productName});

  final String slug;
  final String? productName;

  @override
  State<ReviewsScreen> createState() => _ReviewsScreenState();
}

class _ReviewsScreenState extends State<ReviewsScreen> {
  final _api = ApiService();
  List<Review> _reviews = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await _api.getProductReviews(widget.slug, page: 1);
      final list = res['data'] as List? ?? [];
      _reviews = list
          .map((e) => Review.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openWriteReview() async {
    if (!await AuthGuard.ensure(context)) return;
    final result = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => const _WriteReviewSheet(),
    );
    if (result == null) return;
    try {
      await _api.postReview(
        widget.slug,
        result['rating'] as int,
        title: result['title'] as String?,
        body: result['body'] as String?,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Review submitted!'),
              backgroundColor: AppTheme.success),
        );
      }
      _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.productName ?? 'Reviews'),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openWriteReview,
        icon: const Icon(Icons.edit_rounded),
        label: const Text('Write a Review'),
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ErrorState(message: _error!, onRetry: _load)
                : _reviews.isEmpty
                    ? ListView(
                        children: const [
                          SizedBox(height: 80),
                          EmptyState(
                            icon: Icons.reviews_outlined,
                            title: 'No reviews yet',
                            subtitle:
                                'Be the first to share your experience.',
                          ),
                        ],
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16).copyWith(
                            bottom: 100),
                        itemCount: _reviews.length,
                        separatorBuilder: (_, __) =>
                            const SizedBox(height: 10),
                        itemBuilder: (_, i) => _card(_reviews[i]),
                      ),
      ),
    );
  }

  Widget _card(Review r) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 16,
                  backgroundColor: AppTheme.primaryLight,
                  child: Text(
                    (r.userName ?? 'U').isNotEmpty
                        ? (r.userName ?? 'U')[0].toUpperCase()
                        : 'U',
                    style: const TextStyle(
                        color: AppTheme.primary, fontSize: 13),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(r.userName ?? 'Anonymous',
                          style: const TextStyle(
                              fontWeight: FontWeight.w600, fontSize: 14)),
                      Row(
                        children: [
                          RatingStars(rating: r.rating.toDouble(), size: 13),
                          const SizedBox(width: 6),
                          if (r.isVerifiedPurchase)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFE8F5E9),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Text(
                                'Verified',
                                style: TextStyle(
                                    fontSize: 10,
                                    color: AppTheme.success,
                                    fontWeight: FontWeight.w600),
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
                Text(Format.timeAgo(r.createdAt),
                    style: const TextStyle(
                        fontSize: 11, color: AppTheme.textHint)),
              ],
            ),
            if (r.title != null && r.title!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(r.title!,
                  style: const TextStyle(fontWeight: FontWeight.w600)),
            ],
            if (r.body != null && r.body!.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(r.body!,
                  style: const TextStyle(
                      fontSize: 14, color: AppTheme.textSecondary, height: 1.5)),
            ],
          ],
        ),
      ),
    );
  }
}

class _WriteReviewSheet extends StatefulWidget {
  const _WriteReviewSheet();

  @override
  State<_WriteReviewSheet> createState() => _WriteReviewSheetState();
}

class _WriteReviewSheetState extends State<_WriteReviewSheet> {
  double _rating = 0;
  final _title = TextEditingController();
  final _body = TextEditingController();

  @override
  void dispose() {
    _title.dispose();
    _body.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(
          20, 12, 20, MediaQuery.of(context).viewInsets.bottom + 24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: const Color(0xFFE0E3E8),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          const Text('Write a Review',
              style:
                  TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          const Text('Your Rating',
              style: TextStyle(fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          RatingPicker(
            rating: _rating,
            onUpdate: (v) => setState(() => _rating = v),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _title,
            decoration: const InputDecoration(
              labelText: 'Title (optional)',
              prefixIcon: Icon(Icons.title, size: 20),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _body,
            maxLines: 4,
            decoration: const InputDecoration(
              labelText: 'Your review',
              alignLabelWithHint: true,
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Cancel'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  onPressed: _rating < 1
                      ? null
                      : () => Navigator.pop(context, {
                            'rating': _rating.round(),
                            'title': _title.text.trim().isEmpty
                                ? null
                                : _title.text.trim(),
                            'body': _body.text.trim().isEmpty
                                ? null
                                : _body.text.trim(),
                          }),
                  child: const Text('Submit'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
