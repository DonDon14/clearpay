import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'dart:html' as html;
import 'dart:typed_data';
import 'dart:async';
import '../providers/auth_provider.dart';
import '../providers/dashboard_provider.dart';
import '../services/api_service.dart';

class NotionAppBar extends StatefulWidget implements PreferredSizeWidget {
  final String title;
  final bool showNotifications;
  final VoidCallback? onRefresh;
  
  const NotionAppBar({
    super.key,
    required this.title,
    this.showNotifications = true,
    this.onRefresh,
  });

  @override
  Size get preferredSize => const Size.fromHeight(56.0);

  @override
  State<NotionAppBar> createState() => _NotionAppBarState();
}

class _NotionAppBarState extends State<NotionAppBar> {
  bool _isNotificationOpen = false;
  List<dynamic> _notifications = [];
  int _unreadCount = 0;
  bool _isLoadingNotifications = false;
  final GlobalKey _notificationKey = GlobalKey();
  OverlayEntry? _notificationOverlay;

  @override
  void initState() {
    super.initState();
    if (widget.showNotifications) {
      _loadNotifications();
    }
  }

  @override
  void dispose() {
    _notificationOverlay?.remove();
    super.dispose();
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
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;
    
    // Try to get user data from dashboard provider if available
    Map<String, dynamic>? effectiveUser = user;
    try {
      final dashboardProvider = Provider.of<DashboardProvider>(context, listen: false);
      if (dashboardProvider.dashboardData?.payer != null) {
        final payerData = dashboardProvider.dashboardData!.payer;
        if (payerData.isNotEmpty) {
          // Merge dashboard payer data with user data
          effectiveUser = Map<String, dynamic>.from(user ?? {});
          effectiveUser!.addAll({
            'payer_name': payerData['payer_name'] ?? effectiveUser['payer_name'],
            'payer_id': payerData['payer_id'] ?? effectiveUser['payer_id'],
            'email_address': payerData['email_address'] ?? effectiveUser['email_address'],
            'contact_number': payerData['contact_number'] ?? effectiveUser['contact_number'],
            'profile_picture': payerData['profile_picture'] ?? effectiveUser['profile_picture'],
          });
        }
      }
    } catch (e) {
      // DashboardProvider might not be available, use user data
      effectiveUser = user;
    }
    
    return Stack(
      clipBehavior: Clip.none,
      children: [
        AppBar(
          elevation: 0,
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.transparent,
          leading: Builder(
            builder: (BuildContext context) {
              return IconButton(
                icon: const Icon(Icons.menu, color: Color(0xFF37352F)),
                onPressed: () {
                  try {
                    Scaffold.of(context).openDrawer();
                  } catch (e) {
                    // If drawer is not available, try to find it in the widget tree
                    final scaffoldState = Scaffold.maybeOf(context);
                    if (scaffoldState != null) {
                      scaffoldState.openDrawer();
                    }
                  }
                },
              );
            },
          ),
          title: Text(
            widget.title,
            style: const TextStyle(
              color: Color(0xFF37352F),
              fontSize: 18,
              fontWeight: FontWeight.w600,
              letterSpacing: -0.3,
            ),
          ),
          centerTitle: true,
          actions: [
            // Notifications
            if (widget.showNotifications)
              Stack(
                key: _notificationKey,
                clipBehavior: Clip.none,
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
                      });
                      if (_isNotificationOpen) {
                        _loadNotifications();
                        _showNotificationOverlay();
                      } else {
                        _hideNotificationOverlay();
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
            
          ],
        ),
      ],
    );
  }

  Widget _buildNotificationDropdown() {
    return Material(
      elevation: 8,
      borderRadius: BorderRadius.circular(8),
      color: Colors.white,
      child: MouseRegion(
        cursor: SystemMouseCursors.click,
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
                  Material(
                    color: Colors.transparent,
                    child: InkWell(
                      onTap: () {
                        setState(() {
                          _isNotificationOpen = false;
                        });
                        _hideNotificationOverlay();
                      },
                      borderRadius: BorderRadius.circular(20),
                      child: Container(
                        width: 32,
                        height: 32,
                        alignment: Alignment.center,
                        child: const Icon(Icons.close, size: 18, color: Color(0xFF37352F)),
                      ),
                    ),
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
                      setState(() {
                        _isNotificationOpen = false;
                      });
                      _hideNotificationOverlay();
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
      ),
    );
  }

  Widget _buildNotificationItem(Map<String, dynamic> notification, bool isUnread) {
    final title = notification['title'] ?? 'Notification';
    final description = notification['description'] ?? '';
    final date = notification['created_at'] ?? notification['created_at_date'] ?? '';
    final icon = notification['activity_icon'] ?? Icons.info_outline;
    final color = notification['activity_color'] ?? 'blue';

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          if (isUnread) {
            _markAsRead(notification['id']);
          }
          setState(() {
            _isNotificationOpen = false;
          });
          _hideNotificationOverlay();
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

  void _showNotificationOverlay() {
    _hideNotificationOverlay();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final overlay = Overlay.of(context);
      final renderBox = context.findRenderObject() as RenderBox?;
      if (renderBox == null) return;
      
      final offset = renderBox.localToGlobal(Offset.zero);
      final screenSize = MediaQuery.of(context).size;
      
      _notificationOverlay = OverlayEntry(
        builder: (overlayContext) => Stack(
          children: [
            // Invisible backdrop to capture outside clicks
            Positioned.fill(
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    _isNotificationOpen = false;
                  });
                  _hideNotificationOverlay();
                },
                behavior: HitTestBehavior.translucent,
              ),
            ),
            // Dropdown positioned correctly
            Positioned(
              top: offset.dy + 56,
              right: 8,
              child: GestureDetector(
                onTap: () {}, // Prevent closing when tapping inside
                behavior: HitTestBehavior.opaque,
                child: _buildNotificationDropdown(),
              ),
            ),
          ],
        ),
      );
      overlay.insert(_notificationOverlay!);
    });
  }

  void _hideNotificationOverlay() {
    _notificationOverlay?.remove();
    _notificationOverlay = null;
  }

}
