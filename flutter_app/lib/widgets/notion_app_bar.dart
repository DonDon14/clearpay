import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../screens/profile_screen.dart';
import '../screens/contributions_screen.dart';
import '../screens/payment_history_screen.dart';
import '../screens/announcements_screen.dart';

class NotionAppBar extends StatefulWidget implements PreferredSizeWidget {
  final String title;
  final bool showNotifications;
  final bool showProfile;
  final VoidCallback? onRefresh;
  
  const NotionAppBar({
    super.key,
    required this.title,
    this.showNotifications = true,
    this.showProfile = true,
    this.onRefresh,
  });

  @override
  Size get preferredSize => const Size.fromHeight(56.0);

  @override
  State<NotionAppBar> createState() => _NotionAppBarState();
}

class _NotionAppBarState extends State<NotionAppBar> {
  bool _isNotificationOpen = false;
  bool _isProfileOpen = false;
  List<dynamic> _notifications = [];
  int _unreadCount = 0;
  bool _isLoadingNotifications = false;
  final GlobalKey _notificationKey = GlobalKey();
  final GlobalKey _profileKey = GlobalKey();

  @override
  void initState() {
    super.initState();
    if (widget.showNotifications) {
      _loadNotifications();
    }
  }

  Future<void> _loadNotifications() async {
    setState(() {
      _isLoadingNotifications = true;
    });

    try {
      final response = await ApiService.getNotifications();
      if (response['success'] == true) {
        setState(() {
          _notifications = response['activities'] ?? [];
          _unreadCount = _notifications.where((n) => n['is_unread'] == true || n['read_at'] == null).length;
        });
      }
    } catch (e) {
      // Handle error silently
    } finally {
      setState(() {
        _isLoadingNotifications = false;
      });
    }
  }

  Future<void> _markAsRead(int activityId) async {
    await ApiService.markNotificationRead(activityId);
    _loadNotifications();
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;
    
    return GestureDetector(
      onTap: () {
        // Close dropdowns when tapping outside
        if (_isNotificationOpen || _isProfileOpen) {
          setState(() {
            _isNotificationOpen = false;
            _isProfileOpen = false;
          });
        }
      },
      child: Stack(
        children: [
          AppBar(
          elevation: 0,
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.transparent,
          title: Text(
            widget.title,
            style: const TextStyle(
              color: Color(0xFF37352F),
              fontSize: 18,
              fontWeight: FontWeight.w600,
              letterSpacing: -0.3,
            ),
          ),
          actions: [
            // Notifications
            if (widget.showNotifications)
              Stack(
                key: _notificationKey,
                children: [
                  IconButton(
                    icon: Icon(
                      Icons.notifications_outlined,
                      color: const Color(0xFF37352F),
                      size: 22,
                    ),
                    onPressed: () {
                      setState(() {
                        _isNotificationOpen = !_isNotificationOpen;
                        _isProfileOpen = false;
                      });
                      if (_isNotificationOpen) {
                        _loadNotifications();
                      }
                    },
                  ),
                  if (_unreadCount > 0)
                    Positioned(
                      right: 8,
                      top: 8,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: Color(0xFFEF4444),
                          shape: BoxShape.circle,
                        ),
                        constraints: const BoxConstraints(
                          minWidth: 16,
                          minHeight: 16,
                        ),
                        child: Text(
                          _unreadCount > 9 ? '9+' : '$_unreadCount',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                    ),
                ],
              ),
            
            // Profile
            if (widget.showProfile)
              Container(
                margin: const EdgeInsets.only(right: 8),
                child: IconButton(
                  icon: CircleAvatar(
                    radius: 16,
                    backgroundColor: const Color(0xFFF1F1EF),
                    backgroundImage: user != null && user['profile_picture'] != null
                        ? NetworkImage('${ApiService.baseUrl}/${user['profile_picture']}')
                        : null,
                    child: user?['profile_picture'] == null
                        ? Text(
                            (user?['payer_name'] ?? 'U')[0].toUpperCase(),
                            style: const TextStyle(
                              color: Color(0xFF37352F),
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                            ),
                          )
                        : null,
                  ),
                  onPressed: () {
                    setState(() {
                      _isProfileOpen = !_isProfileOpen;
                      _isNotificationOpen = false;
                    });
                  },
                ),
              ),
          ],
        ),
        
        // Notification Dropdown
        if (_isNotificationOpen && widget.showNotifications)
          Positioned(
            top: 56,
            right: 8,
            child: GestureDetector(
              onTap: () {}, // Prevent closing when tapping inside
              child: _buildNotificationDropdown(),
            ),
          ),
        
        // Profile Dropdown
        if (_isProfileOpen && widget.showProfile)
          Positioned(
            top: 56,
            right: 8,
            child: GestureDetector(
              onTap: () {}, // Prevent closing when tapping inside
              child: _buildProfileDropdown(context, user),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationDropdown() {
    return Material(
      elevation: 8,
      borderRadius: BorderRadius.circular(8),
      color: Colors.white,
      child: Container(
        width: 360,
        constraints: const BoxConstraints(maxHeight: 400),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFFE9E9E7), width: 1),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: Color(0xFFE9E9E7), width: 1)),
              ),
              child: Row(
                children: [
                  const Text(
                    'Notifications',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF37352F),
                    ),
                  ),
                  const Spacer(),
                  IconButton(
                    icon: const Icon(Icons.close, size: 18),
                    onPressed: () {
                      setState(() {
                        _isNotificationOpen = false;
                      });
                    },
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                ],
              ),
            ),
            
            // Notifications List
            Flexible(
              child: _isLoadingNotifications
                  ? const Padding(
                      padding: EdgeInsets.all(40),
                      child: Center(child: CircularProgressIndicator()),
                    )
                  : _notifications.isEmpty
                      ? Padding(
                          padding: const EdgeInsets.all(40),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.notifications_none, size: 48, color: Colors.grey[400]),
                              const SizedBox(height: 16),
                              Text(
                                'No notifications',
                                style: TextStyle(color: Colors.grey[600], fontSize: 14),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          shrinkWrap: true,
                          itemCount: _notifications.length > 5 ? 5 : _notifications.length,
                          itemBuilder: (context, index) {
                            final notification = _notifications[index];
                            final isUnread = notification['is_unread'] == true || notification['read_at'] == null;
                            return _buildNotificationItem(notification, isUnread);
                          },
                        ),
            ),
            
            // Footer
            if (_notifications.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: const BoxDecoration(
                  border: Border(top: BorderSide(color: Color(0xFFE9E9E7), width: 1)),
                ),
                child: SizedBox(
                  width: double.infinity,
                  child: TextButton(
                    onPressed: () {
                      // Navigate to all notifications
                      Navigator.pushNamed(context, '/notifications');
                    },
                    child: const Text(
                      'View All',
                      style: TextStyle(
                        color: Color(0xFF37352F),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildNotificationItem(Map<String, dynamic> notification, bool isUnread) {
    final title = notification['title'] ?? 'Notification';
    final description = notification['description'] ?? '';
    final date = notification['created_at'] ?? notification['created_at_date'] ?? '';
    final icon = notification['activity_icon'] ?? Icons.info_outline;
    final color = notification['activity_color'] ?? 'blue';

    return InkWell(
      onTap: () {
        if (isUnread) {
          _markAsRead(notification['id']);
        }
        setState(() {
          _isNotificationOpen = false;
        });
      },
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isUnread ? const Color(0xFFF7F6F3) : Colors.white,
          border: const Border(bottom: BorderSide(color: Color(0xFFE9E9E7), width: 1)),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: _getColorFromString(color).withOpacity(0.1),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Icon(icon, size: 18, color: _getColorFromString(color)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: isUnread ? FontWeight.w600 : FontWeight.normal,
                      color: const Color(0xFF37352F),
                    ),
                  ),
                  if (description.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      description,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey[600],
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  if (date.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      _formatDate(date),
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[500],
                      ),
                    ),
                  ],
                ],
              ),
            ),
            if (isUnread)
              Container(
                width: 8,
                height: 8,
                decoration: const BoxDecoration(
                  color: Color(0xFFEF4444),
                  shape: BoxShape.circle,
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileDropdown(BuildContext context, Map<String, dynamic>? user) {
    return Material(
      elevation: 8,
      borderRadius: BorderRadius.circular(8),
      color: Colors.white,
      child: Container(
        width: 280,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFFE9E9E7), width: 1),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Profile Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: Color(0xFFE9E9E7), width: 1)),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: const Color(0xFFF1F1EF),
                    backgroundImage: user != null && user['profile_picture'] != null
                        ? NetworkImage('${ApiService.baseUrl}/${user['profile_picture']}')
                        : null,
                    child: user?['profile_picture'] == null
                        ? Text(
                            (user?['payer_name'] ?? 'U')[0].toUpperCase(),
                            style: const TextStyle(
                              color: Color(0xFF37352F),
                              fontSize: 18,
                              fontWeight: FontWeight.w500,
                            ),
                          )
                        : null,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user?['payer_name'] ?? 'User',
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF37352F),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          user?['email_address'] ?? user?['email'] ?? '',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey[600],
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'ID: ${user?['payer_id'] ?? 'N/A'}',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[500],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            
            // Menu Items
            _buildProfileMenuItem(
              icon: Icons.person_outline,
              title: 'My Data',
              subtitle: 'View your information',
              onTap: () {
                setState(() {
                  _isProfileOpen = false;
                });
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const ProfileScreen()),
                );
              },
            ),
            _buildProfileMenuItem(
              icon: Icons.handshake_outlined,
              title: 'Contributions',
              subtitle: 'View active contributions',
              onTap: () {
                setState(() {
                  _isProfileOpen = false;
                });
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const ContributionsScreen()),
                );
              },
            ),
            _buildProfileMenuItem(
              icon: Icons.history,
              title: 'Payment History',
              subtitle: 'View all transactions',
              onTap: () {
                setState(() {
                  _isProfileOpen = false;
                });
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
                );
              },
            ),
            _buildProfileMenuItem(
              icon: Icons.help_outline,
              title: 'Help & Support',
              subtitle: 'Get assistance',
              onTap: () {
                setState(() {
                  _isProfileOpen = false;
                });
                // TODO: Navigate to help screen
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Help & Support coming soon')),
                );
              },
            ),
            
            const Divider(height: 1, color: Color(0xFFE9E9E7)),
            
            // Sign Out
            InkWell(
              onTap: () async {
                setState(() {
                  _isProfileOpen = false;
                });
                final authProvider = Provider.of<AuthProvider>(context, listen: false);
                await authProvider.logout();
                if (context.mounted) {
                  Navigator.of(context).pushReplacementNamed('/login');
                }
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                child: Row(
                  children: [
                    Icon(Icons.logout, size: 20, color: Colors.red[600]),
                    const SizedBox(width: 12),
                    const Text(
                      'Sign Out',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF37352F),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileMenuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          children: [
            Icon(icon, size: 20, color: const Color(0xFF37352F)),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF37352F),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            ),
            Icon(Icons.chevron_right, size: 18, color: Colors.grey[400]),
          ],
        ),
      ),
    );
  }

  Color _getColorFromString(String color) {
    switch (color.toLowerCase()) {
      case 'blue':
        return const Color(0xFF2196F3);
      case 'green':
        return const Color(0xFF4CAF50);
      case 'orange':
        return const Color(0xFFFF9800);
      case 'red':
        return const Color(0xFFEF4444);
      default:
        return const Color(0xFF2196F3);
    }
  }

  String _formatDate(String dateString) {
    if (dateString.isEmpty) return '';
    try {
      final date = DateTime.parse(dateString);
      final now = DateTime.now();
      final difference = now.difference(date);
      
      if (difference.inDays == 0) {
        if (difference.inHours == 0) {
          if (difference.inMinutes == 0) {
            return 'Just now';
          }
          return '${difference.inMinutes}m ago';
        }
        return '${difference.inHours}h ago';
      } else if (difference.inDays == 1) {
        return 'Yesterday';
      } else if (difference.inDays < 7) {
        return '${difference.inDays}d ago';
      } else {
        return DateFormat('MMM dd, yyyy').format(date);
      }
    } catch (e) {
      return dateString;
    }
  }
}
