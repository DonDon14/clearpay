import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../providers/auth_provider.dart';
import '../providers/dashboard_provider.dart';
import '../models/dashboard_data.dart';
import 'contributions_screen.dart';
import 'payment_history_screen.dart';
import 'announcements_screen.dart';
import 'payment_requests_screen.dart';
import 'refund_requests_screen.dart';
import 'requests_screen.dart';
import 'profile_screen.dart';
import '../widgets/notion_app_bar.dart';
import '../widgets/notion_card.dart';
import '../widgets/notion_text.dart';
import '../services/modal_service.dart';

class MainNavigationScreen extends StatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> with SingleTickerProviderStateMixin {
  int _currentIndex = 0;
  bool _isFabOpen = false;
  late AnimationController _fabAnimationController;
  late Animation<double> _fabAnimation;

  // Keep all screens in memory
  final List<Widget> _screens = [
    const DashboardContent(), // Dashboard content without bottom nav
    const ContributionsScreen(),
    RequestsScreen(initialTab: 0), // Combined requests screen with tabs
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    _fabAnimationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 200),
    );
    _fabAnimation = CurvedAnimation(
      parent: _fabAnimationController,
      curve: Curves.easeInOut,
    );
    // Load dashboard data when screen is initialized
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<DashboardProvider>(context, listen: false).loadDashboard();
    });
  }

  @override
  void dispose() {
    _fabAnimationController.dispose();
    super.dispose();
  }

  void _toggleFab() {
    setState(() {
      _isFabOpen = !_isFabOpen;
      if (_isFabOpen) {
        _fabAnimationController.forward();
      } else {
        _fabAnimationController.reverse();
      }
    });
  }

  void _onTabTapped(int index) {
    setState(() {
      _currentIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFFBFE),
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 20,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: SafeArea(
          child: Container(
            height: 65,
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: _buildNavItem(Icons.home_outlined, Icons.home, 'Home', 0, () => _onTabTapped(0)),
                ),
                Expanded(
                  child: _buildNavItem(Icons.payment_outlined, Icons.payment, 'Contributions', 1, () => _onTabTapped(1)),
                ),
                const SizedBox(width: 40), // Space for FAB
                Expanded(
                  child: _buildNavItem(Icons.request_quote_outlined, Icons.request_quote, 'Requests', 2, () => _onTabTapped(2)),
                ),
                Expanded(
                  child: _buildNavItem(Icons.person_outline, Icons.person, 'Profile', 3, () => _onTabTapped(3)),
                ),
              ],
            ),
          ),
        ),
      ),
      floatingActionButton: Column(
        mainAxisAlignment: MainAxisAlignment.end,
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Payment Request and Refund Request Buttons - Side by Side
          if (_isFabOpen)
            FadeTransition(
              opacity: _fabAnimation,
              child: SlideTransition(
                position: Tween<Offset>(
                  begin: const Offset(0, 0.5),
                  end: Offset.zero,
                ).animate(_fabAnimation),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Payment Request Button
                    Container(
                      margin: const EdgeInsets.only(bottom: 10, right: 8),
                      child: FloatingActionButton.extended(
                        heroTag: "payment_request",
                        onPressed: () {
                          _toggleFab();
                          // Show payment request modal from anywhere
                          ModalService.showPaymentRequestModal();
                        },
                        backgroundColor: const Color(0xFF4CAF50),
                        icon: const Icon(Icons.payment, color: Colors.white),
                        label: const Text(
                          'Payment Request',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    // Refund Request Button
                    Container(
                      margin: const EdgeInsets.only(bottom: 10, left: 8),
                      child: FloatingActionButton.extended(
                        heroTag: "refund_request",
                        onPressed: () {
                          _toggleFab();
                          // Show refund request modal from anywhere
                          ModalService.showRefundRequestModal();
                        },
                        backgroundColor: const Color(0xFFFF9800),
                        icon: const Icon(Icons.undo, color: Colors.white),
                        label: const Text(
                          'Refund Request',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          // Main FAB - Purple circular button like in the design
          FloatingActionButton(
            heroTag: "main_fab",
            onPressed: _toggleFab,
            backgroundColor: const Color(0xFF6366F1), // Purple color matching design
            elevation: 4,
            child: AnimatedRotation(
              turns: _isFabOpen ? 0.125 : 0,
              duration: const Duration(milliseconds: 200),
              child: Icon(
                _isFabOpen ? Icons.close : Icons.add,
                color: Colors.white,
                size: 28,
              ),
            ),
          ),
        ],
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
    );
  }

  Widget _buildNavItem(IconData outlinedIcon, IconData filledIcon, String label, int index, VoidCallback onTap) {
    final isSelected = _currentIndex == index;
    final purpleColor = const Color(0xFF6366F1); // Purple color matching the design
    final greyColor = const Color(0xFF9CA3AF); // Light grey for inactive
    
    return GestureDetector(
      onTap: () {
        // Close FAB if open when switching tabs
        if (_isFabOpen) {
          _toggleFab();
        }
        onTap();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 2),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: isSelected
                  ? BoxDecoration(
                      color: purpleColor.withOpacity(0.15),
                      shape: BoxShape.circle,
                    )
                  : null,
              child: Icon(
                isSelected ? filledIcon : outlinedIcon,
                color: isSelected ? purpleColor : greyColor,
                size: isSelected ? 22 : 20,
              ),
            ),
            const SizedBox(height: 1),
            Flexible(
              child: Text(
                label,
                style: TextStyle(
                  color: isSelected ? purpleColor : greyColor,
                  fontSize: 10,
                  fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                  letterSpacing: 0,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.center,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// Dashboard content without bottom navigation (since it's in MainNavigationScreen)
class DashboardContent extends StatelessWidget {
  const DashboardContent({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;
    final dashboardProvider = Provider.of<DashboardProvider>(context);
    
    // Get payer name from dashboard data if available, otherwise from user
    String payerName = 'User';
    if (dashboardProvider.dashboardData?.payer != null) {
      final payerData = dashboardProvider.dashboardData!.payer;
      payerName = payerData['payer_name'] ?? 
                  payerData['name'] ?? 
                  user?['payer_name'] ?? 
                  user?['name'] ?? 
                  'User';
      
      // Update AuthProvider with complete user data from dashboard
      if (user != null && payerData.isNotEmpty) {
        final updatedUser = Map<String, dynamic>.from(user);
        // Merge payer data from dashboard into user data
        updatedUser.addAll({
          'payer_name': payerData['payer_name'] ?? updatedUser['payer_name'],
          'payer_id': payerData['payer_id'] ?? updatedUser['payer_id'],
          'email_address': payerData['email_address'] ?? updatedUser['email_address'],
          'contact_number': payerData['contact_number'] ?? updatedUser['contact_number'],
          'profile_picture': payerData['profile_picture'] ?? updatedUser['profile_picture'],
        });
        authProvider.updateUserData(updatedUser);
      } else if (payerData.isNotEmpty) {
        // If user is null, create new user data from dashboard
        authProvider.updateUserData(Map<String, dynamic>.from(payerData));
      }
    } else if (user != null) {
      payerName = user['payer_name'] ?? user['name'] ?? 'User';
    }

    return Scaffold(
      backgroundColor: const Color(0xFFFFFBFE),
      appBar: NotionAppBar(
        title: 'ClearPay',
        onRefresh: () => dashboardProvider.loadDashboard(),
      ),
      body: RefreshIndicator(
        onRefresh: () => dashboardProvider.loadDashboard(),
        child: CustomScrollView(
          slivers: [
            // Welcome Section - Notion Style
            SliverToBoxAdapter(
              child: Container(
                padding: const EdgeInsets.fromLTRB(24, 32, 24, 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    NotionText(
                      'Welcome back,',
                      fontSize: 15,
                      color: const Color(0xFF787774),
                    ),
                    const SizedBox(height: 4),
                    NotionText(
                      payerName,
                      fontSize: 32,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF37352F),
                    ),
                  ],
                ),
              ),
            ),

            // Content
            SliverToBoxAdapter(
              child: dashboardProvider.isLoading
                  ? const Padding(
                      padding: EdgeInsets.all(40.0),
                      child: Center(child: CircularProgressIndicator()),
                    )
                  : dashboardProvider.errorMessage != null
                      ? _DashboardErrorWidget(dashboardProvider.errorMessage!)
                      : dashboardProvider.hasData
                          ? _DashboardContentWidget(dashboardProvider.dashboardData!, context)
                          : const SizedBox(),
            ),
            
            // Add bottom padding to prevent overflow with bottom navigation and FAB
            const SliverToBoxAdapter(
              child: SizedBox(height: 80),
            ),
          ],
        ),
      ),
    );
  }
}

// Error widget for dashboard
class _DashboardErrorWidget extends StatelessWidget {
  final String error;
  
  const _DashboardErrorWidget(this.error);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Center(
        child: Column(
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              error,
              style: const TextStyle(color: Colors.red),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                Provider.of<DashboardProvider>(context, listen: false).loadDashboard();
              },
              child: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }
}

// Dashboard content widget
class _DashboardContentWidget extends StatelessWidget {
  final dynamic data;
  final BuildContext context;
  
  const _DashboardContentWidget(this.data, this.context);

  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value.replaceAll(',', '')) ?? 0.0;
    }
    return 0.0;
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          // Total Paid Card
          _buildTotalPaidCard(data.totalPaid),

          const SizedBox(height: 24),

          // Quick Actions
          _buildSectionTitle('Quick Actions'),
          const SizedBox(height: 16),
          _buildQuickActions(context),

          const SizedBox(height: 24),

          // Stats Cards
          _buildSectionTitle('Statistics'),
          const SizedBox(height: 16),
          _buildStatsCards(data),

          const SizedBox(height: 24),

          // Recent Payments
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _buildSectionTitle('Recent Payments'),
              TextButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
                  );
                },
                child: NotionText(
                  'See All',
                  fontSize: 14,
                  color: const Color(0xFF37352F),
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _buildRecentPayments(data.recentPayments),

          const SizedBox(height: 24),

          // Announcements
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _buildSectionTitle('Announcements'),
              TextButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AnnouncementsScreen()),
                  );
                },
                child: NotionText(
                  'See All',
                  fontSize: 14,
                  color: const Color(0xFF37352F),
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _buildAnnouncements(data.announcements),
        ],
      ),
    );
  }

  Widget _buildTotalPaidCard(double totalPaid) {
    return NotionCard(
      padding: const EdgeInsets.all(20),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                NotionText(
                  'Total Paid',
                  fontSize: 14,
                  color: const Color(0xFF787774),
                ),
                const SizedBox(height: 8),
                NotionText(
                  '₱${NumberFormat('#,##0.00').format(totalPaid)}',
                  fontSize: 28,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF37352F),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F1EF),
              borderRadius: BorderRadius.circular(6),
            ),
            child: const Icon(
              Icons.account_balance_wallet,
              color: Color(0xFF37352F),
              size: 24,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return NotionText(
      title,
      fontSize: 18,
      fontWeight: FontWeight.w600,
      color: const Color(0xFF37352F),
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _buildActionCard(
            context,
            icon: Icons.payment,
            title: 'Payment Request',
            color: const Color(0xFF4CAF50),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PaymentRequestsScreen()),
              );
            },
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: _buildActionCard(
            context,
            icon: Icons.history,
            title: 'Payment History',
            color: const Color(0xFF2196F3),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildActionCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required Color color,
    required VoidCallback onTap,
  }) {
    return NotionCard(
      padding: const EdgeInsets.all(16),
      onTap: onTap,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(height: 12),
          NotionText(
            title,
            fontSize: 14,
            fontWeight: FontWeight.w500,
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildStatsCards(dynamic data) {
    return Row(
      children: [
        Expanded(
          child: _buildStatCard(
            icon: Icons.receipt_long,
            value: '${data.totalPayments}',
            label: 'Payments',
            color: const Color(0xFF2196F3),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildStatCard(
            icon: Icons.notifications,
            value: '${data.announcements.length}',
            label: 'Announcements',
            color: const Color(0xFFFF9800),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildStatCard(
            icon: Icons.pending_actions,
            value: '${data.pendingRequests}',
            label: 'Pending',
            color: const Color(0xFFFF5722),
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard({
    required IconData icon,
    required String value,
    required String label,
    required Color color,
  }) {
    return NotionCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 12),
          NotionText(
            value,
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: color,
          ),
          const SizedBox(height: 4),
          NotionText(
            label,
            fontSize: 13,
            color: const Color(0xFF787774),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildRecentPayments(List<dynamic> payments) {
    if (payments.isEmpty) {
      return NotionCard(
        padding: const EdgeInsets.all(40),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.receipt_long, size: 48, color: Colors.grey[400]),
              const SizedBox(height: 16),
              NotionText(
                'No payments yet',
                fontSize: 14,
                color: const Color(0xFF787774),
              ),
            ],
          ),
        ),
      );
    }

    return NotionCard(
      padding: EdgeInsets.zero,
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: payments.length > 5 ? 5 : payments.length,
        separatorBuilder: (context, index) => const Divider(height: 1, color: Color(0xFFE9E9E7)),
        itemBuilder: (context, index) {
          final payment = payments[index];
          final date = payment['payment_date'] ?? payment['created_at'] ?? '';
            final amount = _parseDouble(payment['amount_paid'] ?? 0);
          final reference = payment['reference_number'] ?? 'N/A';
          final status = payment['payment_status'] ?? 'pending';

          return InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PaymentHistoryScreen()),
              );
            },
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: _getStatusColor(status).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Icon(
                      Icons.receipt,
                      color: _getStatusColor(status),
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        NotionText(
                          '₱${NumberFormat('#,##0.00').format(amount)}',
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                        ),
                        const SizedBox(height: 4),
                        NotionText(
                          _formatDate(date),
                          fontSize: 13,
                          color: const Color(0xFF787774),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: _getStatusColor(status).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: NotionText(
                      status.toUpperCase(),
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: _getStatusColor(status),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildAnnouncements(List<dynamic> announcements) {
    if (announcements.isEmpty) {
      return NotionCard(
        padding: const EdgeInsets.all(40),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.notifications_off, size: 48, color: Colors.grey[400]),
              const SizedBox(height: 16),
              NotionText(
                'No announcements',
                fontSize: 14,
                color: const Color(0xFF787774),
              ),
            ],
          ),
        ),
      );
    }

    return NotionCard(
      padding: EdgeInsets.zero,
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: announcements.length > 3 ? 3 : announcements.length,
        separatorBuilder: (context, index) => const Divider(height: 1, color: Color(0xFFE9E9E7)),
        itemBuilder: (context, index) {
          final announcement = announcements[index];
          final title = announcement['title'] ?? 'No Title';
          final text = announcement['text'] ?? '';
          final date = announcement['created_at'] ?? '';

          return InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const AnnouncementsScreen()),
              );
            },
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.orange.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Icon(
                      Icons.notifications,
                      color: Colors.orange,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        NotionText(
                          title,
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                        ),
                        if (text.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          NotionText(
                            text.length > 80 ? '${text.substring(0, 80)}...' : text,
                            fontSize: 13,
                            color: const Color(0xFF787774),
                            maxLines: 2,
                          ),
                        ],
                        if (date.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          NotionText(
                            _formatDate(date),
                            fontSize: 12,
                            color: const Color(0xFF9B9A97),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'fully paid':
      case 'completed':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'partial':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  String _formatDate(String dateString) {
    if (dateString.isEmpty) return 'N/A';
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('MMM dd, yyyy').format(date);
    } catch (e) {
      return dateString;
    }
  }
}

