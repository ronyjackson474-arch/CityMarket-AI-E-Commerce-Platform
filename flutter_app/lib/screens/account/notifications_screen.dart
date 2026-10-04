import 'package:flutter/material.dart';

import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/states.dart';

/// Notifications list: unread highlight, tap = mark read, mark-all button.
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final _api = ApiService();
  List<Map<String, dynamic>> _items = [];
  int _unread = 0;
  bool _loading = true;
  bool _markingAll = false;
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
      final res = await _api.getNotifications();
      final list = res['data'] as List? ?? [];
      final meta = res['meta'] as Map<String, dynamic>? ?? {};
      setState(() {
        _items = list.cast<Map<String, dynamic>>();
        _unread = (meta['unread'] ?? 0) as int;
      });
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _markRead(Map<String, dynamic> n) async {
    if (n['read_at'] != null) return;
    final id = n['id'] as int;
    try {
      await _api.markNotificationRead(id);
      setState(() {
        n['read_at'] = DateTime.now().toIso8601String();
        if (_unread > 0) _unread--;
      });
    } catch (_) {
      // best-effort
    }
  }

  Future<void> _markAll() async {
    setState(() => _markingAll = true);
    try {
      await _api.markAllNotificationsRead();
      setState(() {
        for (final n in _items) {
          n['read_at'] ??= DateTime.now().toIso8601String();
        }
        _unread = 0;
      });
    } catch (_) {
      // best-effort
    } finally {
      if (mounted) setState(() => _markingAll = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          if (_unread > 0)
            TextButton.icon(
              onPressed: _markingAll ? null : _markAll,
              icon: const Icon(Icons.done_all_rounded, size: 18),
              label: const Text('Mark all read'),
              style: TextButton.styleFrom(foregroundColor: AppTheme.primary),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? ErrorState(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: _items.isEmpty
                      ? ListView(
                          children: const [
                            SizedBox(height: 120),
                            Icon(Icons.notifications_none_rounded,
                                size: 72, color: AppTheme.textFaint),
                            SizedBox(height: 12),
                            Center(
                              child: Text('No notifications yet',
                                  style: TextStyle(
                                      color: AppTheme.textSecondary)),
                            ),
                          ],
                        )
                      : ListView.separated(
                          padding: const EdgeInsets.all(12),
                          itemCount: _items.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 8),
                          itemBuilder: (context, i) =>
                              _tile(_items[i]),
                        ),
                ),
    );
  }

  Widget _tile(Map<String, dynamic> n) {
    final unread = n['read_at'] == null;
    final title = n['title'] as String? ?? 'Notification';
    final body = n['body'] as String? ?? n['message'] as String? ?? '';
    final created = n['created_at'] as String? ?? '';
    return Dismissible(
      key: ValueKey('notif-${n['id']}'),
      direction: DismissDirection.startToEnd,
      background: Container(
        alignment: Alignment.centerLeft,
        padding: const EdgeInsets.only(left: 20),
        decoration: BoxDecoration(
          color: AppTheme.primary.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(14),
        ),
        child: const Icon(Icons.done_rounded, color: AppTheme.primary),
      ),
      confirmDismiss: (_) async {
        await _markRead(n);
        return false; // swipe marks read, doesn't remove
      },
      child: Material(
        color: unread ? AppTheme.primary.withValues(alpha: 0.05) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: () => _markRead(n),
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              border: Border.all(
                color: unread
                    ? AppTheme.primary.withValues(alpha: 0.3)
                    : Colors.grey.withValues(alpha: 0.15),
              ),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Stack(
                  children: [
                    CircleAvatar(
                      radius: 20,
                      backgroundColor:
                          AppTheme.primary.withValues(alpha: 0.12),
                      child: Icon(
                        unread
                            ? Icons.notifications_active_rounded
                            : Icons.notifications_none_rounded,
                        size: 20,
                        color: AppTheme.primary,
                      ),
                    ),
                    if (unread)
                      Positioned(
                        right: 0,
                        top: 0,
                        child: Container(
                          width: 10,
                          height: 10,
                          decoration: const BoxDecoration(
                            color: AppTheme.primary,
                            shape: BoxShape.circle,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          fontWeight: unread ? FontWeight.bold : FontWeight.w500,
                          fontSize: 14,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                      if (body.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          body,
                          style: const TextStyle(
                              fontSize: 13, color: AppTheme.textSecondary),
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                      if (created.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        Text(
                          _formatDate(created),
                          style: const TextStyle(
                              fontSize: 11, color: AppTheme.textFaint),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String _formatDate(String iso) {
    try {
      final d = DateTime.parse(iso).toLocal();
      return '${d.day}/${d.month}/${d.year} · ${d.hour}:${d.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return iso;
    }
  }
}
