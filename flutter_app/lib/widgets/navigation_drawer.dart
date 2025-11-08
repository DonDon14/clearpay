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
import '../screens/help_screen.dart';
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
    
    // Web portal color scheme
    const primaryBlue = Color(0xFF3B82F6);
    const darkGray = Color(0xFF1F2937);
    const mediumGray = Color(0xFF6B7280);
    const activeBackground = Color(0xFFEFF6FF);
    const lightGray = Color(0xFFF3F4F6);
    
    return Drawer(
      width: 260, // Matching web portal sidebar width
      backgroundColor: Colors.white,
      child: Column(
        children: [
          // Sidebar Header with Logo (matching web portal)
          Container(
            height: 70,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            decoration: const BoxDecoration(
              border: Border(
                bottom: BorderSide(color: Color(0xFFE5E7EB), width: 1),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Logo - ClearPay (matching web portal)
                GestureDetector(
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
                    );
                  },
                  child: Row(
                    children: [
                      Icon(
                        Icons.credit_card,
                        color: primaryBlue,
                        size: 24,
                      ),
                      const SizedBox(width: 10),
                      const Text(
                        'ClearPay',
                        style: TextStyle(
                          color: darkGray,
                          fontSize: 20,
                          fontWeight: FontWeight.w600,
                          letterSpacing: -0.025,
                        ),
                      ),
                    ],
                  ),
                ),
                // Close button (mobile)
                IconButton(
                  icon: const Icon(Icons.close, color: mediumGray, size: 20),
                  onPressed: () => Navigator.pop(context),
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
                ),
              ],
            ),
          ),
          
          // Profile Section (styled to match web portal design)
          Container(
            padding: const EdgeInsets.all(20),
            decoration: const BoxDecoration(
              border: Border(
                bottom: BorderSide(color: Color(0xFFE5E7EB), width: 1),
              ),
            ),
            child: Row(
              children: [
                // Profile Picture
                CircleAvatar(
                  radius: 24,
                  backgroundColor: lightGray,
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
                          style: TextStyle(
                            color: primaryBlue,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        )
                      : null,
                ),
                const SizedBox(width: 12),
                // User Info
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        userName,
                        style: const TextStyle(
                          color: darkGray,
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        userEmail,
                        style: TextStyle(
                          color: mediumGray,
                          fontSize: 13,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Navigation Items - Matching web portal order
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
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
                  icon: Icons.account_circle,
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
                // Divider (matching web portal)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 8),
                  child: Divider(
                    color: Color(0xFFE5E7EB),
                    height: 1,
                    thickness: 1,
                  ),
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
          
          // Footer with Help & Support (matching web portal)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: const BoxDecoration(
              border: Border(
                top: BorderSide(color: Color(0xFFE5E7EB), width: 1),
              ),
            ),
            child: _buildDrawerItem(
              context,
              icon: Icons.help_outline,
              title: 'Help & Support',
              isActive: _isScreenActive(context, HelpScreen),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const HelpScreen()),
                );
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
        HelpScreen: '/help',
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
    return _DrawerItem(
      icon: icon,
      title: title,
      onTap: onTap,
      isActive: isActive,
    );
  }
}

// Separate widget to handle hover state
class _DrawerItem extends StatefulWidget {
  final IconData icon;
  final String title;
  final VoidCallback onTap;
  final bool isActive;

  const _DrawerItem({
    required this.icon,
    required this.title,
    required this.onTap,
    required this.isActive,
  });

  @override
  State<_DrawerItem> createState() => _DrawerItemState();
}

class _DrawerItemState extends State<_DrawerItem> {
  bool _isHovered = false;

  // Web portal color scheme
  static const primaryBlue = Color(0xFF3B82F6);
  static const mediumGray = Color(0xFF6B7280);
  static const activeBackground = Color(0xFFEFF6FF);
  static const hoverBackground = Color(0xFFF9FAFB);

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => setState(() => _isHovered = true),
      onExit: (_) => setState(() => _isHovered = false),
      child: InkWell(
        onTap: widget.onTap,
        borderRadius: BorderRadius.circular(8),
        child: Container(
          margin: const EdgeInsets.symmetric(vertical: 4),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: widget.isActive
                ? activeBackground
                : _isHovered
                    ? hoverBackground
                    : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
            border: widget.isActive
                ? const Border(
                    left: BorderSide(
                      color: primaryBlue,
                      width: 3,
                    ),
                  )
                : null,
          ),
          child: Row(
            children: [
              Icon(
                widget.icon,
                color: widget.isActive ? primaryBlue : mediumGray,
                size: 16,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  widget.title,
                  style: TextStyle(
                    color: widget.isActive ? primaryBlue : mediumGray,
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

