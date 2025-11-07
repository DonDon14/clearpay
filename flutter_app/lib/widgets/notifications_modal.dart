import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../screens/announcements_screen.dart';
import '../screens/contributions_screen.dart';
import '../screens/payment_history_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/refund_requests_screen.dart';

class NotificationsModal extends StatefulWidget {
  const NotificationsModal({super.key});

  @override
  State<NotificationsModal> createState() => _NotificationsModalState();
}

class _NotificationsModalState extends State<NotificationsModal> {
  List<dynamic> _notifications = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAllNotifications();
  }

  Future<void> _loadAllNotifications() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await ApiService.getAllNotifications();
      if (response['success'] == true) {
        final activities = response['activities'] ?? [];
        // Process activities to ensure proper data structure
        final processedActivities = activities.map((n) {
          final activity = Map<String, dynamic>.from(n);
          // Ensure IDs are properly typed
          final id = activity['id'];
          if (id is String) {
            activity['id'] = int.tryParse(id) ?? 0;
          }
          final entityId = activity['entity_id'];
          if (entityId is String) {
            activity['entity_id'] = int.tryParse(entityId) ?? 0;
          }
          // Ensure is_unread is set correctly based on is_read_by_payer
          // Use the same robust parsing logic as notion_app_bar.dart
          final isReadValue = activity['is_read_by_payer'];
          final isRead = isReadValue is int 
              ? isReadValue 
              : (isReadValue is String 
                  ? int.tryParse(isReadValue) ?? 0 
                  : (isReadValue == true || isReadValue == '1' || isReadValue == 1 ? 1 : 0));
          activity['is_unread'] = isRead == 0;
          activity['is_read_by_payer'] = isRead; // Normalize the value
          // Also set read_at for compatibility
          activity['read_at'] = (isRead == 1) ? DateTime.now().toIso8601String() : null;
          return activity;
        }).toList();
        
        setState(() {
          _notifications = processedActivities;
        });
      } else {
        setState(() {
          _notifications = [];
        });
      }
    } catch (e) {
      setState(() {
        _notifications = [];
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _markAsRead(int activityId) async {
    // Optimistically update UI immediately
    setState(() {
      final index = _notifications.indexWhere((n) => n['id'] == activityId);
      if (index != -1) {
        _notifications[index] = Map<String, dynamic>.from(_notifications[index]);
        _notifications[index]['is_read_by_payer'] = 1;
        _notifications[index]['is_unread'] = false;
        _notifications[index]['read_at'] = DateTime.now().toIso8601String();
      }
    });
    
    // Mark as read on server (async)
    ApiService.markNotificationRead(activityId).then((_) {
      // Reload to sync with server
      if (mounted) {
        _loadAllNotifications();
      }
    }).catchError((e) {
      // If server call fails, reload to get correct state
      if (mounted) {
        _loadAllNotifications();
      }
    });
  }

  Future<void> _markAllAsRead() async {
    final unreadNotifications = _notifications.where((n) {
      // Use the same robust parsing logic as notion_app_bar.dart
      final isReadValue = n['is_read_by_payer'];
      final isRead = isReadValue is int 
          ? isReadValue 
          : (isReadValue is String 
              ? int.tryParse(isReadValue) ?? 0 
              : (isReadValue == true || isReadValue == '1' || isReadValue == 1 ? 1 : 0));
      return isRead == 0;
    }).toList();

    for (var notification in unreadNotifications) {
      await ApiService.markNotificationRead(notification['id']);
    }

    _loadAllNotifications();
  }

  IconData _getIconFromString(String iconString) {
    final iconLower = iconString.toLowerCase();
    
    if (iconLower.contains('bullhorn')) return Icons.campaign;
    if (iconLower.contains('edit')) return Icons.edit;
    if (iconLower.contains('check-circle')) return Icons.check_circle;
    if (iconLower.contains('times-circle') || iconLower.contains('times')) return Icons.cancel;
    if (iconLower.contains('plus-circle') || iconLower.contains('plus')) return Icons.add_circle;
    if (iconLower.contains('trash')) return Icons.delete;
    if (iconLower.contains('money-bill') || iconLower.contains('money')) return Icons.payment;
    if (iconLower.contains('user-plus')) return Icons.person_add;
    if (iconLower.contains('user-edit')) return Icons.person_outline;
    if (iconLower.contains('user-times') || iconLower.contains('user-minus')) return Icons.person_remove;
    if (iconLower.contains('cog') || iconLower.contains('gear')) return Icons.settings;
    if (iconLower.contains('info-circle') || iconLower.contains('info')) return Icons.info;
    
    return Icons.notifications;
  }

  Color _getColorFromString(String color) {
    switch (color.toLowerCase()) {
      case 'blue':
      case 'primary':
      case 'info':
        return const Color(0xFF2196F3);
      case 'green':
      case 'success':
        return const Color(0xFF4CAF50);
      case 'orange':
      case 'warning':
        return const Color(0xFFFF9800);
      case 'red':
      case 'danger':
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

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Container(
        constraints: const BoxConstraints(maxWidth: 600, maxHeight: 700),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                color: Color(0xFF2196F3),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(12),
                  topRight: Radius.circular(12),
                ),
              ),
              child: Row(
                children: [
                  const Icon(Icons.notifications, color: Colors.white, size: 24),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Text(
                      'All Notifications',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                ],
              ),
            ),
            
            // Content
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _notifications.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.notifications_none, size: 64, color: Colors.grey[400]),
                              const SizedBox(height: 16),
                              Text(
                                'No notifications',
                                style: TextStyle(color: Colors.grey[600], fontSize: 16),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.all(8),
                          itemCount: _notifications.length,
                          itemBuilder: (context, index) {
                            final notification = _notifications[index];
                            // Use the same robust parsing logic as notion_app_bar.dart
                            final isReadValue = notification['is_read_by_payer'];
                            final isRead = isReadValue is int 
                                ? isReadValue 
                                : (isReadValue is String 
                                    ? int.tryParse(isReadValue) ?? 0 
                                    : (isReadValue == true || isReadValue == '1' || isReadValue == 1 ? 1 : 0));
                            final isUnread = isRead == 0;
                            
                            final title = notification['title'] ?? 'Notification';
                            final description = notification['description'] ?? '';
                            final date = notification['created_at'] ?? 
                                        notification['created_at_date'] ?? 
                                        notification['created_at_formatted'] ?? '';
                            final iconString = notification['activity_icon'] ?? '';
                            final icon = _getIconFromString(iconString);
                            final colorString = notification['activity_color'] ?? 'blue';
                            final color = _getColorFromString(colorString);
                            
                            return Material(
                              color: Colors.transparent,
                              child: InkWell(
                                onTap: () {
                                  // Mark as read if unread
                                  if (isUnread) {
                                    _markAsRead(notification['id']);
                                  }
                                  
                                  // Close modal and navigate
                                  Navigator.of(context).pop();
                                  
                                  // Navigate to appropriate screen
                                  final activityType = notification['activity_type'] ?? '';
                                  final action = notification['action'] ?? '';
                                  
                                  WidgetsBinding.instance.addPostFrameCallback((_) {
                                    try {
                                      switch (activityType.toLowerCase()) {
                                        case 'announcement':
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(builder: (_) => const AnnouncementsScreen()),
                                          );
                                          break;
                                        case 'contribution':
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(builder: (_) => const ContributionsScreen()),
                                          );
                                          break;
                                        case 'payment':
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
                                          );
                                          break;
                                        case 'payer':
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(builder: (_) => const ProfileScreen()),
                                          );
                                          break;
                                        case 'refund':
                                          // For refund notifications, redirect to refunds screen
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (_) => RefundRequestsScreen(
                                                showAppBar: true,
                                              ),
                                            ),
                                          );
                                          break;
                                        default:
                                          // Stay on current page
                                          break;
                                      }
                                    } catch (e) {
                                      // Silently handle navigation errors
                                    }
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
                                        width: 40,
                                        height: 40,
                                        decoration: BoxDecoration(
                                          color: color.withOpacity(0.1),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Icon(icon, size: 20, color: color),
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
                                            const SizedBox(height: 4),
                                            Text(
                                              _formatDate(date),
                                              style: TextStyle(
                                                fontSize: 12,
                                                color: Colors.grey[500],
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      if (isUnread)
                                        Container(
                                          width: 8,
                                          height: 8,
                                          margin: const EdgeInsets.only(left: 8),
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
                          },
                        ),
            ),
            
            // Footer
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: Color(0xFFE9E9E7), width: 1)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Close'),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton.icon(
                    onPressed: _notifications.any((n) {
                      // Use the same robust parsing logic as notion_app_bar.dart
                      final isReadValue = n['is_read_by_payer'];
                      final isRead = isReadValue is int 
                          ? isReadValue 
                          : (isReadValue is String 
                              ? int.tryParse(isReadValue) ?? 0 
                              : (isReadValue == true || isReadValue == '1' || isReadValue == 1 ? 1 : 0));
                      return isRead == 0;
                    }) ? _markAllAsRead : null,
                    icon: const Icon(Icons.done_all, size: 18),
                    label: const Text('Mark All as Read'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF2196F3),
                      foregroundColor: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

