import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/dashboard_provider.dart';
import '../screens/main_navigation_screen.dart';
import '../screens/contributions_screen.dart';
import '../screens/payment_requests_screen.dart';
import '../screens/refund_requests_screen.dart';
import '../screens/payment_history_screen.dart';
import '../screens/announcements_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/help_screen.dart';
import '../screens/login_screen.dart';
import '../utils/logo_helper.dart';
import '../services/api_service.dart';

class AppNavigationDrawer extends StatelessWidget {
  const AppNavigationDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    // Listen to dashboard provider to update when dashboard data loads
    final dashboardProvider = Provider.of<DashboardProvider>(context);
    final user = authProvider.user;
    
    // Get user info - prioritize dashboard data if available (more up-to-date)
    final payerData = dashboardProvider.dashboardData?.payer;
    final userName = payerData?['payer_name'] ?? 
                     payerData?['name'] ?? 
                     user?['payer_name'] ?? 
                     user?['name'] ?? 
                     'User';
    final userEmail = payerData?['email_address'] ?? 
                      payerData?['email'] ?? 
                      user?['email_address'] ?? 
                      user?['email'] ?? '';
    
    // Get profile picture - check multiple sources in priority order
    // 1. Dashboard data (normalized, most up-to-date)
    // 2. User data from AuthProvider (from login or merged from dashboard)
    // 3. Check if dashboard is still loading and user data has profile picture
    String? profilePicture;
    
    if (payerData != null && payerData['profile_picture'] != null) {
      // Dashboard data available
      final dashPic = payerData['profile_picture'];
      if (dashPic.toString().trim().isNotEmpty && dashPic.toString().trim().toLowerCase() != 'null') {
        profilePicture = dashPic.toString();
        debugPrint('Navigation Drawer - Profile Picture from Dashboard: $profilePicture');
      }
    }
    
    // Fallback to user data if dashboard doesn't have it
    if ((profilePicture == null || profilePicture.isEmpty) && user != null) {
      final userPic = user['profile_picture'];
      if (userPic != null && userPic.toString().trim().isNotEmpty && userPic.toString().trim().toLowerCase() != 'null') {
        profilePicture = userPic.toString();
        debugPrint('Navigation Drawer - Profile Picture from User data: $profilePicture');
      }
    }
    
    // Final debug output
    if (profilePicture == null || profilePicture.isEmpty) {
      debugPrint('Navigation Drawer - Profile Picture is NULL or empty');
      debugPrint('Navigation Drawer - Dashboard loaded: ${dashboardProvider.hasData}');
      debugPrint('Navigation Drawer - Dashboard loading: ${dashboardProvider.isLoading}');
      debugPrint('Navigation Drawer - User data: ${user != null ? "Available" : "NULL"}');
      if (payerData != null) {
        debugPrint('Navigation Drawer - Payer data keys: ${payerData.keys.toList()}');
        debugPrint('Navigation Drawer - Payer profile_picture value: ${payerData['profile_picture']}');
      }
    }
    
    // Web portal color scheme
    const primaryBlue = Color(0xFF3B82F6);
    const darkGray = Color(0xFF1F2937);
    const mediumGray = Color(0xFF6B7280);
    const activeBackground = Color(0xFFEFF6FF);
    const lightGray = Color(0xFFF3F4F6);
    
    return Drawer(
      width: 260, // Matching web portal sidebar width
      backgroundColor: Colors.white,
      child: SafeArea(
        child: Column(
          children: [
            // Sidebar Header with Logo (matching web portal)
            Container(
              height: 90,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              decoration: const BoxDecoration(
                border: Border(
                  bottom: BorderSide(color: Color(0xFFE5E7EB), width: 1),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Logo - ClearPay (matching web portal) - Using official logo
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
                        // Official Logo Image
                        Image.network(
                          LogoHelper.getLogoUrl(),
                          width: 32,
                          height: 32,
                          fit: BoxFit.contain,
                          errorBuilder: (context, error, stackTrace) {
                            // If official logo fails, show icon fallback (no text duplication)
                            return Icon(
                              Icons.credit_card,
                              color: primaryBlue,
                              size: 32,
                            );
                          },
                          loadingBuilder: (context, child, loadingProgress) {
                            if (loadingProgress == null) return child;
                            // Show minimal loading indicator
                            return SizedBox(
                              width: 32,
                              height: 32,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(primaryBlue),
                                value: loadingProgress.expectedTotalBytes != null
                                    ? loadingProgress.cumulativeBytesLoaded /
                                        loadingProgress.expectedTotalBytes!
                                    : null,
                              ),
                            );
                          },
                        ),
                        const SizedBox(width: 10),
                        // ClearPay Text (always shown, matching web portal)
                        const Text(
                          'ClearPay',
                          style: TextStyle(
                            color: darkGray,
                            fontSize: 18,
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
                  backgroundImage: profilePicture != null && 
                      profilePicture.toString().trim().isNotEmpty &&
                      profilePicture.toString().trim().toLowerCase() != 'null'
                      ? NetworkImage(
                          _getProfilePictureUrl(profilePicture.toString()),
                          headers: {
                            'Accept': 'image/*',
                          },
                        )
                      : null,
                  onBackgroundImageError: (exception, stackTrace) {
                    // Log error for debugging
                    debugPrint('Profile picture load error: $exception');
                    debugPrint('Stack trace: $stackTrace');
                  },
                  child: profilePicture == null || 
                      profilePicture.toString().trim().isEmpty ||
                      profilePicture.toString().trim().toLowerCase() == 'null'
                      ? Text(
                          userName.isNotEmpty ? userName[0].toUpperCase() : 'U',
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
            child: Column(
              children: [
                _buildDrawerItem(
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
                const SizedBox(height: 8),
                // Divider before logout
                const Divider(height: 1, color: Color(0xFFE5E7EB)),
                const SizedBox(height: 8),
                // Logout button
                _buildLogoutItem(context),
                // Add bottom padding to ensure logout is above system navigation
                SizedBox(height: MediaQuery.of(context).padding.bottom),
              ],
            ),
          ),
        ],
        ),
      ),
    );
  }

  String _getProfilePictureUrl(String profilePicture) {
    // Handle different profile picture formats from database
    String picturePath = profilePicture.trim();
    
    // Debug
    debugPrint('_getProfilePictureUrl - Input: $picturePath');
    
    // If already a full URL, return as is
    if (picturePath.startsWith('http://') || picturePath.startsWith('https://')) {
      debugPrint('_getProfilePictureUrl - Already full URL: $picturePath');
      return picturePath;
    }
    
    // Remove leading slash if present
    if (picturePath.startsWith('/')) {
      picturePath = picturePath.substring(1);
    }
    
    // Extract subfolder and filename from path (e.g., "uploads/profile/filename.png")
    // The path should be in format: "uploads/profile/filename.png" or "profile/filename.png"
    String subfolder = 'profile';
    String filename = '';
    
    if (picturePath.contains('/')) {
      final parts = picturePath.split('/');
      // Find the subfolder (profile, payment_proofs, etc.)
      if (parts.contains('profile')) {
        subfolder = 'profile';
        filename = parts.last;
      } else if (parts.contains('payment_proofs')) {
        subfolder = 'payment_proofs';
        filename = parts.last;
      } else if (parts.contains('payment_methods')) {
        subfolder = 'payment_methods';
        filename = parts.last;
      } else if (parts.contains('qr_receipts')) {
        subfolder = 'qr_receipts';
        filename = parts.last;
      } else {
        // Fallback: assume profile if path contains profile in any part
        filename = parts.last;
        // Try to find subfolder
        for (final part in parts) {
          if (part == 'profile' || part == 'payment_proofs' || part == 'payment_methods' || part == 'qr_receipts') {
            subfolder = part;
            break;
          }
        }
      }
    } else {
      // Just filename, assume profile folder
      filename = picturePath;
    }
    
    // Construct full URL using ImageController (ensures CORS headers)
    // This route goes through CodeIgniter and will have proper CORS headers
    String baseUrl = ApiService.baseUrl;
    if (baseUrl.endsWith('/')) {
      baseUrl = baseUrl.substring(0, baseUrl.length - 1);
    }
    
    // Use ImageController route for CORS-enabled image serving
    final fullUrl = '$baseUrl/uploads/$subfolder/$filename';
    debugPrint('_getProfilePictureUrl - Constructed URL (via ImageController): $fullUrl');
    return fullUrl;
  }

  Widget _buildLogoutItem(BuildContext context) {
    const errorRed = Color(0xFFDC2626);
    const hoverBackground = Color(0xFFF9FAFB);

    return StatefulBuilder(
      builder: (context, setState) {
        bool _isHovered = false;
        return MouseRegion(
          onEnter: (_) => setState(() => _isHovered = true),
          onExit: (_) => setState(() => _isHovered = false),
          child: InkWell(
            onTap: () async {
              // Show confirmation dialog
              final shouldLogout = await showDialog<bool>(
                context: context,
                builder: (context) => AlertDialog(
                  title: const Text('Logout'),
                  content: const Text('Are you sure you want to logout?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.of(context).pop(false),
                      child: const Text('Cancel'),
                    ),
                    TextButton(
                      onPressed: () => Navigator.of(context).pop(true),
                      style: TextButton.styleFrom(
                        foregroundColor: errorRed,
                      ),
                      child: const Text('Logout'),
                    ),
                  ],
                ),
              );

              if (shouldLogout == true && context.mounted) {
                // Close drawer first
                Navigator.pop(context);
                
                // Logout from AuthProvider
                final authProvider = Provider.of<AuthProvider>(context, listen: false);
                await authProvider.logout();
                
                // Navigate to login screen
                if (context.mounted) {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (route) => false,
                  );
                }
              }
            },
            borderRadius: BorderRadius.circular(8),
            child: Container(
              margin: const EdgeInsets.symmetric(vertical: 4),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: _isHovered ? hoverBackground : Colors.transparent,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.logout,
                    color: errorRed,
                    size: 16,
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Text(
                      'Logout',
                      style: TextStyle(
                        color: errorRed,
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
      },
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

