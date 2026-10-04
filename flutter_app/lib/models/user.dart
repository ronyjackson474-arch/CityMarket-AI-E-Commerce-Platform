/// CityMarket user, wallet & auth models — match the **real** API shapes.
///
/// Verified against `POST /api/auth/login` and `GET /api/auth/me`:
/// ```
/// {"message": "...", "data": {"user": {…}, "token": "1|…"}}
/// {"data": {…user…}}
/// ```

class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? avatar;
  final String role;
  final String? emailVerifiedAt;
  final bool isActive;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatar,
    this.role = 'customer',
    this.emailVerifiedAt,
    this.isActive = true,
  });

  bool get isAdmin => role == 'admin';

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: _asInt(json['id']),
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
      avatar: json['avatar'] as String?,
      role: json['role'] as String? ?? 'customer',
      emailVerifiedAt: json['email_verified_at'] as String?,
      isActive: json['is_active'] as bool? ?? true,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'phone': phone,
        'role': role,
      };
}

class Wallet {
  final int id;
  final int userId;
  final double balance;
  final String referralCode;
  final List<WalletTransaction> transactions;

  const Wallet({
    required this.id,
    required this.userId,
    required this.balance,
    required this.referralCode,
    this.transactions = const [],
  });

  factory Wallet.fromJson(Map<String, dynamic> json) {
    return Wallet(
      id: _asInt(json['id']),
      userId: _asInt(json['user_id']),
      balance: _asDouble(json['balance']),
      referralCode: json['referral_code'] as String? ?? '',
      transactions: (json['transactions'] as List<dynamic>?)
              ?.map((e) => WalletTransaction.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class WalletTransaction {
  final int id;
  final String type; // 'credit' | 'debit'
  final double amount;
  final String reason;
  final String? description;
  final DateTime createdAt;

  const WalletTransaction({
    required this.id,
    required this.type,
    required this.amount,
    required this.reason,
    this.description,
    required this.createdAt,
  });

  factory WalletTransaction.fromJson(Map<String, dynamic> json) {
    return WalletTransaction(
      id: _asInt(json['id']),
      type: json['type'] as String? ?? 'credit',
      amount: _asDouble(json['amount']),
      reason: json['reason'] as String? ?? '',
      description: json['description'] as String?,
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ??
          DateTime.now(),
    );
  }
}

/// Login / Register response wrapper.
class AuthResponse {
  final String message;
  final String token;
  final User user;

  const AuthResponse({
    required this.message,
    required this.token,
    required this.user,
  });

  /// Parses `{"message": "...", "data": {"user": {…}, "token": "1|…"}}`.
  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    final data = (json['data'] as Map<String, dynamic>?) ?? json;
    final token = data['token'] as String?;
    if (token == null) {
      throw const FormatException('Auth response missing token');
    }
    return AuthResponse(
      message: json['message'] as String? ?? '',
      token: token,
      user: User.fromJson(
          (data['user'] as Map<String, dynamic>?) ?? <String, dynamic>{}),
    );
  }
}

// ── helpers ──────────────────────────────────────────────────────────
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
  if (v == null) return 0;
  return 0;
}
