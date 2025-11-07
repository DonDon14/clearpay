import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../screens/main_navigation_screen.dart';
import '../screens/contributions_screen.dart';
import '../screens/payment_requests_screen.dart';
import '../screens/refund_requests_screen.dart';
import '../screens/payment_history_screen.dart';
import '../screens/announcements_screen.dart';
import '../screens/profile_screen.dart';
import '../services/api_service.dart';

class AppNavigationDrawer extends StatelessWidget {
  const AppNavigationDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;
    
    // Get user info
    final userName = user?['payer_name'] ?? user?['name'] ?? 'User';
    final userEmail = user?['email_address'] ?? user?['email'] ?? '';
    final profilePicture = user?['profile_picture'];
    
    return Drawer(
      backgroundColor: const Color(0xFF1A2F4A), // Dark blue background matching reference
      child: Column(
        children: [
          // ClearPay Logo and Text at the top
          Container(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.credit_card,
                  color: Colors.white,
                  size: 24,
                ),
                const SizedBox(width: 8),
                const Text(
                  'ClearPay',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
          // Divider after logo
          Divider(
            color: Colors.white.withOpacity(0.2),
            height: 1,
            thickness: 1,
            indent: 20,
            endIndent: 20,
          ),
          // Header Section with Profile
          Container(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
            child: Row(
              children: [
                // Profile Picture
                CircleAvatar(
                  radius: 30,
                  backgroundColor: Colors.white.withOpacity(0.2),
                  backgroundImage: profilePicture != null && profilePicture.toString().isNotEmpty
                      ? NetworkImage(
                          profilePicture.toString().startsWith('http')
                              ? profilePicture.toString()
                              : '${ApiService.baseUrl}/${profilePicture.toString()}'
                        )
                      : null,
                  child: profilePicture == null || profilePicture.toString().isEmpty
                      ? Text(
                          userName[0].toUpperCase(),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        )
                      : null,
                ),
                const SizedBox(width: 16),
                // User Info
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        userName,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        userEmail,
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.8),
                          fontSize: 14,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                // Close button
                IconButton(
                  icon: const Icon(Icons.arrow_back_ios, color: Colors.white, size: 20),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ),
          
          // Navigation Items - Matching web portal order
          Expanded(
            child: ListView(
              padding: EdgeInsets.zero,
              children: [
                _buildDrawerItem(
                  context,
                  icon: Icons.home,
                  title: 'Dashboard',
                  isActive: _isScreenActive(context, MainNavigationScreen),
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
                    );
                  },
                ),
                _buildDrawerItem(
                  context,
                  icon: Icons.person,
                  title: 'My Data',
                  isActive: _isScreenActive(context, ProfileScreen),
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const ProfileScreen()),
                    );
                  },
                ),
                _buildDrawerItem(
                  context,
                  icon: Icons.campaign,
                  title: 'Announcements',
                  isActive: _isScreenActive(context, AnnouncementsScreen),
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const AnnouncementsScreen()),
                    );
                  },
                ),
                _buildDrawerItem(
                  context,
                  icon: Icons.account_balance_wallet,
                  title: 'Contributions',
                  isActive: _isScreenActive(context, ContributionsScreen),
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const ContributionsScreen()),
                    );
                  },
                ),
                // Divider
                const Divider(
                  color: Colors.white24,
                  height: 32,
                  thickness: 1,
                ),
                _buildDrawerItem(
                  context,
                  icon: Icons.history,
                  title: 'Payment History',
                  isActive: _isScreenActive(context, PaymentHistoryScreen),
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
                    );
                  },
                ),
                _buildDrawerItem(
                  context,
                  icon: Icons.send,
                  title: 'Payment Requests',
                  isActive: _isScreenActive(context, PaymentRequestsScreen),
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const PaymentRequestsScreen()),
                    );
                  },
                ),
                _buildDrawerItem(
                  context,
                  icon: Icons.undo,
                  title: 'Refund Requests',
                  isActive: _isScreenActive(context, RefundRequestsScreen),
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const RefundRequestsScreen()),
                    );
                  },
                ),
              ],
            ),
          ),
          
          // Log Out Button
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              border: Border(
                top: BorderSide(
                  color: Colors.white.withOpacity(0.2),
                  width: 1,
                ),
              ),
            ),
            child: _buildDrawerItem(
              context,
              icon: Icons.logout,
              title: 'Log Out',
              onTap: () async {
                Navigator.pop(context);
                await ApiService.logout();
                if (context.mounted) {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
                  );
                }
              },
            ),
          ),
        ],
      ),
    );
  }

  bool _isRouteActive(BuildContext context, String routeName) {
    final currentRoute = ModalRoute.of(context)?.settings.name;
    if (currentRoute == null) {
      // Check by comparing route paths
      final navigator = Navigator.of(context);
      final currentRouteSettings = navigator.widget.initialRoute;
      // For MaterialPageRoute, we'll check the route class name
      return false;
    }
    return currentRoute == routeName;
  }

  bool _isScreenActive(BuildContext context, Type screenType) {
    // Get the current route
    final route = ModalRoute.of(context);
    if (route == null) return false;
    
    // Check if the current route's settings name matches
    final routeName = route.settings.name;
    if (routeName != null) {
      // Map screen types to route names
      final routeMap = {
        MainNavigationScreen: '/dashboard',
        ProfileScreen: '/profile',
        AnnouncementsScreen: '/announcements',
        ContributionsScreen: '/contributions',
        PaymentHistoryScreen: '/payment-history',
        PaymentRequestsScreen: '/payment-requests',
        RefundRequestsScreen: '/refund-requests',
      };
      return routeMap[screenType] == routeName;
    }
    
    // Fallback: Try to check the route's widget type
    // This works by checking if the route is a MaterialPageRoute and comparing types
    if (route is MaterialPageRoute) {
      try {
        // Get the route's builder and check the widget type
        // We'll use a try-catch to safely check the widget type
        final currentWidget = route.builder(context);
        return currentWidget.runtimeType == screenType;
      } catch (e) {
        // If we can't determine, return false
        return false;
      }
    }
    
    return false;
  }

  Widget _buildDrawerItem(
    BuildContext context, {
    required IconData icon,
    required String title,
    required VoidCallback onTap,
    bool isActive = false,
  }) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: isActive ? Colors.white.withOpacity(0.15) : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
      ),
      child: ListTile(
        leading: Icon(
          icon,
          color: isActive ? Colors.white : Colors.white.withOpacity(0.8),
          size: 24,
        ),
        title: Text(
          title,
          style: TextStyle(
            color: isActive ? Colors.white : Colors.white.withOpacity(0.8),
            fontSize: 16,
            fontWeight: isActive ? FontWeight.w600 : FontWeight.w500,
          ),
        ),
        onTap: onTap,
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    );
  }
}

