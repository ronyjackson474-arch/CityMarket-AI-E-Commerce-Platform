/// Central configuration for the CityMarket Flutter app.
///
/// [apiBaseUrl] points at the public REST API of this project.
/// Keep it in one place so switching environments is a one-line change.
class AppConstants {
  AppConstants._();

  /// The public base URL of the Laravel backend (no trailing slash).
  static const String baseUrl =
      'https://citymarket-ai-e-comm-2sb7p3.drytis.dev';

  /// REST API prefix — everything under `/api`.
  static const String apiBaseUrl = '$baseUrl/api';

  /// Image / asset base (used for resolving relative image_path values).
  static const String mediaBaseUrl = '$baseUrl/storage';

  /// App-wide display name.
  static const String appName = 'CityMarket';

  /// SharedPreferences keys.
  static const String prefToken = 'auth_token';
  static const String prefUserName = 'user_name';
  static const String prefUserEmail = 'user_email';

  /// Default currency symbol (Indian Rupee — catalog uses ₹ pricing).
  static const String currencySymbol = '₹';

  /// Test credentials shipped with the app (seeded on the backend).
  /// Real users create their own accounts via Register.
  static const String demoEmail = 'customer@citymarket.test';
  static const String demoPassword = 'password';
}
