import 'dart:async';
import 'package:flutter/material.dart';

import '../../../theme/app_theme.dart';

/// A live countdown timer for a flash deal. Displays HH:MM:SS until [endsAt].
class CountdownTimer extends StatefulWidget {
  const CountdownTimer({super.key, required this.endsAt});
  final DateTime endsAt;

  @override
  State<CountdownTimer> createState() => _CountdownTimerState();
}

class _CountdownTimerState extends State<CountdownTimer> {
  Timer? _timer;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _update();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _update());
  }

  void _update() {
    final diff = widget.endsAt.difference(DateTime.now());
    if (!mounted) return;
    if (diff.isNegative) {
      _timer?.cancel();
      setState(() => _remaining = Duration.zero);
      return;
    }
    setState(() => _remaining = diff);
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  String _two(int n) => n.toString().padLeft(2, '0');

  @override
  Widget build(BuildContext context) {
    final h = _remaining.inHours;
    final m = _remaining.inMinutes.remainder(60);
    final s = _remaining.inSeconds.remainder(60);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
      decoration: BoxDecoration(
        color: const Color(0xFFFDECEC),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.timer_outlined, size: 12, color: AppTheme.danger),
          const SizedBox(width: 3),
          Text(
            '${_two(h)}:${_two(m)}:${_two(s)}',
            style: const TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.bold,
                color: AppTheme.danger),
          ),
        ],
      ),
    );
  }
}
