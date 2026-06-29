/// Generic Laravel pagination parser.
///
/// Most list endpoints return the standard `Illuminate\Pagination` envelope:
/// ```
/// {"current_page": 1, "data": [...], "next_page_url": "...|null",
///  "last_page": 3, "per_page": 20, "total": 58}
/// ```
/// Some endpoints (featured, flash-deals, recommendations) return just
/// `{"data": [...]}` — this parser handles both.

class Paginated<T> {
  final List<T> data;
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;
  final String? nextPageUrl;
  final String? prevPageUrl;

  const Paginated({
    required this.data,
    this.currentPage = 1,
    this.lastPage = 1,
    this.perPage = 20,
    this.total = 0,
    this.nextPageUrl,
    this.prevPageUrl,
  });

  bool get hasNextPage =>
      nextPageUrl != null && nextPageUrl!.isNotEmpty && currentPage < lastPage;

  factory Paginated.fromJson(
    Map<String, dynamic> json,
    T Function(Map<String, dynamic>) fromJson,
  ) {
    final raw = json['data'];
    // If `data` is a List → normal paginated (or bare) list.
    if (raw is List) {
      return Paginated<T>(
        data: raw
            .map((e) => fromJson(e as Map<String, dynamic>))
            .toList(),
        currentPage: json['current_page'] as int? ?? 1,
        lastPage: json['last_page'] as int? ?? 1,
        perPage: json['per_page'] as int? ?? raw.length,
        total: json['total'] as int? ?? raw.length,
        nextPageUrl: json['next_page_url'] as String?,
        prevPageUrl: json['prev_page_url'] as String?,
      );
    }
    // Fallback: unknown shape → empty.
    return const Paginated<T>(data: []);
  }
}
