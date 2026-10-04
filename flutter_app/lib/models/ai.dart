/// CityMarket AI + chat models — match real API shapes.
///
/// Verified:
/// - ai/search: `{"message": "...", "data": {"query", "interpretation":
///   {"keywords","category","price_max","ai_interpreted"}, "products": [...]}}`
/// - chat/send: `{"conversation_id": 4, "message": "...", "assistant_message_id": 8}`
/// - chat/conversations: `{"data": [{"id","status","context","created_at"}]}`

import 'product.dart';

/// Response from `POST /api/ai/search`.
class AiSearchResult {
  final String message;
  final String query;
  final AiInterpretation interpretation;
  final List<Product> products;

  const AiSearchResult({
    required this.message,
    required this.query,
    required this.interpretation,
    required this.products,
  });

  factory AiSearchResult.fromJson(Map<String, dynamic> json) {
    final data = (json['data'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final interp =
        (data['interpretation'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    return AiSearchResult(
      message: json['message'] as String? ?? '',
      query: data['query'] as String? ?? '',
      interpretation: AiInterpretation.fromJson(interp),
      products: (data['products'] as List<dynamic>?)
              ?.map((e) => Product.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class AiInterpretation {
  final String keywords;
  final String? category;
  final double? priceMax;
  final double? priceMin;
  final bool aiInterpreted;

  const AiInterpretation({
    this.keywords = '',
    this.category,
    this.priceMax,
    this.priceMin,
    this.aiInterpreted = false,
  });

  factory AiInterpretation.fromJson(Map<String, dynamic> json) {
    return AiInterpretation(
      keywords: json['keywords'] as String? ?? '',
      category: json['category'] as String?,
      priceMax: json['price_max'] == null
          ? null
          : double.tryParse(json['price_max'].toString()),
      priceMin: json['price_min'] == null
          ? null
          : double.tryParse(json['price_min'].toString()),
      aiInterpreted: json['ai_interpreted'] as bool? ?? false,
    );
  }
}

/// A chat conversation (from `GET /chat/conversations`).
class Conversation {
  final int id;
  final String status;
  final DateTime createdAt;

  const Conversation({
    required this.id,
    required this.status,
    required this.createdAt,
  });

  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      id: json['id'] as int? ?? 0,
      status: json['status'] as String? ?? 'active',
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ??
          DateTime.now(),
    );
  }
}

/// A single chat message exchanged with the AI assistant.
/// Rendered in the chat UI; `isUser` flips the bubble side.
class ChatMessage {
  final String message;
  final bool isUser;
  final DateTime timestamp;
  final int? assistantMessageId;

  const ChatMessage({
    required this.message,
    required this.isUser,
    required this.timestamp,
    this.assistantMessageId,
  });

  factory ChatMessage.user(String text) =>
      ChatMessage(message: text, isUser: true, timestamp: DateTime.now());

  factory ChatMessage.assistant(String text, {int? id}) => ChatMessage(
        message: text,
        isUser: false,
        timestamp: DateTime.now(),
        assistantMessageId: id,
      );
}
