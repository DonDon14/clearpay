import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../widgets/payment_receipt_modal.dart';
import '../widgets/notion_app_bar.dart';
import '../widgets/navigation_drawer.dart';

class PaymentHistoryScreen extends StatefulWidget {
  const PaymentHistoryScreen({super.key});

  @override
  State<PaymentHistoryScreen> createState() => _PaymentHistoryScreenState();
}

class _PaymentHistoryScreenState extends State<PaymentHistoryScreen> {
  bool _isLoading = true;
  List<dynamic> _contributionsWithPayments = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadPaymentHistory();
  }

  Future<void> _loadPaymentHistory() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await ApiService.getPaymentHistory();
      
      if (response['success'] == true && response['data'] != null) {
        setState(() {
          _contributionsWithPayments = response['data'];
        });
      } else {
        setState(() {
          _errorMessage = response['error'] ?? 'Failed to load payment history';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Failed to load payment history: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppNavigationDrawer(),
      appBar: NotionAppBar(
        title: 'Payment History',
        subtitle: 'View all your payment transactions',
        onRefresh: _loadPaymentHistory,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? _buildErrorWidget()
              : RefreshIndicator(
                  onRefresh: _loadPaymentHistory,
                  child: _contributionsWithPayments.isEmpty
                      ? _buildEmptyState()
                      : _buildPaymentHistoryList(),
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
            onPressed: _loadPaymentHistory,
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
          Icon(Icons.receipt_long, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'No Payment History',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
          const SizedBox(height: 8),
          Text(
            'You haven\'t made any payments yet.',
            style: TextStyle(color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentHistoryList() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _contributionsWithPayments.length,
      itemBuilder: (context, index) {
        final contribution = _contributionsWithPayments[index];
        return _buildContributionSection(contribution);
      },
    );
  }

  Widget _buildContributionSection(Map<String, dynamic> contribution) {
    final title = contribution['title'] ?? 'N/A';
    final description = contribution['description'] ?? '';
    final totalAmount = _parseDouble(contribution['amount'] ?? 0);
    final totalPaid = _parseDouble(contribution['total_paid'] ?? 0);
    final remaining = _parseDouble(contribution['remaining_amount'] ?? 0);
    final payments = contribution['payments'] ?? [];
    final isFullyPaid = totalPaid >= totalAmount;

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      child: ExpansionTile(
        leading: CircleAvatar(
          backgroundColor: isFullyPaid ? Colors.green : Colors.orange,
          child: Icon(
            isFullyPaid ? Icons.check_circle : Icons.pending,
            color: Colors.white,
          ),
        ),
        title: Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              'Total: ₱${NumberFormat('#,##0.00').format(totalAmount)} • Paid: ₱${NumberFormat('#,##0.00').format(totalPaid)}',
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
            if (remaining > 0)
              Text(
                'Remaining: ₱${NumberFormat('#,##0.00').format(remaining)}',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.orange,
                  fontWeight: FontWeight.w600,
                ),
              ),
          ],
        ),
        children: [
          if (description.isNotEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Text(
                description,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
              ),
            ),
          const Divider(height: 1),
          ...payments.map<Widget>((payment) {
            // Add contribution title to payment data
            final paymentWithContribution = Map<String, dynamic>.from(payment);
            paymentWithContribution['contribution_title'] = title;
            return _buildPaymentItem(paymentWithContribution);
          }),
        ],
      ),
    );
  }

  Widget _buildPaymentItem(Map<String, dynamic> payment) {
    final amount = _parseDouble(payment['amount_paid'] ?? 0);
    final date = payment['payment_date'] ?? payment['created_at'] ?? '';
    final method = payment['payment_method'] ?? 'N/A';
    final status = payment['payment_status'] ?? 'pending';
    final receipt = payment['receipt_number'] ?? 'N/A';
    final reference = payment['reference_number'] ?? 'N/A';

    return ListTile(
      dense: true,
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: _getStatusColor(status).withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(
          Icons.receipt,
          color: _getStatusColor(status),
          size: 20,
        ),
      ),
      title: Text(
        '₱${NumberFormat('#,##0.00').format(amount)}',
        style: const TextStyle(
          fontWeight: FontWeight.bold,
        ),
      ),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            _formatDate(date),
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: _getStatusColor(status).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(
                    color: _getStatusColor(status),
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Text(
                method.toString().replaceAll('_', ' ').toUpperCase(),
                style: TextStyle(
                  fontSize: 10,
                  color: Colors.grey[600],
                ),
              ),
            ],
          ),
          if (receipt != 'N/A')
            Text(
              'Receipt: $receipt',
              style: TextStyle(
                fontSize: 10,
                color: Colors.grey[500],
              ),
            ),
        ],
      ),
      trailing: Icon(
        Icons.chevron_right,
        color: Colors.grey[400],
      ),
      onTap: () {
        // Show payment receipt modal
        PaymentReceiptModal.show(context, payment);
      },
    );
  }


  String _formatDate(String dateString) {
    if (dateString.isEmpty) return 'N/A';
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('MMM dd, yyyy • hh:mm a').format(date);
    } catch (e) {
      return dateString;
    }
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
}
