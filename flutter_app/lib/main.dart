import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/login_screen.dart';
import 'screens/main_navigation_screen.dart';
import 'screens/splash_screen.dart';
import 'providers/auth_provider.dart';
import 'providers/dashboard_provider.dart';
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
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => DashboardProvider()),
      ],
      child: MaterialApp(
        navigatorKey: ModalService.navigatorKey,
        title: 'ClearPay Payer',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
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
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(3),
              side: const BorderSide(
                color: Color(0xFFE9E9E7),
                width: 1,
              ),
            ),
            color: Colors.white,
          ),
          dividerTheme: const DividerThemeData(
            color: Color(0xFFE9E9E7),
            thickness: 1,
            space: 1,
          ),
        ),
        home: const SplashScreen(child: AuthWrapper()),
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

