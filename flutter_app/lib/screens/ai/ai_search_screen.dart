import 'package:flutter/material.dart';

import '../../models/ai.dart';
import '../../models/product.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/buttons.dart';
import '../../widgets/product_card.dart';
import '../../widgets/states.dart';

/// AI-powered natural language product search. User types a query like
/// "laptop under 50000" and the backend LLM interprets it into structured
/// filters, returning matching products.
class AiSearchScreen extends StatefulWidget {
  const AiSearchScreen({super.key});

  @override
  State<AiSearchScreen> createState() => _AiSearchScreenState();
}

class _AiSearchScreenState extends State<AiSearchScreen> {
  final _ctrl = TextEditingController();
  final _api = ApiService();
  AiSearchResult? _result;
  bool _loading = false;
  String? _error;

  static const _suggestions = [
    'laptop under 50000',
    'best smartphone camera',
    'wireless headphones for gaming',
    'smartwatch with long battery',
    'budget tablet for students',
  ];

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  Future<void> _search({String? query}) async {
    final q = query ?? _ctrl.text.trim();
    if (q.isEmpty) return;
    _ctrl.text = q;
    FocusScope.of(context).unfocus();
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await _api.aiSearch(q);
      _result = AiSearchResult.fromJson(res);
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('AI Search')),
      body: Column(
        children: [
          // search field
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _ctrl,
                    textInputAction: TextInputAction.search,
                    onSubmitted: (v) => _search(),
                    decoration: InputDecoration(
                      hintText: 'Describe what you\'re looking for...',
                      prefixIcon: const Icon(Icons.auto_awesome_rounded,
                          color: AppTheme.primary),
                      suffixIcon: _ctrl.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded),
                              onPressed: () {
                                _ctrl.clear();
                                setState(() => _result = null);
                              },
                            )
                          : null,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                GradientButton(
                  label: '',
                  icon: Icons.search_rounded,
                  loading: _loading,
                  width: 50,
                  height: 50,
                  onPressed: () => _search(),
                ),
              ],
            ),
          ),
          // suggestions
          if (_result == null && !_loading && _error == null)
            _suggestionsSection()
          else if (_loading)
            const Expanded(
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(),
                    SizedBox(height: 16),
                    Text('AI is thinking...',
                        style: TextStyle(color: AppTheme.textSecondary)),
                  ],
                ),
              ),
            )
          else if (_error != null)
            Expanded(
              child: ErrorState(
                  message: _error!,
                  onRetry: () => _search()),
            )
          else if (_result != null)
            Expanded(child: _resultsSection()),
        ],
      ),
    );
  }

  Widget _suggestionsSection() {
    return Expanded(
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const Icon(Icons.auto_awesome_rounded,
              size: 56, color: AppTheme.primary),
          const SizedBox(height: 12),
          const Text(
            'Search with AI',
            textAlign: TextAlign.center,
            style: TextStyle(
                fontSize: 22, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          const Text(
            'Describe what you need in plain English. Our AI will find the perfect products for you.',
            textAlign: TextAlign.center,
            style: TextStyle(
                color: AppTheme.textSecondary, height: 1.5, fontSize: 14),
          ),
          const SizedBox(height: 24),
          const Text('Try:',
              style: TextStyle(fontWeight: FontWeight.w600)),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _suggestions
                .map((s) => ActionChip(
                      label: Text(s),
                      labelStyle:
                          const TextStyle(color: AppTheme.primary),
                      backgroundColor: AppTheme.primaryLight.withValues(alpha: 0.3),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20)),
                      onPressed: () => _search(query: s),
                    ))
                .toList(),
          ),
        ],
      ),
    );
  }

  Widget _resultsSection() {
    final r = _result!;
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      children: [
        // interpretation card
        Container(
          padding: const EdgeInsets.all(14),
          margin: const EdgeInsets.only(bottom: 8),
          decoration: BoxDecoration(
            gradient: AppTheme.darkGradient,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.auto_awesome_rounded,
                      color: Colors.white, size: 18),
                  const SizedBox(width: 8),
                  const Text(
                    'AI Interpretation',
                    style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 14),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                '"${r.query}"',
                style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.9),
                    fontStyle: FontStyle.italic,
                    fontSize: 13),
              ),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 6,
                children: [
                  if (r.interpretation.keywords.isNotEmpty)
                    _aiTag('🔑 ${r.interpretation.keywords}'),
                  if (r.interpretation.category != null)
                    _aiTag('📦 ${r.interpretation.category}'),
                  if (r.interpretation.priceMax != null)
                    _aiTag(
                        '💰 Under ${Format.currency(r.interpretation.priceMax!)}'),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        Text(
          '${r.products.length} product(s) found',
          style: const TextStyle(
              color: AppTheme.textSecondary,
              fontWeight: FontWeight.w600,
              fontSize: 13),
        ),
        const SizedBox(height: 8),
        if (r.products.isEmpty)
          const EmptyState(
            icon: Icons.search_off_rounded,
            title: 'No matches',
            subtitle: 'Try rephrasing your query.',
          )
        else
          ProductGrid(
            products: r.products,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            padding: EdgeInsets.zero,
            onTap: (p) => Nav.toProduct(context, p.slug),
          ),
      ],
    );
  }

  Widget _aiTag(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(label,
          style: const TextStyle(color: Colors.white, fontSize: 12)),
    );
  }
}
