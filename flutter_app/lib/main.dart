import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/login_screen.dart';
import 'screens/main_navigation_screen.dart';
import 'screens/splash_screen.dart';
import 'providers/auth_provider.dart';
import 'providers/dashboard_provider.dart';
import 'providers/theme_provider.dart';
import 'services/api_service.dart';
import 'services/modal_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize API service
  await ApiService.init();
  
  runApp(const ClearPayApp());
}

class ClearPayApp extends StatelessWidget {
  const ClearPayApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => DashboardProvider()),
      ],
      child: Consumer<ThemeProvider>(
        builder: (context, themeProvider, child) {
          return MaterialApp(
            navigatorKey: ModalService.navigatorKey,
            title: 'ClearPay Payer',
            debugShowCheckedModeBanner: false,
            themeMode: themeProvider.themeMode,
            theme: ThemeData(
              useMaterial3: true,
              brightness: Brightness.light,
              primarySwatch: Colors.blue,
              primaryColor: const Color(0xFF37352F),
              scaffoldBackgroundColor: const Color(0xFFFFFBFE),
              fontFamily: 'Inter',
              appBarTheme: const AppBarTheme(
                backgroundColor: Colors.white,
                foregroundColor: Color(0xFF37352F),
                elevation: 0,
                surfaceTintColor: Colors.transparent,
                iconTheme: IconThemeData(
                  color: Color(0xFF37352F),
                ),
              ),
              cardTheme: CardThemeData(
                elevation: 4, // Enhanced elevation for better 3D effect
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                  // No border - using elevation/shadow instead
                ),
                color: Colors.white,
                shadowColor: Colors.black.withOpacity(0.15),
              ),
              dividerTheme: const DividerThemeData(
                color: Color(0xFFE9E9E7),
                thickness: 1,
                space: 1,
              ),
              colorScheme: ColorScheme.light(
                primary: const Color(0xFF3B82F6),
                secondary: const Color(0xFF6B7280),
                surface: Colors.white,
                background: const Color(0xFFFFFBFE),
                error: const Color(0xFFDC2626),
                onPrimary: Colors.white,
                onSecondary: Colors.white,
                onSurface: const Color(0xFF37352F),
                onBackground: const Color(0xFF37352F),
                onError: Colors.white,
              ),
            ),
            darkTheme: ThemeData(
              useMaterial3: true,
              brightness: Brightness.dark,
              primarySwatch: Colors.blue,
              primaryColor: Colors.white,
              scaffoldBackgroundColor: const Color(0xFF121212), // Material dark background
              fontFamily: 'Inter',
              appBarTheme: const AppBarTheme(
                backgroundColor: Color(0xFF1E1E1E),
                foregroundColor: Colors.white,
                elevation: 0,
                surfaceTintColor: Colors.transparent,
                iconTheme: IconThemeData(
                  color: Colors.white,
                ),
              ),
              cardTheme: CardThemeData(
                elevation: 4, // More elevation in dark mode for better depth
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                  // No border - using elevation/shadow instead
                ),
                color: const Color(0xFF1E1E1E), // Slightly lighter than background
                shadowColor: Colors.black.withOpacity(0.5),
              ),
              dividerTheme: const DividerThemeData(
                color: Color(0xFF2C2C2C),
                thickness: 1,
                space: 1,
              ),
              colorScheme: ColorScheme.dark(
                primary: const Color(0xFF60A5FA),
                secondary: const Color(0xFF9CA3AF),
                surface: const Color(0xFF1E1E1E), // Card background
                surfaceVariant: const Color(0xFF2C2C2C), // For icon backgrounds, etc.
                background: const Color(0xFF121212), // Main background
                error: const Color(0xFFEF4444),
                onPrimary: Colors.white,
                onSecondary: Colors.white,
                onSurface: Colors.white, // Text on cards/surface
                onBackground: Colors.white, // Text on background
                onError: Colors.white,
              ),
            ),
            home: const SplashScreen(child: AuthWrapper()),
          );
        },
      ),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  bool _isLoading = true;
  bool _isAuthenticated = false;

  @override
  void initState() {
    super.initState();
    _checkAuthStatus();
  }

  Future<void> _checkAuthStatus() async {
    // Add a small delay to ensure splash screen is visible
    await Future.delayed(const Duration(milliseconds: 500));
    
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    final userId = prefs.getInt('user_id');
    
    if (mounted) {
      setState(() {
        _isAuthenticated = token != null && userId != null;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      // Don't show duplicate loading - splash screen already handles this
      // Return empty container to prevent duplication
      return const SizedBox.shrink();
    }

    if (_isAuthenticated) {
      // Load dashboard data when authenticated
      WidgetsBinding.instance.addPostFrameCallback((_) {
        Provider.of<DashboardProvider>(context, listen: false).loadDashboard();
      });
      return const MainNavigationScreen();
    } else {
      return const LoginScreen();
    }
  }
}

