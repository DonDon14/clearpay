import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

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
      appBar: AppBar(
        title: const Text('Payment History'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadPaymentHistory,
          ),
        ],
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
        // TODO: Show payment details
        _showPaymentDetails(payment);
      },
    );
  }

  void _showPaymentDetails(Map<String, dynamic> payment) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.user;
    
    // Get payer information from payment data first (from API), then fall back to user data
    final payerName = payment['payer_name'] ?? 
                     user?['payer_name'] ?? 
                     'N/A';
    final payerId = payment['payer_student_id'] ?? 
                   payment['payer_id'] ?? 
                   user?['payer_id'] ?? 
                   user?['student_id'] ?? 
                   'N/A';
    final contactNumber = payment['contact_number'] ?? 
                         user?['contact_number'] ?? 
                         'N/A';
    final emailAddress = payment['email_address'] ?? 
                        user?['email_address'] ?? 
                        'N/A';
    
    // Get contribution title from payment or contribution data
    final contributionTitle = payment['contribution_title'] ?? 
                             payment['contribution']?['title'] ?? 
                             'N/A';
    
    // Format payment date for receipt
    final paymentDate = payment['payment_date'] ?? payment['created_at'] ?? '';
    final formattedDate = _formatReceiptDate(paymentDate);
    
    // Get payment details
    final amountPaid = _parseDouble(payment['amount_paid'] ?? 0);
    final paymentMethod = (payment['payment_method'] ?? 'N/A').toString();
    final status = payment['payment_status'] ?? 'pending';
    final receiptNumber = payment['receipt_number'] ?? 'N/A';
    final referenceNumber = payment['reference_number'] ?? 'N/A';
    
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(16),
        child: Container(
          constraints: const BoxConstraints(maxWidth: 600),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Header - Green background
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: const BoxDecoration(
                  color: Color(0xFF198754), // Green
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(10),
                    topRight: Radius.circular(10),
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 25,
                      height: 25,
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Icon(
                        Icons.qr_code,
                        color: Colors.white,
                        size: 16,
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'Payment Receipts',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white),
                      onPressed: () => Navigator.pop(context),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ],
                ),
              ),
              
              // Receipt Content
              Flexible(
                child: SingleChildScrollView(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // ClearPay Logo and Title
                        Center(
                          child: Column(
                            children: [
                              const Icon(
                                Icons.credit_card,
                                size: 48,
                                color: Color(0xFF0d6efd),
                              ),
                              const SizedBox(height: 8),
                              const Text(
                                'ClearPay',
                                style: TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF0d6efd),
                                ),
                              ),
                              Text(
                                'Payment Receipt',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey[600],
                                ),
                              ),
                              const Divider(height: 32),
                            ],
                          ),
                        ),
                        
                        // Receipt Number and Payment Date Cards
                        Row(
                          children: [
                            Expanded(
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.grey[100],
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Receipt Number',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: Colors.grey[600],
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      receiptNumber,
                                      style: const TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.grey[100],
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Payment Date',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: Colors.grey[600],
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      formattedDate,
                                      style: const TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                        
                        const SizedBox(height: 20),
                        
                        // Payer Information Section
                        _buildReceiptSection(
                          icon: Icons.person,
                          title: 'Payer Information',
                          children: [
                            _buildReceiptDetailRow('Name:', payerName, isBold: true),
                            _buildReceiptDetailRow('ID:', payerId.toString()),
                            _buildReceiptDetailRow('Contact:', contactNumber),
                            _buildReceiptDetailRow('Email:', emailAddress),
                          ],
                        ),
                        
                        const SizedBox(height: 20),
                        
                        // Payment Details Section
                        _buildReceiptSection(
                          icon: Icons.credit_card,
                          title: 'Payment Details',
                          children: [
                            _buildReceiptDetailRow('Contribution:', contributionTitle, isBold: true),
                            _buildReceiptDetailRow(
                              'Amount Paid:',
                              '₱${NumberFormat('#,##0.00').format(amountPaid)}',
                              isBold: true,
                              valueColor: const Color(0xFF198754), // Green
                            ),
                            _buildReceiptDetailRow('Payment Method:', paymentMethod),
                            _buildReceiptDetailRowWidget(
                              'Status:',
                              _getStatusBadge(status),
                            ),
                          ],
                        ),
                        
                        const SizedBox(height: 20),
                        
                        // QR Code Section (always show if receipt number exists)
                        if (receiptNumber != 'N/A')
                          Center(
                            child: Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                border: Border.all(color: const Color(0xFF0d6efd), width: 2),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Column(
                                children: [
                                  if (referenceNumber != 'N/A')
                                    Text(
                                      'Reference Number: $referenceNumber',
                                      style: const TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF0d6efd),
                                      ),
                                    ),
                                  if (referenceNumber != 'N/A') const SizedBox(height: 12),
                                  _buildQRCodeImage(
                                    receiptNumber: receiptNumber,
                                    payerName: payerName,
                                    amountPaid: amountPaid,
                                    paymentDate: paymentDate,
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    'Scan this QR code to verify payment',
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: Colors.grey[600],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              ),
              
              // Footer with buttons
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: Colors.grey[100],
                  borderRadius: const BorderRadius.only(
                    bottomLeft: Radius.circular(10),
                    bottomRight: Radius.circular(10),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton.icon(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.close, size: 16),
                      label: const Text('Close'),
                      style: TextButton.styleFrom(
                        foregroundColor: Colors.grey[700],
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton.icon(
                      onPressed: () {
                        // TODO: Implement download QR
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Download QR feature coming soon')),
                        );
                      },
                      icon: const Icon(Icons.download, size: 16),
                      label: const Text('Download QR'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0d6efd),
                        foregroundColor: Colors.white,
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton.icon(
                      onPressed: () {
                        // TODO: Implement print receipt
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Print receipt feature coming soon')),
                        );
                      },
                      icon: const Icon(Icons.print, size: 16),
                      label: const Text('Print Receipt'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0d6efd),
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReceiptSection({
    required IconData icon,
    required String title,
    required List<Widget> children,
  }) {
    return Container(
      padding: const EdgeInsets.only(left: 12),
      decoration: BoxDecoration(
        border: Border(
          left: BorderSide(color: const Color(0xFF0d6efd), width: 2),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: const Color(0xFF0d6efd)),
              const SizedBox(width: 6),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF0d6efd),
                  letterSpacing: 0.3,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _buildReceiptDetailRow(
    String label,
    String value, {
    bool isBold = false,
    Color? valueColor,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w500,
                color: Colors.grey[600],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontSize: 11,
                fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
                color: valueColor ?? Colors.black87,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildReceiptDetailRowWidget(
    String label,
    Widget value,
  ) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w500,
                color: Colors.grey[600],
              ),
            ),
          ),
          Expanded(child: value),
        ],
      ),
    );
  }

  Widget _getStatusBadge(String status) {
    final statusText = status.toLowerCase() == 'fully paid' 
        ? 'COMPLETED' 
        : status.toUpperCase();
    // Use green for COMPLETED/fully paid, otherwise use the status color
    final color = (status.toLowerCase() == 'fully paid' || status.toLowerCase() == 'completed')
        ? const Color(0xFF198754) // Green
        : _getStatusColor(status);
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        statusText,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildQRCodeImage({
    required String receiptNumber,
    required String payerName,
    required double amountPaid,
    required String paymentDate,
  }) {
    // Generate QR code data - same format as web app
    final qrData = '$receiptNumber|$payerName|$amountPaid|$paymentDate';
    final encodedData = Uri.encodeComponent(qrData);
    
    // Use QR Server API (same as web app)
    final qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data=$encodedData';
    
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        border: Border.all(color: const Color(0xFF0d6efd), width: 2),
        borderRadius: BorderRadius.circular(6),
        color: Colors.white,
      ),
      child: Image.network(
        qrUrl,
        width: 150,
        height: 150,
        fit: BoxFit.contain,
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return const SizedBox(
            width: 150,
            height: 150,
            child: Center(
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
          );
        },
        errorBuilder: (context, error, stackTrace) {
          // Fallback to Google Charts API
          final fallbackUrl = 'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=$encodedData&choe=UTF-8';
          return Image.network(
            fallbackUrl,
            width: 150,
            height: 150,
            fit: BoxFit.contain,
            loadingBuilder: (context, child, loadingProgress) {
              if (loadingProgress == null) return child;
              return const SizedBox(
                width: 150,
                height: 150,
                child: Center(
                  child: CircularProgressIndicator(strokeWidth: 2),
                ),
              );
            },
            errorBuilder: (context, error, stackTrace) {
              return Container(
                width: 150,
                height: 150,
                decoration: BoxDecoration(
                  color: Colors.grey[200],
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.error_outline, color: Colors.grey[600], size: 32),
                    const SizedBox(height: 8),
                    Text(
                      'QR unavailable',
                      style: TextStyle(
                        fontSize: 10,
                        color: Colors.grey[600],
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }

  String _formatReceiptDate(String dateString) {
    if (dateString.isEmpty) return 'N/A';
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('MMMM dd, yyyy \'at\' hh:mm a').format(date);
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

  String _formatDate(String dateString) {
    if (dateString.isEmpty) return 'N/A';
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('MMM dd, yyyy • hh:mm a').format(date);
    } catch (e) {
      return dateString;
    }
  }
}
