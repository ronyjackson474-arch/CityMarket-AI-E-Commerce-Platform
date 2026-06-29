import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/order.dart';
import '../../providers/app_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/format.dart';
import '../../utils/nav.dart';
import '../../widgets/buttons.dart';
import '../../widgets/states.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _api = ApiService();
  Cart? _cart;
  List<Address> _addresses = [];
  int? _selectedAddressId;
  String _paymentMethod = 'cod';
  final _couponCtrl = TextEditingController();
  bool _loading = true;
  bool _placing = false;
  String? _error;

  // Inline new-address form
  bool _showAddressForm = false;
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _line1 = TextEditingController();
  final _city = TextEditingController();
  final _state = TextEditingController();
  final _pin = TextEditingController();

  final _payments = const [
    ('cod', 'Cash on Delivery', Icons.money_rounded),
    ('upi', 'UPI', Icons.qr_code_rounded),
    ('card', 'Credit / Debit Card', Icons.credit_card_rounded),
  ];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _couponCtrl.dispose();
    _name.dispose();
    _phone.dispose();
    _line1.dispose();
    _city.dispose();
    _state.dispose();
    _pin.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final results = await Future.wait([_api.getCart(), _api.getAddresses()]);
      _cart = Cart.fromJson(results[0]);
      final addrList = (results[1]['data'] as List? ?? [])
          .map((e) => Address.fromJson(e as Map<String, dynamic>))
          .toList();
      _addresses = addrList;
      final defaultAddr = addrList.where((a) => a.isDefault).firstOrNull;
      _selectedAddressId = defaultAddr?.id ?? addrList.firstOrNull?.id;
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _saveNewAddress() async {
    if (_name.text.isEmpty ||
        _phone.text.isEmpty ||
        _line1.text.isEmpty ||
        _city.text.isEmpty ||
        _state.text.isEmpty ||
        _pin.text.isEmpty) {
      _toast('Please fill all address fields');
      return;
    }
    try {
      final res = await _api.addAddress({
        'name': _name.text.trim(),
        'phone': _phone.text.trim(),
        'address_line1': _line1.text.trim(),
        'city': _city.text.trim(),
        'state': _state.text.trim(),
        'pincode': _pin.text.trim(),
        'is_default': true,
      });
      final newAddr = Address.fromJson(res['data'] as Map<String, dynamic>);
      setState(() {
        _addresses = [newAddr, ..._addresses];
        _selectedAddressId = newAddr.id;
        _showAddressForm = false;
        _name.clear();
        _phone.clear();
        _line1.clear();
        _city.clear();
        _state.clear();
        _pin.clear();
      });
    } catch (e) {
      _toast(e.toString());
    }
  }

  Future<void> _placeOrder() async {
    if (_selectedAddressId == null && !_showAddressForm) {
      _toast('Please select a delivery address');
      return;
    }
    final address = _addresses.firstWhere((a) => a.id == _selectedAddressId);
    setState(() => _placing = true);
    try {
      final res = await _api.createOrder(
        shippingAddress: address.toJson(),
        paymentMethod: _paymentMethod,
        couponCode: _couponCtrl.text.trim().isEmpty
            ? null
            : _couponCtrl.text.trim(),
      );
      final orderData = (res['data'] as Map<String, dynamic>?) ?? {};
      final orderNum = orderData['order_number'] as String? ?? '';
      if (mounted) {
        context.read<AppProvider>().refreshCartCount();
        Nav.toOrderPlaced(context, orderNum);
      }
    } catch (e) {
      _toast(e.toString());
    } finally {
      if (mounted) setState(() => _placing = false);
    }
  }

  void _toast(String msg, {bool success = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        behavior: SnackBarBehavior.floating,
        backgroundColor: success ? AppTheme.success : AppTheme.danger,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: LoadingOverlay(
        isLoading: _placing,
        child: _body(),
      ),
      bottomNavigationBar: _cart != null && _cart!.items.isNotEmpty
          ? _placeOrderBar()
          : null,
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return ErrorState(message: _error!, onRetry: _load);
    }
    if (_cart == null || _cart!.items.isEmpty) {
      return const EmptyState(
        icon: Icons.shopping_cart_outlined,
        title: 'Cart is empty',
        subtitle: 'Add items before checking out.',
      );
    }
    return ListView(
      padding: const EdgeInsets.all(16).copyWith(bottom: 120),
      children: [
        _sectionTitle('Delivery Address'),
        if (_addresses.isEmpty && !_showAddressForm)
          _addAddressPrompt()
        else if (_showAddressForm)
          _addressForm()
        else
          ..._addresses.map(_addressTile),
        if (!_showAddressForm)
          TextButton.icon(
            onPressed: () =>
                setState(() => _showAddressForm = true),
            icon: const Icon(Icons.add_location_alt_rounded, size: 18),
            label: const Text('Add new address'),
          ),
        const SizedBox(height: 16),
        _sectionTitle('Order Summary'),
        ..._cart!.items.map((item) => ListTile(
              dense: true,
              contentPadding: EdgeInsets.zero,
              leading: Text('${item.quantity}×',
                  style: const TextStyle(fontWeight: FontWeight.w600)),
              title: Text(item.product.name,
                  maxLines: 1, overflow: TextOverflow.ellipsis),
              trailing: Text(Format.currency(item.lineTotal),
                  style: const TextStyle(fontWeight: FontWeight.w600)),
            )),
        const Divider(height: 24),
        _totalsRow('Subtotal', _cart!.subtotal),
        if (_cart!.summary.discount > 0)
          _totalsRow('Discount', -_cart!.summary.discount,
              color: AppTheme.success),
        _totalsRow('Shipping', _cart!.summary.shipping,
            free: _cart!.summary.shipping == 0),
        const Divider(height: 24),
        _totalsRow('Total', _cart!.summary.total, bold: true),
        const SizedBox(height: 16),
        _sectionTitle('Coupon Code'),
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: _couponCtrl,
                decoration: const InputDecoration(
                  hintText: 'Enter coupon code',
                  prefixIcon: Icon(Icons.local_offer_outlined, size: 20),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        _sectionTitle('Payment Method'),
        ..._payments.map((p) => RadioListTile<String>(
              value: p.$1,
              groupValue: _paymentMethod,
              activeColor: AppTheme.primary,
              onChanged: (v) => setState(() => _paymentMethod = v!),
              title: Text(p.$2),
              secondary: Icon(p.$3, color: AppTheme.textSecondary),
            )),
        const SizedBox(height: 24),
      ],
    );
  }

  Widget _sectionTitle(String t) => Padding(
        padding: const EdgeInsets.only(top: 4, bottom: 10),
        child: Text(t,
            style: const TextStyle(
                fontSize: 16, fontWeight: FontWeight.bold)),
      );

  Widget _addAddressPrompt() => Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFFF7F8FA),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            const Icon(Icons.location_off_outlined,
                color: AppTheme.textHint, size: 32),
            const SizedBox(height: 8),
            const Text('No addresses saved yet.',
                style: TextStyle(
                    fontSize: 13, color: AppTheme.textSecondary)),
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: () => setState(() => _showAddressForm = true),
              icon: const Icon(Icons.add_location_alt_rounded, size: 18),
              label: const Text('Add Address'),
            ),
          ],
        ),
      );

  Widget _addressTile(Address a) {
    final selected = a.id == _selectedAddressId;
    return Card(
      color: selected ? AppTheme.primaryLight.withValues(alpha: 0.3) : Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
            color: selected ? AppTheme.primary : Colors.transparent,
            width: 1.5),
      ),
      child: RadioListTile<int>(
        value: a.id,
        groupValue: _selectedAddressId,
        activeColor: AppTheme.primary,
        onChanged: (v) => setState(() => _selectedAddressId = v),
        title: Text(a.name,
            style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text('${a.fullAddress}\n${a.phone}',
            style: const TextStyle(fontSize: 13)),
        isThreeLine: true,
      ),
    );
  }

  Widget _addressForm() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _f(_name, 'Full Name'),
            const SizedBox(height: 10),
            _f(_phone, 'Phone', keyboard: TextInputType.phone),
            const SizedBox(height: 10),
            _f(_line1, 'Address Line'),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(child: _f(_city, 'City')),
                const SizedBox(width: 10),
                Expanded(child: _f(_state, 'State')),
              ],
            ),
            const SizedBox(height: 10),
            _f(_pin, 'Pincode', keyboard: TextInputType.number),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () =>
                        setState(() => _showAddressForm = false),
                    child: const Text('Cancel'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _saveNewAddress,
                    child: const Text('Save'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _f(TextEditingController c, String label,
          {TextInputType? keyboard}) =>
      TextField(
        controller: c,
        keyboardType: keyboard,
        decoration: InputDecoration(
          labelText: label,
          isDense: true,
        ),
      );

  Widget _totalsRow(String label, num amount,
      {bool bold = false, bool free = false, Color? color}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                  fontWeight: bold ? FontWeight.bold : FontWeight.normal,
                  color: AppTheme.textSecondary,
                  fontSize: bold ? 16 : 14)),
          Text(
            free ? 'FREE' : Format.currency(amount),
            style: TextStyle(
                fontWeight: bold ? FontWeight.bold : FontWeight.w600,
                color: color ??
                    (bold ? AppTheme.primary : AppTheme.textPrimary),
                fontSize: bold ? 18 : 14),
          ),
        ],
      ),
    );
  }

  Widget _placeOrderBar() {
    return SafeArea(
      child: Container(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 12,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Total',
                      style:
                          TextStyle(color: AppTheme.textSecondary)),
                  Text(
                    Format.currency(_cart!.summary.total),
                    style: const TextStyle(
                        fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 16),
            SizedBox(
              width: 180,
              child: GradientButton(
                label: 'Place Order',
                icon: Icons.check_circle_rounded,
                loading: _placing,
                onPressed: _placeOrder,
                height: 50,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
