import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/ai.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/auth_guard.dart';
import '../../utils/format.dart';

/// AI shopping assistant chat. Each user message is sent to the backend
/// `POST /chat/send`; the assistant response is appended as a bubble.
class AiChatScreen extends StatefulWidget {
  const AiChatScreen({super.key});

  @override
  State<AiChatScreen> createState() => _AiChatScreenState();
}

class _AiChatScreenState extends State<AiChatScreen> {
  final _api = ApiService();
  final _inputCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();
  final List<ChatMessage> _messages = [];
  int? _conversationId;
  bool _sending = false;

  static const _quickPrompts = [
    'Help me find a gift',
    'Track my order',
    'What are today\'s deals?',
    'Compare iPhone vs Samsung',
  ];

  @override
  void initState() {
    super.initState();
    _messages.add(ChatMessage.assistant(
      'Hi! I\'m your CityMarket AI assistant 🤖\nHow can I help you shop today?',
    ));
  }

  @override
  void dispose() {
    _inputCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollCtrl.hasClients) {
        _scrollCtrl.animateTo(
          _scrollCtrl.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _send({String? text}) async {
    final message = (text ?? _inputCtrl.text).trim();
    if (message.isEmpty || _sending) return;

    // guard auth
    if (!await AuthGuard.ensure(context)) return;

    _inputCtrl.clear();
    setState(() {
      _messages.add(ChatMessage.user(message));
      _sending = true;
    });
    _scrollToBottom();

    try {
      final res = await _api.chatSend(message, conversationId: _conversationId);
      _conversationId = res['conversation_id'] as int?;
      final reply = res['message'] as String? ??
          'Sorry, I didn\'t get that. Could you rephrase?';
      setState(() {
        _messages.add(ChatMessage.assistant(
          reply,
          id: res['assistant_message_id'] as int?,
        ));
      });
    } catch (e) {
      setState(() {
        _messages.add(ChatMessage.assistant(
          'I\'m having trouble connecting. Please try again.',
        ));
      });
    } finally {
      if (mounted) {
        setState(() => _sending = false);
      }
      _scrollToBottom();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                gradient: AppTheme.primaryGradient,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.auto_awesome_rounded,
                  color: Colors.white, size: 18),
            ),
            const SizedBox(width: 10),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('AI Assistant',
                    style:
                        TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                Text('Online • Typically replies instantly',
                    style: TextStyle(
                        fontSize: 11, color: AppTheme.success)),
              ],
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          Expanded(child: _messageList()),
          if (_messages.length <= 1) _quickPrompts(),
          _inputBar(),
        ],
      ),
    );
  }

  Widget _messageList() {
    return ListView.builder(
      controller: _scrollCtrl,
      padding: const EdgeInsets.all(16),
      itemCount: _messages.length,
      itemBuilder: (context, i) {
        final m = _messages[i];
        final showTime =
            i == _messages.length - 1 || _messages[i + 1].isUser != m.isUser;
        return _bubble(m, showTime: showTime);
      },
    );
  }

  Widget _bubble(ChatMessage m, {bool showTime = false}) {
    final isUser = m.isUser;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment:
            isUser ? CrossAxisAlignment.end : CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment:
                isUser ? MainAxisAlignment.end : MainAxisAlignment.start,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              if (!isUser) ...[
                CircleAvatar(
                  radius: 14,
                  backgroundColor: AppTheme.primaryLight,
                  child: const Icon(Icons.auto_awesome_rounded,
                      size: 14, color: AppTheme.primary),
                ),
                const SizedBox(width: 8),
              ],
              Flexible(
                child: Container(
                  constraints: BoxConstraints(
                    maxWidth: MediaQuery.of(context).size.width * 0.75,
                  ),
                  padding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    color: isUser ? AppTheme.primary : Colors.white,
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(16),
                      topRight: const Radius.circular(16),
                      bottomLeft:
                          isUser ? const Radius.circular(16) : Radius.zero,
                      bottomRight:
                          isUser ? Radius.zero : const Radius.circular(16),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.05),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Text(
                    m.message,
                    style: TextStyle(
                      color: isUser ? Colors.white : AppTheme.textPrimary,
                      fontSize: 14,
                      height: 1.4,
                    ),
                  ),
                ),
              ),
            ],
          ),
          if (showTime)
            Padding(
              padding: EdgeInsets.only(
                  left: isUser ? 0 : 36, top: 4, bottom: 4),
              child: Text(
                Format.timeAgo(m.timestamp),
                style: const TextStyle(
                    fontSize: 10, color: AppTheme.textHint),
              ),
            ),
        ],
      ),
    );
  }

  Widget _quickPrompts() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Wrap(
        spacing: 8,
        runSpacing: 8,
        children: _quickPromptsList
            .map((q) => ActionChip(
                  label: Text(q),
                  labelStyle: const TextStyle(fontSize: 12),
                  backgroundColor: const Color(0xFFF1F3F6),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20)),
                  onPressed: () => _send(text: q),
                ))
            .toList(),
      ),
    );
  }

  List<String> get _quickPromptsList => _quickPrompts;

  Widget _inputBar() {
    return SafeArea(
      child: Container(
        padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _inputCtrl,
                minLines: 1,
                maxLines: 4,
                textInputAction: TextInputAction.send,
                onSubmitted: (v) => _send(),
                enabled: !_sending,
                decoration: InputDecoration(
                  hintText: 'Type a message...',
                  filled: true,
                  fillColor: const Color(0xFFF1F3F6),
                  contentPadding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 10),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide.none,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 8),
            Container(
              width: 44,
              height: 44,
              decoration: const BoxDecoration(
                gradient: AppTheme.primaryGradient,
                shape: BoxShape.circle,
              ),
              child: IconButton(
                icon: _sending
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                            strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.send_rounded,
                        color: Colors.white, size: 18),
                onPressed: _sending ? null : () => _send(),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
