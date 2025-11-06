import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../widgets/notion_app_bar.dart';
import '../widgets/notion_card.dart';
import '../widgets/notion_text.dart';
import 'payment_requests_screen.dart';
import 'payment_history_screen.dart';

class ContributionsScreen extends StatefulWidget {
  const ContributionsScreen({super.key});

  @override
  State<ContributionsScreen> createState() => _ContributionsScreenState();
}

class _ContributionsScreenState extends State<ContributionsScreen> {
  bool _isLoading = true;
  List<dynamic> _contributions = [];
  List<dynamic> _filteredContributions = [];
  String? _errorMessage;
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();
  Map<int, bool> _expandedGroups = {}; // Track which payment groups are expanded

  @override
  void initState() {
    super.initState();
    _loadContributions();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    setState(() {
      _searchQuery = _searchController.text.toLowerCase();
      _filterContributions();
    });
  }

  void _filterContributions() {
    if (_searchQuery.isEmpty) {
      _filteredContributions = _contributions;
    } else {
      _filteredContributions = _contributions.where((contribution) {
        final title = (contribution['title'] ?? '').toString().toLowerCase();
        final description = (contribution['description'] ?? '').toString().toLowerCase();
        final category = (contribution['category'] ?? '').toString().toLowerCase();
        return title.contains(_searchQuery) ||
            description.contains(_searchQuery) ||
            category.contains(_searchQuery);
      }).toList();
    }
  }

  Future<void> _loadContributions() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await ApiService.getContributions();
      
      if (response['success'] == true && response['data'] != null) {
        setState(() {
          _contributions = response['data'];
          _filterContributions();
        });
      } else {
        setState(() {
          _errorMessage = response['error'] ?? 'Failed to load contributions';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Failed to load contributions: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _togglePaymentGroups(int contributionId) {
    setState(() {
      _expandedGroups[contributionId] = !(_expandedGroups[contributionId] ?? false);
    });
  }

  void _showPaymentGroupDetails(dynamic contributionId, dynamic paymentSequence, Map<String, dynamic> groupData) async {
    // Convert to int if needed
    final contribId = contributionId is int ? contributionId : int.tryParse(contributionId.toString());
    final seq = paymentSequence is int ? paymentSequence : int.tryParse(paymentSequence.toString());
    
    if (contribId == null || seq == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Invalid contribution or payment sequence')),
      );
      return;
    }

    // Show loading dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator()),
    );

    try {
      final response = await ApiService.getContributionPayments(contribId, paymentSequence: seq);
      
      if (mounted) {
        Navigator.pop(context); // Close loading dialog
        
        if (response['success'] == true && response['payments'] != null) {
          _showPaymentGroupModal(contribId, seq, groupData, response['payments']);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response['error'] ?? 'Failed to load payment details')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context); // Close loading dialog
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}')),
        );
      }
    }
  }

  void _showPaymentGroupModal(int contributionId, int paymentSequence, Map<String, dynamic> groupData, List<dynamic> payments) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (context, scrollController) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  border: Border(bottom: BorderSide(color: Colors.grey[300]!)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: NotionText(
                        'Payment Group $paymentSequence',
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  controller: scrollController,
                  padding: const EdgeInsets.all(16),
                  child: _buildPaymentGroupModalContent(groupData, payments, contributionId, paymentSequence),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPaymentGroupModalContent(Map<String, dynamic> groupData, List<dynamic> payments, int contributionId, int paymentSequence) {
    // Ensure payments is not null
    final paymentsList = payments ?? [];
    final totalPaid = _parseDouble(groupData['total_paid'] ?? 0);
    final remainingBalance = _parseDouble(groupData['remaining_balance'] ?? 0);
    final contributionAmount = _parseDouble(groupData['amount'] ?? (paymentsList.isNotEmpty ? paymentsList[0]['contribution_amount'] : 0));
    final progress = contributionAmount > 0 ? (totalPaid / contributionAmount * 100) : 0;
    final isFullyPaid = groupData['computed_status'] == 'fully paid';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Summary Cards
        Row(
          children: [
            Expanded(
              child: NotionCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    NotionText('Total Amount', fontSize: 12, color: const Color(0xFF787774)),
                    const SizedBox(height: 8),
                    NotionText(
                      '₱${NumberFormat('#,##0.00').format(contributionAmount)}',
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF6366F1),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: NotionCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    NotionText('Amount Paid', fontSize: 12, color: const Color(0xFF787774)),
                    const SizedBox(height: 8),
                    NotionText(
                      '₱${NumberFormat('#,##0.00').format(totalPaid)}',
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: Colors.green,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: NotionCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    NotionText('Remaining', fontSize: 12, color: const Color(0xFF787774)),
                    const SizedBox(height: 8),
                    NotionText(
                      '₱${NumberFormat('#,##0.00').format(remainingBalance)}',
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: Colors.orange,
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: NotionCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    NotionText('Status', fontSize: 12, color: const Color(0xFF787774)),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: isFullyPaid ? Colors.green.withOpacity(0.1) : Colors.orange.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: NotionText(
                        isFullyPaid ? 'Fully Paid' : 'Partial',
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: isFullyPaid ? Colors.green : Colors.orange,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        // Progress Bar
        NotionText('Payment Progress', fontSize: 14, fontWeight: FontWeight.w600),
        const SizedBox(height: 8),
        LinearProgressIndicator(
          value: progress / 100,
          backgroundColor: Colors.grey[200],
          valueColor: AlwaysStoppedAnimation<Color>(
            isFullyPaid ? Colors.green : Colors.orange,
          ),
          minHeight: 8,
        ),
        const SizedBox(height: 4),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            NotionText('0%', fontSize: 12, color: const Color(0xFF787774)),
            NotionText('${progress.toStringAsFixed(1)}%', fontSize: 12, color: const Color(0xFF787774)),
            NotionText('100%', fontSize: 12, color: const Color(0xFF787774)),
          ],
        ),
        const SizedBox(height: 24),
        // Individual Payments
        NotionText('Individual Payments', fontSize: 16, fontWeight: FontWeight.w600),
        const SizedBox(height: 12),
          if (payments == null || payments.isEmpty)
          NotionCard(
            padding: const EdgeInsets.all(40),
            child: Center(
              child: Column(
                children: [
                  Icon(Icons.receipt_long, size: 48, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  NotionText('No payments found', fontSize: 14, color: const Color(0xFF787774)),
                ],
              ),
            ),
          )
        else
          NotionCard(
            padding: EdgeInsets.zero,
            child: ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: paymentsList.length,
              separatorBuilder: (context, index) => const Divider(height: 1, color: Color(0xFFE9E9E7)),
              itemBuilder: (context, index) {
                final payment = paymentsList[index];
                return _buildPaymentItem(payment);
              },
            ),
          ),
        const SizedBox(height: 16),
        // Action Buttons
        Row(
          children: [
            if (!isFullyPaid)
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    // Get contribution data for this group
                    final firstPayment = paymentsList.isNotEmpty ? paymentsList[0] : null;
                    final contributionData = {
                      'id': contributionId,
                      'title': firstPayment?['contribution_title'] ?? 'Contribution',
                      'amount': contributionAmount,
                      'description': firstPayment?['contribution_description'] ?? '',
                      'remaining_balance': remainingBalance,
                      'payment_sequence': paymentSequence,
                    };
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => PaymentRequestsScreen(
                          showAppBar: true,
                          preSelectedContribution: contributionData,
                        ),
                      ),
                    );
                  },
                  icon: const Icon(Icons.payment),
                  label: const Text('Request Payment'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4CAF50),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
            if (!isFullyPaid) const SizedBox(width: 12),
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const PaymentHistoryScreen(),
                    ),
                  );
                },
                icon: const Icon(Icons.history),
                label: const Text('View History'),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildPaymentItem(Map<String, dynamic> payment) {
    final amount = _parseDouble(payment['amount_paid'] ?? 0);
    final paymentDate = (payment['payment_date'] ?? payment['created_at'] ?? '').toString();
    final paymentMethod = (payment['payment_method'] ?? 'N/A').toString();
    final referenceNumber = (payment['reference_number'] ?? 'N/A').toString();
    final qrReceiptPath = payment['qr_receipt_path'];

    return InkWell(
      onTap: qrReceiptPath != null && qrReceiptPath.toString().isNotEmpty
          ? () => _showQRReceipt(payment)
          : null,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF6366F1).withOpacity(0.1),
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Icon(Icons.receipt, color: Color(0xFF6366F1), size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  NotionText(
                    '₱${NumberFormat('#,##0.00').format(amount)}',
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                  const SizedBox(height: 4),
                  NotionText(
                    _formatDate(paymentDate),
                    fontSize: 12,
                    color: const Color(0xFF787774),
                  ),
                  const SizedBox(height: 4),
                  NotionText(
                    '$paymentMethod • Ref: $referenceNumber',
                    fontSize: 12,
                    color: const Color(0xFF787774),
                  ),
                ],
              ),
            ),
            if (qrReceiptPath != null && qrReceiptPath.toString().isNotEmpty)
              IconButton(
                icon: const Icon(Icons.qr_code, color: Color(0xFF6366F1)),
                onPressed: () => _showQRReceipt(payment),
                tooltip: 'View Receipt',
              ),
          ],
        ),
      ),
    );
  }

  void _showQRReceipt(Map<String, dynamic> payment) {
    final qrReceiptPath = payment['qr_receipt_path'];
    if (qrReceiptPath == null || qrReceiptPath.toString().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Receipt not available')),
      );
      return;
    }

    showDialog(
      context: context,
      builder: (context) => Dialog(
        child: Container(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const NotionText('Payment Receipt', fontSize: 18, fontWeight: FontWeight.w700),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Image.network(
                '${ApiService.baseUrl}/$qrReceiptPath',
                errorBuilder: (context, error, stackTrace) {
                  return const Column(
                    children: [
                      Icon(Icons.error_outline, size: 64, color: Colors.red),
                      SizedBox(height: 16),
                      Text('Failed to load receipt'),
                    ],
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFFBFE),
      appBar: NotionAppBar(
        title: 'Contributions',
        onRefresh: _loadContributions,
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search contributions...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                        },
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: Color(0xFFE9E9E7)),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: Color(0xFFE9E9E7)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: Color(0xFF6366F1), width: 2),
                ),
              ),
            ),
          ),
          // Content
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _errorMessage != null
                    ? _buildErrorWidget()
                    : _filteredContributions.isEmpty
                        ? _buildEmptyState()
                        : RefreshIndicator(
                            onRefresh: _loadContributions,
                            child: ListView.builder(
                              padding: const EdgeInsets.all(16),
                              itemCount: _filteredContributions.length,
                              itemBuilder: (context, index) {
                                final contribution = _filteredContributions[index];
                                return Padding(
                                  padding: const EdgeInsets.only(bottom: 16),
                                  child: _buildContributionCard(contribution),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: Colors.red),
          const SizedBox(height: 16),
          NotionText(
            _errorMessage ?? 'An error occurred',
            fontSize: 16,
            color: Colors.red,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadContributions,
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          NotionText(
            _searchQuery.isNotEmpty ? 'No contributions found' : 'No Contributions',
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF787774),
          ),
          const SizedBox(height: 8),
          NotionText(
            _searchQuery.isNotEmpty
                ? 'Try adjusting your search terms'
                : 'There are currently no contributions available.',
            fontSize: 14,
            color: const Color(0xFF787774),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value.replaceAll(',', '')) ?? 0.0;
    }
    return 0.0;
  }

  String _formatDate(String? dateString) {
    if (dateString == null || dateString.isEmpty) return 'N/A';
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('MMM dd, yyyy').format(date);
    } catch (e) {
      return dateString;
    }
  }

  Widget _buildContributionCard(Map<String, dynamic> contribution) {
    final title = contribution['title'] ?? 'N/A';
    final description = (contribution['description'] ?? '').toString();
    final amount = _parseDouble(contribution['amount']);
    final totalPaid = _parseDouble(contribution['total_paid']);
    final remaining = _parseDouble(contribution['remaining_balance']);
    final status = contribution['status'] ?? 'active';
    final paymentGroups = (contribution['payment_groups'] as List?) ?? <dynamic>[];
    final contributionIdRaw = contribution['id'];
    final contributionId = contributionIdRaw is int 
        ? contributionIdRaw 
        : (contributionIdRaw is String ? int.tryParse(contributionIdRaw) : null) ?? 0;
    
    final progress = amount > 0 ? (totalPaid / amount * 100) : 0;
    final isFullyPaid = totalPaid >= amount;
    final isPartiallyPaid = totalPaid > 0 && totalPaid < amount;
    final isExpanded = _expandedGroups[contributionId] ?? false;

    // Determine if Add Payment button should be shown
    final showAddPayment = status == 'active' && (
      (paymentGroups.isEmpty) || 
      (paymentGroups.isNotEmpty && paymentGroups.every((group) => (group['computed_status'] ?? '') == 'fully paid'))
    );

    return NotionCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Row(
                  children: [
                    const Icon(Icons.receipt_long, size: 20, color: Color(0xFF6366F1)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: NotionText(
                        title,
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),
              Row(
                children: [
                  if (status == 'inactive')
                    Container(
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.grey[300],
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const NotionText(
                        'INACTIVE',
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        color: Colors.grey,
                      ),
                    ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: isFullyPaid
                          ? Colors.green.withOpacity(0.1)
                          : isPartiallyPaid
                              ? Colors.orange.withOpacity(0.1)
                              : Colors.grey.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: NotionText(
                      isFullyPaid
                          ? 'FULLY PAID'
                          : isPartiallyPaid
                              ? 'PARTIAL'
                              : 'UNPAID',
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: isFullyPaid
                          ? Colors.green
                          : isPartiallyPaid
                              ? Colors.orange
                              : Colors.grey,
                    ),
                  ),
                ],
              ),
            ],
          ),
          // Description
          if (description.isNotEmpty) ...[
            const SizedBox(height: 12),
            NotionText(
              description.length > 100 ? '${description.substring(0, 100)}...' : description,
              fontSize: 14,
              color: const Color(0xFF787774),
            ),
          ],
          const SizedBox(height: 16),
          // Amounts
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  NotionText('Total Amount', fontSize: 12, color: const Color(0xFF787774)),
                  const SizedBox(height: 4),
                  NotionText(
                    '₱${NumberFormat('#,##0.00').format(amount)}',
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  NotionText(
                    paymentGroups.length > 1 ? 'Payment Groups' : 'Total Paid',
                    fontSize: 12,
                    color: const Color(0xFF787774),
                  ),
                  const SizedBox(height: 4),
                  NotionText(
                    paymentGroups.length > 1
                        ? '${paymentGroups.length} Groups'
                        : '₱${NumberFormat('#,##0.00').format(totalPaid)}',
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: paymentGroups.length > 1 ? const Color(0xFF6366F1) : Colors.green,
                  ),
                ],
              ),
            ],
          ),
          if (remaining > 0) ...[
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                NotionText('Remaining', fontSize: 12, color: const Color(0xFF787774)),
                NotionText(
                  '₱${NumberFormat('#,##0.00').format(remaining)}',
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Colors.orange,
                ),
              ],
            ),
          ],
          const SizedBox(height: 12),
          // Progress
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              NotionText('Progress', fontSize: 12, color: const Color(0xFF787774)),
              NotionText('${progress.toStringAsFixed(1)}%', fontSize: 12, color: const Color(0xFF787774)),
            ],
          ),
          const SizedBox(height: 8),
          LinearProgressIndicator(
            value: progress / 100,
            backgroundColor: Colors.grey[200],
            valueColor: AlwaysStoppedAnimation<Color>(
              isFullyPaid ? Colors.green : isPartiallyPaid ? Colors.orange : Colors.grey,
            ),
            minHeight: 6,
          ),
          // Payment Groups
          if (paymentGroups.isNotEmpty) ...[
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                NotionText('Payment Groups', fontSize: 14, fontWeight: FontWeight.w600),
                TextButton.icon(
                  onPressed: () => _togglePaymentGroups(contributionId),
                  icon: Icon(isExpanded ? Icons.expand_less : Icons.expand_more),
                  label: Text(isExpanded ? 'Hide Groups' : 'Show Groups'),
                ),
              ],
            ),
            if (isExpanded) ...[
              const SizedBox(height: 8),
              ...paymentGroups.map<Widget>((group) {
                final groupTotal = _parseDouble(group['total_paid'] ?? 0);
                final groupStatus = group['computed_status'] ?? 'unpaid';
                final sequenceRaw = group['payment_sequence'] ?? 1;
                final sequence = sequenceRaw is int 
                    ? sequenceRaw 
                    : (sequenceRaw is String ? int.tryParse(sequenceRaw) : null) ?? 1;
                final groupRemaining = _parseDouble(group['remaining_balance'] ?? 0);
                final isGroupFullyPaid = groupStatus == 'fully paid';
                
                return InkWell(
                  onTap: () => _showPaymentGroupDetails(contributionId, sequence, group),
                  child: NotionCard(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                Container(
                                  width: 32,
                                  height: 32,
                                  decoration: BoxDecoration(
                                    color: isGroupFullyPaid
                                        ? Colors.green
                                        : Colors.orange,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Center(
                                    child: NotionText(
                                      '$sequence',
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    NotionText(
                                      'Group $sequence',
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                    ),
                                    const SizedBox(height: 4),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: isGroupFullyPaid
                                            ? Colors.green.withOpacity(0.1)
                                            : Colors.orange.withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: NotionText(
                                        isGroupFullyPaid ? 'Completed' : 'Partial',
                                        fontSize: 11,
                                        fontWeight: FontWeight.w600,
                                        color: isGroupFullyPaid ? Colors.green : Colors.orange,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                NotionText(
                                  '₱${NumberFormat('#,##0.00').format(groupTotal)}',
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.green,
                                ),
                                if (groupRemaining > 0)
                                  NotionText(
                                    'Remaining: ₱${NumberFormat('#,##0.00').format(groupRemaining)}',
                                    fontSize: 11,
                                    color: const Color(0xFF787774),
                                  ),
                              ],
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            NotionText(
                              '${group['payment_count'] ?? 0} payment${(group['payment_count'] ?? 0) != 1 ? 's' : ''}',
                              fontSize: 11,
                              color: const Color(0xFF787774),
                            ),
                            if (group['last_payment_date'] != null)
                              NotionText(
                                'Last: ${_formatDate(group['last_payment_date'])}',
                                fontSize: 11,
                                color: const Color(0xFF787774),
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ],
          ],
          // Add Payment Button
          if (showAddPayment) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {
                  // Get contribution data
                  final contributionData = {
                    'id': contributionId,
                    'title': title,
                    'amount': amount,
                    'description': description,
                    'remaining_balance': remaining,
                  };
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => PaymentRequestsScreen(
                        showAppBar: true,
                        preSelectedContribution: contributionData,
                      ),
                    ),
                  );
                },
                icon: const Icon(Icons.add),
                label: const Text('Add Payment'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF6366F1),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ] else if (status == 'inactive') ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: null,
                icon: const Icon(Icons.block),
                label: const Text('Add Payment (Disabled)'),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
            const SizedBox(height: 8),
            NotionText(
              'Contribution is inactive. You can still view payments and request refunds.',
              fontSize: 12,
              color: const Color(0xFF787774),
              textAlign: TextAlign.center,
            ),
          ],
        ],
      ),
    );
  }
}
