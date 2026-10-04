import 'package:flutter/material.dart';

/// Cross-platform breakpoints + layout helpers.
///
/// Phone:  < 600
/// Tablet: 600–1023
/// Desktop/Web: >= 1024
class Responsive {
  const Responsive._();

  static const double tabletBreakpoint = 600;
  static const double desktopBreakpoint = 1024;
  static const double maxContentWidth = 1200;
  static const double maxFormWidth = 800;

  static bool isPhone(BuildContext context) =>
      MediaQuery.of(context).size.width < tabletBreakpoint;

  static bool isTablet(BuildContext context) {
    final w = MediaQuery.of(context).size.width;
    return w >= tabletBreakpoint && w < desktopBreakpoint;
  }

  static bool isDesktop(BuildContext context) =>
      MediaQuery.of(context).size.width >= desktopBreakpoint;

  static bool isWide(BuildContext context) => isTablet(context) || isDesktop(context);

  /// Grid columns for product cards: 2 phone, 3 tablet, 4–5 desktop.
  static int productColumns(BuildContext context) {
    final w = MediaQuery.of(context).size.width;
    if (w >= 1400) return 5;
    if (w >= desktopBreakpoint) return 4;
    if (w >= tabletBreakpoint) return 3;
    return 2;
  }

  /// Grid columns for category tiles: 2 phone, 4 tablet, 6 desktop.
  static int categoryColumns(BuildContext context) {
    final w = MediaQuery.of(context).size.width;
    if (w >= desktopBreakpoint) return 6;
    if (w >= tabletBreakpoint) return 4;
    return 2;
  }

  /// Center + constrain content width on wide screens.
  static Widget centered({
    required BuildContext context,
    required Widget child,
    double maxWidth = maxContentWidth,
  }) {
    return Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth),
        child: child,
      ),
    );
  }

  /// Horizontal padding: 16 phone, 24 tablet, 32 desktop.
  static double screenPadding(BuildContext context) =>
      isDesktop(context) ? 32 : (isTablet(context) ? 24 : 16);
}
