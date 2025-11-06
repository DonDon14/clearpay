import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class ContributionsScreen extends StatefulWidget {
  const ContributionsScreen({super.key});

  @override
  State<ContributionsScreen> createState() => _ContributionsScreenState();
}

class _ContributionsScreenState extends State<ContributionsScreen> {
  bool _isLoading = true;
  List<dynamic> _contributions = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadContributions();
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Contributions'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadContributions,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? _buildErrorWidget()
              : RefreshIndicator(
                  onRefresh: _loadContributions,
                  child: _contributions.isEmpty
                      ? _buildEmptyState()
                      : _buildContributionsList(),
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
          Text(
            _errorMessage ?? 'An error occurred',
            style: const TextStyle(color: Colors.red),
            textAlign: TextAlign.center,
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
          Text(
            'No Contributions',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
          const SizedBox(height: 8),
          Text(
            'There are currently no contributions available.',
            style: TextStyle(color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildContributionsList() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _contributions.length,
      itemBuilder: (context, index) {
        final contribution = _contributions[index];
        return _buildContributionCard(contribution);
      },
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

  Widget _buildContributionCard(Map<String, dynamic> contribution) {
    final title = contribution['title'] ?? 'N/A';
    final description = contribution['description'] ?? '';
    final amount = _parseDouble(contribution['amount']);
    final totalPaid = _parseDouble(contribution['total_paid']);
    final remaining = _parseDouble(contribution['remaining_balance']);
    final status = contribution['status'] ?? 'active';
    final paymentGroups = contribution['payment_groups'] ?? [];
    
    final progress = amount > 0 ? (totalPaid / amount * 100) : 0;
    final isFullyPaid = totalPaid >= amount;
    final isPartiallyPaid = totalPaid > 0 && totalPaid < amount;

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.receipt_long, size: 20, color: Color(0xFF2196F3)),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              title,
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                      if (status == 'inactive')
                        Container(
                          margin: const EdgeInsets.only(top: 4),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.grey[300],
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Text(
                            'INACTIVE',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey,
                            ),
                          ),
                        ),
                    ],
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
                  child: Text(
                    isFullyPaid
                        ? 'FULLY PAID'
                        : isPartiallyPaid
                            ? 'PARTIAL'
                            : 'UNPAID',
                    style: TextStyle(
                      color: isFullyPaid
                          ? Colors.green
                          : isPartiallyPaid
                              ? Colors.orange
                              : Colors.grey,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            if (description.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                description.length > 100 ? '${description.substring(0, 100)}...' : description,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
              ),
            ],
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Total Amount',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '₱${NumberFormat('#,##0.00').format(amount)}',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'Paid',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '₱${NumberFormat('#,##0.00').format(totalPaid)}',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
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
                  Text(
                    'Remaining',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                  Text(
                    '₱${NumberFormat('#,##0.00').format(remaining)}',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.orange,
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Progress',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
                Text(
                  '${progress.toStringAsFixed(1)}%',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(
              value: progress / 100,
              backgroundColor: Colors.grey[200],
              valueColor: AlwaysStoppedAnimation<Color>(
                isFullyPaid ? Colors.green : isPartiallyPaid ? Colors.orange : Colors.grey,
              ),
            ),
            if (paymentGroups.isNotEmpty) ...[
              const SizedBox(height: 16),
              ExpansionTile(
                title: const Text(
                  'Payment Groups',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                children: paymentGroups.map<Widget>((group) {
                  final groupTotal = _parseDouble(group['total_paid']);
                  final groupStatus = group['computed_status'] ?? 'unpaid';
                  final sequence = group['payment_sequence'] ?? 1;
                  return ListTile(
                    dense: true,
                    leading: CircleAvatar(
                      radius: 16,
                      backgroundColor: groupStatus == 'fully paid'
                          ? Colors.green
                          : Colors.orange,
                      child: Text(
                        '$sequence',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    title: Text('Group $sequence'),
                    subtitle: Text(
                      groupStatus.replaceAll('_', ' ').toUpperCase(),
                      style: TextStyle(
                        fontSize: 12,
                        color: groupStatus == 'fully paid' ? Colors.green : Colors.orange,
                      ),
                    ),
                    trailing: Text(
                      '₱${NumberFormat('#,##0.00').format(groupTotal)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  );
                }).toList(),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
