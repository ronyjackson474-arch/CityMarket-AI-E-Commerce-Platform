/// CityMarket User & Auth Models

class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? avatar;
  final String role; // 'customer' or 'admin'
  final Wallet? wallet;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatar,
    this.role = 'customer',
    this.wallet,
  });

  bool get isAdmin => role == 'admin';

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      avatar: json['avatar'] as String?,
      role: json['role'] as String? ?? 'customer',
      wallet: json['wallet'] != null
          ? Wallet.fromJson(json['wallet'] as Map<String, dynamic>)
          : null,
    );
  }
}

class Wallet {
  final int id;
  final double balance;
  final String referralCode;
  final int? referredBy;

  Wallet({
    required this.id,
    required this.balance,
    required this.referralCode,
    this.referredBy,
  });

  factory Wallet.fromJson(Map<String, dynamic> json) {
    return Wallet(
      id: json['id'] as int,
      balance: double.parse(json['balance'].toString()),
      referralCode: json['referral_code'] as String? ?? '',
      referredBy: json['referred_by'] as int?,
    );
  }
}

class WalletTransaction {
  final int id;
  final String type; // 'credit' or 'debit'
  final double amount;
  final String reason;
  final String? description;
  final DateTime createdAt;

  WalletTransaction({
    required this.id,
    required this.type,
    required this.amount,
    required this.reason,
    this.description,
    required this.createdAt,
  });

  factory WalletTransaction.fromJson(Map<String, dynamic> json) {
    return WalletTransaction(
      id: json['id'] as int,
      type: json['type'] as String,
      amount: double.parse(json['amount'].toString()),
      reason: json['reason'] as String,
      description: json['description'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

class AuthResponse {
  final String token;
  final User user;

  AuthResponse({required this.token, required this.user});

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      token: json['token'] as String,
      user: User.fromJson(json['user'] as Map<String, dynamic>),
    );
  }
}
