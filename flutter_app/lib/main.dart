import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import 'providers/app_provider.dart';
import 'screens/main_shell.dart';
import 'screens/auth/splash_screen.dart';
import 'theme/app_theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ),
  );
  runApp(const CityMarketApp());
}

class CityMarketApp extends StatelessWidget {
  const CityMarketApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => AppProvider()..init(),
        ),
      ],
      child: MaterialApp(
        title: 'CityMarket',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: Consumer<AppProvider>(
          builder: (context, app, _) {
            if (!app.initialized) {
              return const SplashScreen();
            }
            return const MainShell();
          },
        ),
      ),
    );
  }
}
