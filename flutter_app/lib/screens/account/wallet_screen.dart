import 'package:flutter/material.dart';

import '../../models/user.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/format.dart';
import '../../widgets/buttons.dart';
import '../../widgets/states.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  final _api = ApiService();
  Wallet? _wallet;
  List<WalletTransaction> _transactions = [];
  bool _loading = true;
  String? _error;
  final _referralCtrl = TextEditingController();
  bool _applying = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _referralCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await _api.getWallet();
      final data = res['data'] as Map<String, dynamic>? ?? {};
      _wallet = Wallet.fromJson(data);
      _transactions = _wallet!.transactions;
      // also load full transaction list
      try {
        final tRes = await _api.getWalletTransactions();
        final list = tRes['data'] as List? ?? [];
        _transactions = list
            .map((e) => WalletTransaction.fromJson(e as Map<String, dynamic>))
            .toList();
      } catch (_) {}
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _applyReferral() async {
    if (_referralCtrl.text.trim().isEmpty) return;
    setState(() => _applying = true);
    try {
      await _api.applyReferral(_referralCtrl.text.trim());
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Referral applied!'),
              backgroundColor: AppTheme.success),
        );
      }
      _referralCtrl.clear();
      _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _applying = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Wallet')),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ErrorState(message: _error!, onRetry: _load)
                : ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // balance card
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          gradient: AppTheme.darkGradient,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Wallet Balance',
                              style: TextStyle(
                                  color: Colors.white70, fontSize: 14),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              Format.currency(_wallet?.balance ?? 0),
                              style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 34,
                                  fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 20),
                            Row(
                              children: [
                                const Icon(Icons.card_giftcard_rounded,
                                    color: Colors.white70, size: 18),
                                const SizedBox(width: 6),
                                const Text('Referral Code:',
                                    style: TextStyle(
                                        color: Colors.white70,
                                        fontSize: 13)),
                                const SizedBox(width: 6),
                                Text(
                                  _wallet?.referralCode ?? '-',
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 15),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),
                      // referral apply
                      const Text('Have a referral code?',
                          style: TextStyle(
                              fontSize: 15, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: _referralCtrl,
                              decoration: const InputDecoration(
                                hintText: 'Enter code',
                                prefixIcon:
                                    Icon(Icons.redeem_rounded, size: 20),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          GradientButton(
                            label: 'Apply',
                            loading: _applying,
                            onPressed: _applyReferral,
                            width: 100,
                            height: 50,
                          ),
                        ],
                      ),
                      const SizedBox(height: 28),
                      // transactions
                      Row(
                        children: [
                          const Text('Transactions',
                              style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold)),
                          const Spacer(),
                          Text('${_transactions.length}',
                              style: const TextStyle(
                                  color: AppTheme.textSecondary)),
                        ],
                      ),
                      const SizedBox(height: 8),
                      if (_transactions.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF7F8FA),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Column(
                            children: [
                              Icon(Icons.receipt_long_outlined,
                                  color: AppTheme.textHint, size: 32),
                              SizedBox(height: 8),
                              Text('No transactions yet',
                                  style: TextStyle(
                                      color: AppTheme.textSecondary,
                                      fontSize: 13)),
                            ],
                          ),
                        )
                      else
                        ..._transactions.map(_txTile),
                    ],
                  ),
      ),
    );
  }

  Widget _txTile(WalletTransaction t) {
    final isCredit = t.type.toLowerCase() == 'credit';
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: (isCredit ? AppTheme.success : AppTheme.danger)
                .withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(
            isCredit
                ? Icons.arrow_downward_rounded
                : Icons.arrow_upward_rounded,
            color: isCredit ? AppTheme.success : AppTheme.danger,
            size: 20,
          ),
        ),
        title: Text(t.reason.isEmpty ? t.description ?? 'Transaction' : t.reason,
            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
        subtitle: Text(Format.dateTime(t.createdAt),
            style:
                const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
        trailing: Text(
          '${isCredit ? '+' : '-'}${Format.currency(t.amount)}',
          style: TextStyle(
            color: isCredit ? AppTheme.success : AppTheme.danger,
            fontWeight: FontWeight.bold,
            fontSize: 15,
          ),
        ),
      ),
    );
  }
}
