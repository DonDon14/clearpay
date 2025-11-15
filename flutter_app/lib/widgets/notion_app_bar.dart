import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'dart:async';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../providers/auth_provider.dart';
import '../providers/dashboard_provider.dart';
import '../services/api_service.dart';
import 'notifications_modal.dart';
import 'announcement_modal.dart';
import '../screens/announcements_screen.dart';
import '../screens/contributions_screen.dart';
import '../screens/payment_history_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/refund_requests_screen.dart';

class NotionAppBar extends StatefulWidget implements PreferredSizeWidget {
  final String title;
  final String? subtitle;
  final bool showNotifications;
  final VoidCallback? onRefresh;
  
  const NotionAppBar({
    super.key,
    required this.title,
    this.subtitle,
    this.showNotifications = true,
    this.onRefresh,
  });

  @override
  Size get preferredSize => subtitle != null 
      ? const Size.fromHeight(80.0) 
      : const Size.fromHeight(70.0);

  @override
  State<NotionAppBar> createState() => _NotionAppBarState();
}

class _NotionAppBarState extends State<NotionAppBar> {
  // Use ValueNotifiers for reactive updates (no setState during build)
  final ValueNotifier<List<Map<String, dynamic>>> _activitiesNotifier = 
      ValueNotifier<List<Map<String, dynamic>>>([]);
  final ValueNotifier<Set<String>> _unreadIdsNotifier = 
      ValueNotifier<Set<String>>(<String>{});
  final ValueNotifier<Set<String>> _unseenIdsNotifier = 
      ValueNotifier<Set<String>>(<String>{});
  final ValueNotifier<bool> _isLoadingNotifier = ValueNotifier<bool>(false);
  
  bool _isDropdownOpen = false;
  OverlayEntry? _overlayEntry;
  Timer? _refreshTimer;
  Set<int> _shownAnnouncementIds = <int>{}; // Track shown announcements to avoid duplicates
  
  @override
  void initState() {
    super.initState();
    if (widget.showNotifications) {
      // Load previously shown announcement IDs
      _loadShownAnnouncementIds();
      
      // Load activities after first frame
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _loadActivities();
      });
      
      // Periodically refresh notifications to detect new ones (every 30 seconds)
      // This ensures new notifications appear even when app is open
      _refreshTimer = Timer.periodic(const Duration(seconds: 30), (timer) {
        if (mounted && !_isLoadingNotifier.value) {
          _loadActivities();
        }
      });
    }
  }

  @override
  void dispose() {
    _overlayEntry?.remove();
    _refreshTimer?.cancel();
    _activitiesNotifier.dispose();
    _unreadIdsNotifier.dispose();
    _unseenIdsNotifier.dispose();
    _isLoadingNotifier.dispose();
    super.dispose();
  }

  // Load activities from API
  Future<void> _loadActivities() async {
    if (_isLoadingNotifier.value || !mounted) return;
    
    _isLoadingNotifier.value = true;
    
    try {
      final response = await ApiService.getNotifications();
      
      if (!mounted) return;
      
      if (response['success'] == true) {
        final activities = List<Map<String, dynamic>>.from(
          response['activities'] ?? []
        );
        
        final unreadIds = <String>{};
        final unseenIds = <String>{};
        
        // Process activities
        final processed = activities.map((a) {
          final activity = Map<String, dynamic>.from(a);
          final id = activity['id'].toString();
          
          // Check if unread (is_read_by_payer == 0)
          // Handle both string and int types from backend
          final isReadValue = activity['is_read_by_payer'];
          final isRead = isReadValue is int 
              ? isReadValue 
              : (isReadValue is String 
                  ? int.tryParse(isReadValue) ?? 0 
                  : (isReadValue == true || isReadValue == '1' || isReadValue == 1 ? 1 : 0));
          
          // Add to unread set if not read (isRead == 0)
          if (isRead == 0) {
            unreadIds.add(id);
            unseenIds.add(id);
          }
          
          return activity;
        }).toList();
        
        // Debug logging
        print('Loaded ${processed.length} activities');
        print('Unread count: ${unreadIds.length}');
        print('Unread IDs: $unreadIds');
        
        // Check for new announcement activities and show modal automatically
        if (mounted) {
          _activitiesNotifier.value = processed;
          _unreadIdsNotifier.value = unreadIds;
          _unseenIdsNotifier.value = unseenIds;
          _isLoadingNotifier.value = false;
          
          // Check for new announcements to show modal
          _checkForNewAnnouncements(processed);
        }
      } else {
        if (mounted) {
          _isLoadingNotifier.value = false;
        }
      }
    } catch (e) {
      if (mounted) {
        _isLoadingNotifier.value = false;
      }
    }
  }

  // Mark activity as read (removes red dot and updates badge)
  Future<void> _markAsRead(String activityId) async {
    final id = int.tryParse(activityId);
    if (id == null) return;
    
    // Update UI immediately using ValueNotifier (no setState)
    final currentUnread = Set<String>.from(_unreadIdsNotifier.value);
    if (currentUnread.contains(activityId)) {
      currentUnread.remove(activityId);
      _unreadIdsNotifier.value = currentUnread;
      // Badge shows unread count, so it updates automatically via ValueListenableBuilder
    }
    
    // Update server (don't wait)
    Future.microtask(() async {
      try {
        await ApiService.markNotificationRead(id);
      } catch (e) {
        // Silently handle errors
      }
    });
  }

  // Mark all as seen (clears badge when bell clicked)
  void _markAllAsSeen() {
    _unseenIdsNotifier.value = <String>{};
  }

  // Load previously shown announcement IDs from shared preferences
  Future<void> _loadShownAnnouncementIds() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final shownIdsString = prefs.getString('shownAnnouncementIds') ?? '';
      if (shownIdsString.isNotEmpty) {
        final shownIdsList = shownIdsString.split(',').map((id) => int.tryParse(id)).whereType<int>().toList();
        _shownAnnouncementIds = Set<int>.from(shownIdsList);
      }
    } catch (e) {
      print('Error loading shown announcement IDs: $e');
    }
  }

  // Check for new announcement activities and show modal automatically
  Future<void> _checkForNewAnnouncements(List<Map<String, dynamic>> activities) async {
    if (!mounted) return;
    
    try {
      // Get last shown announcement ID from shared preferences
      final prefs = await SharedPreferences.getInstance();
      final lastShownId = prefs.getInt('lastShownAnnouncementId') ?? 0;
      
      // Load previously shown announcement IDs from shared preferences
      final shownIdsString = prefs.getString('shownAnnouncementIds') ?? '';
      if (shownIdsString.isNotEmpty) {
        final shownIdsList = shownIdsString.split(',').map((id) => int.tryParse(id)).whereType<int>().toList();
        _shownAnnouncementIds = Set<int>.from(shownIdsList);
      }
      
      // Filter for new announcement activities that haven't been shown
      final announcementActivities = activities.where((activity) {
        final activityType = (activity['activity_type'] ?? '').toString().toLowerCase();
        final action = (activity['action'] ?? '').toString().toLowerCase();
        final activityId = activity['id'] is int 
            ? activity['id'] 
            : int.tryParse(activity['id'].toString()) ?? 0;
        
        // Check if activity is an announcement
        if (activityType != 'announcement' || 
            (action != 'created' && action != 'published') ||
            activityId <= lastShownId) {
          return false;
        }
        
        // Try to get announcement ID from new_values or entity_id
        int? announcementId;
        if (activity['new_values'] != null) {
          try {
            final newValues = activity['new_values'];
            Map<String, dynamic> parsedValues;
            if (newValues is String) {
              parsedValues = Map<String, dynamic>.from(jsonDecode(newValues) as Map);
            } else if (newValues is Map) {
              parsedValues = Map<String, dynamic>.from(newValues);
            } else {
              parsedValues = {};
            }
            
            final id = parsedValues['id'] ?? parsedValues['announcement_id'];
            if (id != null) {
              announcementId = id is int ? id : int.tryParse(id.toString());
            }
          } catch (e) {
            print('Error parsing new_values: $e');
          }
        }
        
        // Fallback to entity_id
        if (announcementId == null && activity['entity_id'] != null) {
          final entityId = activity['entity_id'];
          announcementId = entityId is int ? entityId : int.tryParse(entityId.toString());
        }
        
        // Check if this announcement has been shown
        if (announcementId != null && _shownAnnouncementIds.contains(announcementId)) {
          return false; // Already shown, skip it
        }
        
        return true; // New announcement, show it
      }).toList();
      
      if (announcementActivities.isNotEmpty) {
        // Get the latest announcement
        announcementActivities.sort((a, b) {
          final aId = a['id'] is int ? a['id'] : int.tryParse(a['id'].toString()) ?? 0;
          final bId = b['id'] is int ? b['id'] : int.tryParse(b['id'].toString()) ?? 0;
          return bId.compareTo(aId);
        });
        
        final latestAnnouncement = announcementActivities.first;
        final activityId = latestAnnouncement['id'] is int 
            ? latestAnnouncement['id'] 
            : int.tryParse(latestAnnouncement['id'].toString()) ?? 0;
        
        // Parse announcement data from new_values
        Map<String, dynamic>? announcementData;
        if (latestAnnouncement['new_values'] != null) {
          try {
            final newValues = latestAnnouncement['new_values'];
            if (newValues is String) {
              announcementData = Map<String, dynamic>.from(
                jsonDecode(newValues) as Map
              );
            } else if (newValues is Map) {
              announcementData = Map<String, dynamic>.from(newValues);
            }
            
            // Merge with activity metadata
            if (announcementData != null) {
              announcementData['created_at'] = latestAnnouncement['created_at'];
              announcementData['created_at_date'] = latestAnnouncement['created_at_date'];
              announcementData['created_at_time'] = latestAnnouncement['created_at_time'];
            }
          } catch (e) {
            print('Error parsing announcement data: $e');
            // Fallback: use activity data
            announcementData = latestAnnouncement;
          }
        } else {
          // Fallback: use activity data directly
          announcementData = latestAnnouncement;
        }
        
        if (announcementData != null && mounted) {
          // Get the actual announcement ID from the announcement data
          final announcementId = announcementData['id'] ?? 
                                 announcementData['announcement_id'] ?? 
                                 latestAnnouncement['entity_id'];
          
          // Check if this specific announcement has already been shown
          if (announcementId != null) {
            final announcementIdInt = announcementId is int 
                ? announcementId 
                : int.tryParse(announcementId.toString());
            
            if (announcementIdInt != null && _shownAnnouncementIds.contains(announcementIdInt)) {
              print('Announcement already shown, skipping: $announcementIdInt');
              return;
            }
            
            // Mark announcement as shown (using announcement ID, not activity ID)
            _shownAnnouncementIds.add(announcementIdInt!);
            await prefs.setInt('lastShownAnnouncementId', activityId);
            await prefs.setString('shownAnnouncementIds', _shownAnnouncementIds.join(','));
            print('Marked announcement as shown: $announcementIdInt');
          } else {
            // Fallback: use activity ID if announcement ID not available
            _shownAnnouncementIds.add(activityId);
            await prefs.setInt('lastShownAnnouncementId', activityId);
            await prefs.setString('shownAnnouncementIds', _shownAnnouncementIds.join(','));
          }
          
          // Show modal after a short delay
          Future.delayed(const Duration(milliseconds: 500), () {
            if (mounted) {
              showDialog(
                context: context,
                barrierDismissible: false,
                builder: (context) => AnnouncementModal(
                  announcementData: announcementData!,
                ),
              );
            }
          });
        }
      }
    } catch (e) {
      print('Error checking for new announcements: $e');
    }
  }

  // Handle notification click
  void _handleNotificationClick(Map<String, dynamic> activity, BuildContext navContext, NavigatorState navigator) {
    final activityId = activity['id'].toString();
    
    // Check both activity_type and entity_type (some notifications have empty activity_type)
    final activityType = (activity['activity_type'] ?? '').toString().toLowerCase();
    final entityType = (activity['entity_type'] ?? '').toString().toLowerCase();
    
    // Use entity_type if activity_type is empty
    final typeToUse = activityType.isNotEmpty ? activityType : entityType;
    
    // Debug logging
    print('Notification clicked - Activity ID: $activityId');
    print('Activity type: $activityType, Entity type: $entityType, Using: $typeToUse');
    print('Activity data: $activity');
    
    // Mark as read
    _markAsRead(activityId);
    
    // Close dropdown first
    _closeDropdown();
    
    // Navigate immediately using the stored navigator
    Future.delayed(const Duration(milliseconds: 150), () {
      if (!mounted) {
        print('Widget not mounted, skipping navigation');
        return;
      }
      
      try {
        print('Navigating to: $typeToUse');
        
        // Also check title as fallback
        final title = (activity['title'] ?? '').toString().toLowerCase();
        
        switch (typeToUse) {
          case 'announcement':
            print('Navigating to AnnouncementsScreen');
            navigator.push(
              MaterialPageRoute(builder: (_) => const AnnouncementsScreen()),
            );
            break;
          case 'contribution':
            print('Navigating to ContributionsScreen');
            navigator.push(
              MaterialPageRoute(builder: (_) => const ContributionsScreen()),
            );
            break;
          case 'payment':
          case 'payment_request':
          case 'payment-request':
          case 'paymentrequest':
            // Payment requests should navigate to payment history for payers
            print('Navigating to PaymentHistoryScreen');
            navigator.push(
              MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
            );
            break;
          case 'payer':
            print('Navigating to ProfileScreen');
            navigator.push(
              MaterialPageRoute(builder: (_) => const ProfileScreen()),
            );
            break;
          case 'refund':
            print('Navigating to RefundRequestsScreen');
            navigator.push(
              MaterialPageRoute(
                builder: (_) => RefundRequestsScreen(showAppBar: true),
              ),
            );
            break;
          default:
            print('Unknown activity type: $typeToUse');
            // Fallback: check title for keywords
            if (title.contains('refund')) {
              print('Title contains "refund", navigating to RefundRequestsScreen');
              navigator.push(
                MaterialPageRoute(
                  builder: (_) => RefundRequestsScreen(showAppBar: true),
                ),
              );
            } else if (title.contains('payment') || title.contains('request')) {
              print('Title contains "payment" or "request", navigating to PaymentHistoryScreen');
              navigator.push(
                MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
              );
            } else if (title.contains('announcement')) {
              print('Title contains "announcement", navigating to AnnouncementsScreen');
              navigator.push(
                MaterialPageRoute(builder: (_) => const AnnouncementsScreen()),
              );
            }
            break;
        }
      } catch (e, stackTrace) {
        // Log error for debugging
        print('Navigation error: $e');
        print('Stack trace: $stackTrace');
      }
    });
  }

  // Toggle dropdown
  void _toggleDropdown() {
    if (_isDropdownOpen) {
      _closeDropdown();
    } else {
      _openDropdown();
    }
  }

  // Open dropdown
  void _openDropdown() {
    if (_isDropdownOpen || !mounted) return;
    
    // Mark all as seen (Facebook-like behavior)
    _markAllAsSeen();
    
    // Store navigation context for use in overlay (use the widget's context, not overlay context)
    // Get the navigator state directly to ensure it's valid
    final navigatorState = Navigator.of(context);
    final navigationContext = context;
    
    // Show overlay immediately with current data
    final overlay = Overlay.of(context);
    final renderBox = context.findRenderObject() as RenderBox?;
    if (renderBox == null) return;
    
    final offset = renderBox.localToGlobal(Offset.zero);
    
    // Reload activities in background (don't wait)
    if (!_isLoadingNotifier.value) {
      _loadActivities();
    }
    
    _overlayEntry = OverlayEntry(
      builder: (overlayContext) => Stack(
        children: [
          // Backdrop
          Positioned.fill(
            child: GestureDetector(
              onTap: _closeDropdown,
              behavior: HitTestBehavior.translucent,
            ),
          ),
          // Dropdown - use ValueListenableBuilder to avoid setState
          Positioned(
            top: offset.dy + 56,
            right: 8,
            child: Material(
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
                        border: Border(
                          bottom: BorderSide(color: Color(0xFFE9E9E7), width: 1),
                        ),
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
                            onPressed: _closeDropdown,
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                          ),
                        ],
                      ),
                    ),
                    // List - use ValueListenableBuilder (no setState)
                    Flexible(
                      child: ValueListenableBuilder<bool>(
                        valueListenable: _isLoadingNotifier,
                        builder: (context, isLoading, _) {
                          return ValueListenableBuilder<List<Map<String, dynamic>>>(
                            valueListenable: _activitiesNotifier,
                            builder: (context, activities, _) {
                              if (isLoading && activities.isEmpty) {
                                return const Padding(
                                  padding: EdgeInsets.all(40),
                                  child: Center(child: CircularProgressIndicator()),
                                );
                              }
                              
                              if (activities.isEmpty) {
                                return Padding(
                                  padding: const EdgeInsets.all(40),
                                  child: Column(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(Icons.notifications_none,
                                          size: 48, color: Colors.grey[400]),
                                      const SizedBox(height: 16),
                                      Text(
                                        'No notifications',
                                        style: TextStyle(
                                            color: Colors.grey[600], fontSize: 14),
                                      ),
                                    ],
                                  ),
                                );
                              }
                              
                              return ValueListenableBuilder<Set<String>>(
                                valueListenable: _unreadIdsNotifier,
                                builder: (context, unreadIds, _) {
                                  // Always show "View All" if there are activities
                                  final itemCount = activities.length > 5 
                                      ? 6  // Show 5 items + "View All"
                                      : activities.length + 1; // Show all items + "View All"
                                  
                                  return ListView.builder(
                                    shrinkWrap: true,
                                    itemCount: itemCount,
                                    itemBuilder: (context, index) {
                                      // Show "View All" button at the end
                                      if (index == 5 || (activities.length <= 5 && index == activities.length)) {
                                        return Container(
                                          padding: const EdgeInsets.all(12),
                                          decoration: const BoxDecoration(
                                            border: Border(
                                              top: BorderSide(
                                                  color: Color(0xFFE9E9E7), width: 1),
                                            ),
                                          ),
                                          child: SizedBox(
                                            width: double.infinity,
                                            child: TextButton(
                                              onPressed: () {
                                                _closeDropdown();
                                                Future.delayed(const Duration(milliseconds: 100), () {
                                                  if (mounted) {
                                                    showDialog(
                                                      context: navigationContext,
                                                      builder: (_) =>
                                                          const NotificationsModal(),
                                                    );
                                                  }
                                                });
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
                                        );
                                      }
                                      
                                      final activity = activities[index];
                                      final activityId = activity['id'].toString();
                                      final isUnread = unreadIds.contains(activityId);
                                      
                                      return _buildNotificationItem(activity, isUnread, navigationContext, navigatorState);
                                    },
                                  );
                                },
                              );
                            },
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
    
    overlay.insert(_overlayEntry!);
    _isDropdownOpen = true;
  }

  // Close dropdown
  void _closeDropdown() {
    if (!_isDropdownOpen) return;
    _overlayEntry?.remove();
    _overlayEntry = null;
    _isDropdownOpen = false;
  }

  // Build notification item
  Widget _buildNotificationItem(Map<String, dynamic> activity, bool isUnread, BuildContext navContext, NavigatorState navigator) {
    final title = activity['title'] ?? 'Notification';
    final description = activity['description'] ?? '';
    final date = activity['created_at_date'] ?? activity['created_at_formatted'] ?? '';
    final iconString = activity['activity_icon'] ?? 'fas fa-info-circle';
    final colorString = activity['activity_color'] ?? 'blue';
    
    final icon = _getIcon(iconString);
    final color = _getColor(colorString);
    
    return InkWell(
      onTap: () => _handleNotificationClick(activity, navContext, navigator),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isUnread ? const Color(0xFFF7F6F3) : Colors.white,
          border: const Border(
            bottom: BorderSide(color: Color(0xFFE9E9E7), width: 1),
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Icon(icon, size: 18, color: color),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF37352F),
                    ),
                  ),
                  if (description.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      description,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  if (date.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      date,
                      style: TextStyle(
                        fontSize: 11,
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
                margin: const EdgeInsets.only(left: 8, top: 4),
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

  // Helper: Get icon from string
  IconData _getIcon(String iconString) {
    if (iconString.contains('bullhorn')) return Icons.campaign;
    if (iconString.contains('money')) return Icons.payment;
    if (iconString.contains('user')) return Icons.person;
    if (iconString.contains('check')) return Icons.check_circle;
    if (iconString.contains('times')) return Icons.cancel;
    if (iconString.contains('edit')) return Icons.edit;
    if (iconString.contains('trash')) return Icons.delete;
    if (iconString.contains('plus')) return Icons.add_circle;
    return Icons.info;
  }

  // Helper: Get color from string
  Color _getColor(String colorString) {
    switch (colorString.toLowerCase()) {
      case 'primary':
      case 'blue':
        return const Color(0xFF2196F3);
      case 'success':
      case 'green':
        return const Color(0xFF4CAF50);
      case 'warning':
      case 'orange':
        return const Color(0xFFFF9800);
      case 'danger':
      case 'red':
        return const Color(0xFFEF4444);
      case 'info':
        return const Color(0xFF2196F3);
      default:
        return const Color(0xFF2196F3);
    }
  }

  @override
  Widget build(BuildContext context) {
    // Use listen: false to avoid triggering rebuilds during build
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.user;
    
    Map<String, dynamic>? effectiveUser = user;
    try {
      final dashboardProvider = Provider.of<DashboardProvider>(context, listen: false);
      if (dashboardProvider.dashboardData?.payer != null) {
        final payerData = dashboardProvider.dashboardData!.payer;
        if (payerData.isNotEmpty) {
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
      effectiveUser = user;
    }
    
    return AppBar(
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
                final scaffoldState = Scaffold.maybeOf(context);
                if (scaffoldState != null) {
                  scaffoldState.openDrawer();
                }
              }
            },
          );
        },
      ),
      title: widget.subtitle != null
          ? Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Container(
                      width: 4,
                      height: 20,
                      decoration: BoxDecoration(
                        color: const Color(0xFF3B82F6),
                        borderRadius: BorderRadius.circular(2),
                      ),
                      margin: const EdgeInsets.only(right: 12),
                    ),
                    Text(
                      widget.title,
                      style: const TextStyle(
                        color: Color(0xFF1F2937),
                        fontSize: 24,
                        fontWeight: FontWeight.w600,
                        height: 1.2,
                      ),
                    ),
                  ],
                ),
                if (widget.subtitle != null) ...[
                  const SizedBox(height: 4),
                  Padding(
                    padding: const EdgeInsets.only(left: 16),
                    child: Text(
                      widget.subtitle!,
                      style: const TextStyle(
                        color: Color(0xFF6B7280),
                        fontSize: 14,
                        fontWeight: FontWeight.w400,
                      ),
                    ),
                  ),
                ],
              ],
            )
          : Row(
              children: [
                Container(
                  width: 4,
                  height: 20,
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B82F6),
                    borderRadius: BorderRadius.circular(2),
                  ),
                  margin: const EdgeInsets.only(right: 12),
                ),
                Text(
                  widget.title,
                  style: const TextStyle(
                    color: Color(0xFF1F2937),
                    fontSize: 24,
                    fontWeight: FontWeight.w600,
                    height: 1.2,
                  ),
                ),
              ],
            ),
      titleSpacing: 0,
      actions: [
        if (widget.showNotifications)
          Stack(
            clipBehavior: Clip.none,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined,
                    color: Color(0xFF37352F), size: 22),
                onPressed: _toggleDropdown,
              ),
              // Use ValueListenableBuilder for badge - shows unread count (like web portal)
              ValueListenableBuilder<Set<String>>(
                valueListenable: _unreadIdsNotifier,
                builder: (context, unreadIds, _) {
                  // Debug logging
                  print('Badge builder - Unread count: ${unreadIds.length}, IDs: $unreadIds');
                  
                  if (unreadIds.isEmpty) {
                    return const SizedBox.shrink();
                  }
                  
                  final count = unreadIds.length;
                  print('Showing badge with count: $count');
                  
                  return Positioned(
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
                        count > 9 ? '9+' : '$count',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  );
                },
              ),
            ],
          ),
      ],
    );
  }
}
