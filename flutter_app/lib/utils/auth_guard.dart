import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_provider.dart';
import '../screens/auth/login_screen.dart';
import '../theme/app_theme.dart';

/// Guards an action behind authentication. If the user is not logged in,
/// shows a bottom-sheet prompt offering to log in or register. Returns true
/// only if the user was already authenticated (the caller may then proceed).
///
/// Usage:
/// ```
/// if (!await AuthGuard.ensure(context)) return;
/// // proceed with authenticated action
/// ```
class AuthGuard {
  AuthGuard._();

  static Future<bool> ensure(BuildContext context) async {
    final app = context.read<AppProvider>();
    if (app.isLoggedIn) return true;

    await showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetCtx) => Padding(
        padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 48,
              height: 4,
              decoration: BoxDecoration(
                color: const Color(0xFFE0E3E8),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 20),
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                gradient: AppTheme.primaryGradient,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(Icons.lock_outline_rounded,
                  color: Colors.white, size: 36),
            ),
            const SizedBox(height: 18),
            const Text(
              'Sign in required',
              style: TextStyle(
                fontSize: 19,
                fontWeight: FontWeight.bold,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              'Please log in or create an account to continue.',
              textAlign: TextAlign.center,
              style:
                  TextStyle(fontSize: 14, color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(sheetCtx);
                  Navigator.push(context,
                      MaterialPageRoute(builder: (_) => const LoginScreen()));
                },
                child: const Text('Log In'),
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: () {
                  Navigator.pop(sheetCtx);
                  Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) => const LoginScreen()));
                },
                child: const Text('Create Account'),
              ),
            ),
          ],
        ),
      ),
    );
    return false;
  }
}
