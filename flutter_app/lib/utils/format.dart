/// Currency / date formatting helpers.
import 'package:intl/intl.dart';

class Format {
  Format._();

  /// Formats a price as Indian Rupee, e.g. `₹1,29,900`.
  static String currency(num amount, {bool withSymbol = true}) {
    final f = NumberFormat.currency(
      locale: 'en_IN',
      symbol: withSymbol ? '₹' : '',
      decimalDigits: 0,
    );
    return f.format(amount);
  }

  static String date(DateTime dt) =>
      DateFormat('d MMM yyyy').format(dt);

  static String dateTime(DateTime dt) =>
      DateFormat('d MMM yyyy, h:mm a').format(dt);

  /// Compact date like "2h ago" / "3d ago".
  static String timeAgo(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1) return 'just now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return DateFormat('d MMM').format(dt);
  }
}
