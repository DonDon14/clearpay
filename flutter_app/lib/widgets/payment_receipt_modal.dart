import 'dart:convert';
import 'dart:typed_data';
// Conditional import for web-only features
import '../utils/html_stub.dart' if (dart.library.html) 'dart:html' as html;
// Import window separately for web
import '../utils/html_stub.dart' if (dart.library.html) 'dart:html' show window;
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:http/http.dart' as http;
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../utils/logo_helper.dart';

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
                    onPressed: () => _downloadQRCode(
                      context,
                      receiptNumber,
                      referenceNumber,
                      payerName,
                      amountPaid,
                      paymentDate,
                    ),
                    icon: const Icon(Icons.download, size: 16),
                    label: const Text('Download QR'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0d6efd),
                      foregroundColor: Colors.white,
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton.icon(
                    onPressed: () => _downloadReceipt(
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
                    ),
                    icon: const Icon(Icons.download, size: 16),
                    label: const Text('Download Receipt'),
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

  /// Download QR code as PNG image (same as web app)
  Future<void> _downloadQRCode(
    BuildContext context,
    String receiptNumber,
    String referenceNumber,
    String payerName,
    double amountPaid,
    String paymentDate,
  ) async {
    if (!kIsWeb) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Download QR is only available on web')),
      );
      return;
    }

    try {
      // Generate QR code data
      final qrData = '$receiptNumber|$payerName|$amountPaid|$paymentDate';
      final encodedData = Uri.encodeComponent(qrData);
      final qrUrl =
          'https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data=$encodedData';

      // Download QR code image
      final response = await http.get(Uri.parse(qrUrl));
      if (response.statusCode != 200) {
        throw Exception('Failed to download QR code');
      }

      final qrImageBytes = response.bodyBytes;
      final qrImageBlob = html.Blob([qrImageBytes]);
      final qrImageUrl = html.Url.createObjectUrlFromBlob(qrImageBlob);

      // Load QR code image first to get its size
      final qrImage = html.ImageElement();
      qrImage.src = qrImageUrl;
      await qrImage.onLoad.first;

      // Load logo image
      final logoUrl = LogoHelper.getLogoUrl();
      final logoImage = html.ImageElement();
      logoImage.src = logoUrl;
      logoImage.crossOrigin = 'anonymous'; // Required for canvas drawing
      
      // Wait for logo to load, but don't fail if it doesn't
      bool logoLoaded = false;
      try {
        await logoImage.onLoad.first.timeout(const Duration(seconds: 3));
        logoLoaded = true;
      } catch (e) {
        // Logo failed to load, continue without it
        logoLoaded = false;
      }

      final qrSize = qrImage.naturalWidth > 0 ? qrImage.naturalWidth : 150;
      final logoSize = logoLoaded && logoImage.naturalWidth > 0 ? logoImage.naturalWidth : 0;
      final logoDrawSize = logoLoaded && logoSize > 0 ? (logoSize > 50 ? 50 : logoSize) : 0;
      final padding = 24; // Padding around the entire image
      final spacing = 12; // Spacing between elements

      // Calculate text width to ensure proper fit
      final tempCanvas = html.CanvasElement();
      final tempCtx = tempCanvas.context2D;
      tempCtx.font = 'bold 14px Arial';
      final clearPayTextWidth = tempCtx.measureText('ClearPay').width ?? 0;
      tempCtx.font = '12px Arial';
      final textWidth = tempCtx.measureText(referenceNumber).width ?? 0;
      
      // Determine canvas width based on the widest element
      final maxContentWidth = [
        qrSize,
        logoDrawSize,
        clearPayTextWidth.round(),
        (textWidth + (padding * 2)).round()
      ].reduce((a, b) => a > b ? a : b);
      final canvasWidth = maxContentWidth + (padding * 2);

      // Calculate heights for each section
      final logoHeight = logoLoaded && logoDrawSize > 0 ? logoDrawSize : 0;
      final logoSpacing = logoHeight > 0 ? spacing : 0;
      final titleHeight = 20; // Height for "ClearPay" text
      final titleSpacing = spacing;
      final qrSpacing = spacing;
      final refNumberHeight = 16; // Height for reference number text
      final refNumberSpacing = spacing;

      // Calculate total height
      final totalHeight = padding + // Top padding
          logoHeight + // Logo
          logoSpacing + // Space after logo
          titleHeight + // "ClearPay" text
          titleSpacing + // Space after title
          qrSize + // QR code
          qrSpacing + // Space after QR code
          refNumberHeight + // Reference number
          padding; // Bottom padding

      // Create canvas with calculated dimensions
      final canvas = html.CanvasElement(
        width: canvasWidth,
        height: totalHeight,
      );
      final canvasCtx = canvas.context2D;

      // Fill white background
      canvasCtx.fillStyle = '#ffffff';
      canvasCtx.fillRect(0, 0, canvas.width!.toDouble(), canvas.height!.toDouble());

      // Add blue border
      canvasCtx.strokeStyle = '#0d6efd';
      canvasCtx.lineWidth = 2;
      canvasCtx.strokeRect(1.0, 1.0, (canvas.width! - 2).toDouble(), (canvas.height! - 2).toDouble());

      // Draw elements with compact spacing
      double currentY = padding.toDouble();

      // Draw logo at the top (centered)
      if (logoLoaded && logoDrawSize > 0) {
        final logoX = (canvas.width! - logoDrawSize) / 2;
        canvasCtx.drawImageScaled(logoImage, logoX.toDouble(), currentY.toDouble(), logoDrawSize.toDouble(), logoDrawSize.toDouble());
        currentY += logoDrawSize + logoSpacing;
      }

      // Add ClearPay title below logo
      canvasCtx.fillStyle = '#0d6efd';
      canvasCtx.font = 'bold 18px Arial';
      canvasCtx.textAlign = 'center';
      canvasCtx.textBaseline = 'top';
      canvasCtx.fillText('ClearPay', (canvas.width! / 2).toDouble(), currentY);
      currentY += titleHeight + titleSpacing;

      // Draw QR code (centered)
      final qrX = (canvas.width! - qrSize) / 2;
      canvasCtx.drawImageScaled(qrImage, qrX.toDouble(), currentY, qrSize.toDouble(), qrSize.toDouble());
      currentY += qrSize + qrSpacing;

      // Add reference number at the bottom (centered)
      canvasCtx.fillStyle = '#212529';
      canvasCtx.font = '12px Arial';
      canvasCtx.textAlign = 'center';
      canvasCtx.textBaseline = 'top';
      canvasCtx.fillText(referenceNumber, (canvas.width! / 2).toDouble(), currentY);

      // Convert canvas to blob and download
      final blob = await canvas.toBlob();
      if (blob != null) {
        final url = html.Url.createObjectUrlFromBlob(blob);
        final anchor = html.AnchorElement(href: url)
          ..setAttribute('download', 'QR_Receipt_${referenceNumber.replaceAll(RegExp(r'[^a-zA-Z0-9]'), '_')}.png')
          ..click();
        html.Url.revokeObjectUrl(url);
      }

      // Clean up
      html.Url.revokeObjectUrl(qrImageUrl);

      // Show toast notification after a short delay to appear above modal
      if (context.mounted) {
        Future.delayed(const Duration(milliseconds: 100), () {
          if (context.mounted) {
            _showToast(context, 'QR code downloaded successfully', Colors.green);
          }
        });
      }
    } catch (e) {
      if (context.mounted) {
        Future.delayed(const Duration(milliseconds: 100), () {
          if (context.mounted) {
            _showToast(context, 'Failed to download QR code: ${e.toString()}', Colors.red);
          }
        });
      }
    }
  }

  /// Download receipt as PDF/HTML (web only)
  Future<void> _downloadReceipt(
    BuildContext context,
    Map<String, dynamic> payment,
    String payerName,
    String payerId,
    String contactNumber,
    String emailAddress,
    String contributionTitle,
    String formattedDate,
    double amountPaid,
    String paymentMethod,
    String status,
    String receiptNumber,
    String referenceNumber,
  ) async {
    if (!kIsWeb) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Download receipt is only available on web')),
      );
      return;
    }

    try {
      // Generate QR code data URL
      final qrData = '$receiptNumber|$payerName|$amountPaid|${payment['payment_date'] ?? payment['created_at'] ?? ''}';
      final encodedData = Uri.encodeComponent(qrData);
      final qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data=$encodedData';
      
      // Download QR code and convert to data URL
      final qrResponse = await http.get(Uri.parse(qrUrl));
      final qrImageBytes = qrResponse.bodyBytes;
      // Convert bytes to base64
      final qrBase64 = base64Encode(qrImageBytes);
      final qrDataUrl = 'data:image/png;base64,$qrBase64';

      // Get logo URL for HTML receipt
      final logoUrl = LogoHelper.getLogoUrl();

      // Create HTML content for receipt
      final statusText = status.toLowerCase() == 'fully paid'
          ? 'COMPLETED'
          : status.toUpperCase();
      final statusColor = (status.toLowerCase() == 'fully paid' ||
              status.toLowerCase() == 'completed')
          ? '#198754'
          : '#6c757d';

      final htmlContent = '''
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Payment Receipt - $receiptNumber</title>
  <style>
    @page {
      size: A4;
      margin: 20mm;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      color: #212529;
    }
    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 2px solid #0d6efd;
      padding-bottom: 20px;
    }
    .header img {
      width: 64px;
      height: 64px;
      margin-bottom: 10px;
      object-fit: contain;
    }
    .header h1 {
      color: #0d6efd;
      margin: 0;
      font-size: 24px;
      font-weight: bold;
    }
    .header p {
      color: #6c757d;
      margin: 5px 0 0 0;
      font-size: 14px;
    }
    .receipt-info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
    }
    .info-card {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      flex: 1;
      margin: 0 10px;
    }
    .info-card:first-child {
      margin-left: 0;
    }
    .info-card:last-child {
      margin-right: 0;
    }
    .info-card label {
      display: block;
      font-size: 11px;
      color: #6c757d;
      margin-bottom: 5px;
    }
    .info-card .value {
      display: block;
      font-size: 14px;
      font-weight: bold;
      color: #212529;
    }
    .section {
      margin-bottom: 25px;
      border-left: 3px solid #0d6efd;
      padding-left: 15px;
    }
    .section-title {
      color: #0d6efd;
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .detail-row {
      display: flex;
      margin-bottom: 10px;
    }
    .detail-label {
      width: 120px;
      font-size: 12px;
      color: #6c757d;
      font-weight: 500;
    }
    .detail-value {
      flex: 1;
      font-size: 12px;
      color: #212529;
    }
    .detail-value.bold {
      font-weight: bold;
    }
    .detail-value.green {
      color: #198754;
      font-weight: bold;
    }
    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      background: $statusColor;
      color: white;
      border-radius: 12px;
      font-size: 10px;
      font-weight: bold;
      width: fit-content;
    }
    .qr-section {
      text-align: center;
      margin: 30px 0;
      padding: 20px;
      border: 2px solid #0d6efd;
      border-radius: 8px;
    }
    .qr-section img {
      max-width: 150px;
      max-height: 150px;
      margin: 10px 0;
    }
    .footer {
      margin-top: 40px;
      text-align: center;
      font-size: 10px;
      color: #6c757d;
      border-top: 1px solid #dee2e6;
      padding-top: 20px;
    }
    @media print {
      body {
        margin: 0;
        padding: 10px;
      }
      .qr-section {
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="$logoUrl" alt="ClearPay Logo" onerror="this.style.display='none';">
    <h1>ClearPay</h1>
    <p>Payment Receipt</p>
  </div>

  <div class="receipt-info">
    <div class="info-card">
      <label>Receipt Number</label>
      <div class="value">$receiptNumber</div>
    </div>
    <div class="info-card">
      <label>Payment Date</label>
      <div class="value">$formattedDate</div>
    </div>
  </div>

  <div class="section">
    <div class="section-title">👤 Payer Information</div>
    <div class="detail-row">
      <div class="detail-label">Name:</div>
      <div class="detail-value bold">$payerName</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">ID:</div>
      <div class="detail-value">$payerId</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Contact:</div>
      <div class="detail-value">$contactNumber</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Email:</div>
      <div class="detail-value">$emailAddress</div>
    </div>
  </div>

  <div class="section">
    <div class="section-title">💳 Payment Details</div>
    <div class="detail-row">
      <div class="detail-label">Contribution:</div>
      <div class="detail-value bold">$contributionTitle</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Amount Paid:</div>
      <div class="detail-value green">₱${NumberFormat('#,##0.00').format(amountPaid)}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Payment Method:</div>
      <div class="detail-value">$paymentMethod</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Status:</div>
      <div class="detail-value">
        <span class="status-badge">$statusText</span>
      </div>
    </div>
  </div>

  ${referenceNumber != 'N/A' ? '''
  <div class="section">
    <div class="section-title">📱 Reference Number</div>
    <div class="detail-row">
      <div class="detail-value bold">$referenceNumber</div>
    </div>
  </div>
  ''' : ''}

  <div class="qr-section">
    <div class="section-title">📱 QR Code</div>
    <img src="$qrDataUrl" alt="QR Code" />
    ${referenceNumber != 'N/A' ? '<p style="margin-top: 10px; font-weight: bold; color: #0d6efd;">$referenceNumber</p>' : ''}
    <p style="font-size: 10px; color: #6c757d; margin-top: 5px;">Scan this QR code to verify payment</p>
  </div>

  <div class="footer">
    <p>This receipt is digitally signed and verified by ClearPay</p>
    <p>Generated on ${DateFormat('MMMM dd, yyyy \'at\' hh:mm a').format(DateTime.now())}</p>
  </div>
</body>
</html>
''';

      // Add auto-print script to HTML
      final htmlWithScript = htmlContent.replaceAll(
        '</body>',
        '''
  <script>
    window.onload = function() {
      setTimeout(function() {
        window.print();
      }, 500);
    };
  </script>
</body>''',
      );
      
      // Create blob URL and open in new window for printing (which can save as PDF)
      final blob = html.Blob([htmlWithScript], 'text/html');
      final url = html.Url.createObjectUrlFromBlob(blob);
      
      // Open in new window
      if (kIsWeb) {
        window.open(url, '_blank');
      }
      
      // Clean up blob URL after a delay
      Future.delayed(const Duration(seconds: 2), () {
        html.Url.revokeObjectUrl(url);
      });

      // Show toast notification
      if (context.mounted) {
        Future.delayed(const Duration(milliseconds: 100), () {
          if (context.mounted) {
            _showToast(context, 'Receipt opened for printing. Use "Save as PDF" in the print dialog.', Colors.green);
          }
        });
      }
    } catch (e) {
      if (context.mounted) {
        Future.delayed(const Duration(milliseconds: 100), () {
          if (context.mounted) {
            _showToast(context, 'Failed to download receipt: ${e.toString()}', Colors.red);
          }
        });
      }
    }
  }

  /// Show toast notification above modal
  void _showToast(BuildContext context, String message, Color backgroundColor) {
    final overlay = Overlay.of(context);
    final overlayEntry = OverlayEntry(
      builder: (context) => Positioned(
        top: 100,
        left: MediaQuery.of(context).size.width / 2 - 150,
        child: Material(
          color: Colors.transparent,
          child: Container(
            width: 300,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: backgroundColor,
              borderRadius: BorderRadius.circular(8),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.2),
                  blurRadius: 8,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  backgroundColor == Colors.green ? Icons.check_circle : Icons.error,
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
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );

    overlay.insert(overlayEntry);

    // Remove after 3 seconds
    Future.delayed(const Duration(seconds: 3), () {
      overlayEntry.remove();
    });
  }
}

