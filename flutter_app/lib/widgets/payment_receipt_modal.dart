import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../utils/logo_helper.dart';
import 'payment_receipt_modal_downloads.dart';

/// A reusable payment receipt modal widget that can be called from any screen
class PaymentReceiptModal {
  /// Show the payment receipt modal
  static void show(BuildContext context, Map<String, dynamic> payment) {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) => _PaymentReceiptDialog(payment: payment),
    );
  }
}

class _PaymentReceiptDialog extends StatelessWidget {
  final Map<String, dynamic> payment;

  const _PaymentReceiptDialog({required this.payment});

  @override
  Widget build(BuildContext context) {
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

    return Dialog(
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
                            // Logo Image
                            Image.network(
                              LogoHelper.getLogoUrl(),
                              width: 64,
                              height: 64,
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) {
                                // Fallback to icon if image fails to load
                                return const Icon(
                                  Icons.credit_card,
                                  size: 48,
                                  color: Color(0xFF0d6efd),
                                );
                              },
                              loadingBuilder: (context, child, loadingProgress) {
                                if (loadingProgress == null) return child;
                                return const SizedBox(
                                  width: 48,
                                  height: 48,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 3,
                                    valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF0d6efd)),
                                  ),
                                );
                              },
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

            // Footer with buttons - Horizontal layout
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
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => PaymentReceiptDownloads.downloadQRCode(
                        context,
                        receiptNumber,
                        referenceNumber,
                        payerName,
                        amountPaid,
                        paymentDate,
                        (message, color) => _showToast(context, message, color),
                      ),
                      icon: const Icon(Icons.download, size: 16),
                      label: const Text('Download QR'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0d6efd),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => PaymentReceiptDownloads.downloadReceipt(
                        context,
                        payment,
                        payerName,
                        payerId,
                        contactNumber,
                        emailAddress,
                        contributionTitle,
                        formattedDate,
                        amountPaid,
                        paymentMethod,
                        status,
                        receiptNumber,
                        referenceNumber,
                        (message, color) => _showToast(context, message, color),
                      ),
                      icon: const Icon(Icons.download, size: 16),
                      label: const Text('Download Receipt'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0d6efd),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      ),
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
          Flexible(child: value),
        ],
      ),
    );
  }

  Widget _getStatusBadge(String status) {
    final statusText = status.toLowerCase() == 'fully paid'
        ? 'COMPLETED'
        : status.toUpperCase();
    // Use green for COMPLETED/fully paid, otherwise use the status color
    final color = (status.toLowerCase() == 'fully paid' ||
            status.toLowerCase() == 'completed')
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
    final qrUrl =
        'https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data=$encodedData';

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
          final fallbackUrl =
              'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=$encodedData&choe=UTF-8';
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

  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value.replaceAll(',', '')) ?? 0.0;
    }
    return 0.0;
  }


  /// Show toast notification above modal (appears at top of screen)
  void _showToast(BuildContext context, String message, Color backgroundColor) {
    // Use Navigator overlay to ensure toast appears above modal
    final navigator = Navigator.of(context, rootNavigator: true);
    final overlay = navigator.overlay;
    if (overlay == null) return;

    final overlayEntry = OverlayEntry(
      builder: (context) => Positioned(
        top: MediaQuery.of(context).padding.top + 16,
        left: 16,
        right: 16,
        child: Material(
          color: Colors.transparent,
          child: SafeArea(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: backgroundColor,
                borderRadius: BorderRadius.circular(8),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Icon(
                    backgroundColor == Colors.green || backgroundColor == const Color(0xFF198754)
                        ? Icons.check_circle
                        : backgroundColor == Colors.orange || backgroundColor == const Color(0xFFFF9800)
                            ? Icons.warning
                            : Icons.error,
                    color: Colors.white,
                    size: 20,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      message,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                      ),
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );

    overlay.insert(overlayEntry);

    // Remove after 4 seconds
    Future.delayed(const Duration(seconds: 4), () {
      overlayEntry.remove();
    });
  }
}

