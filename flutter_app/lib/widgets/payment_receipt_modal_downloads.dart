import 'dart:convert';
import 'dart:typed_data';
import 'dart:io' show Platform, File;
// Conditional import for web-only features
import '../utils/html_stub.dart' if (dart.library.html) 'dart:html' as html;
import '../utils/html_stub.dart' if (dart.library.html) 'dart:html' show window;
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:intl/intl.dart';
import 'package:http/http.dart' as http;
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';
import 'package:permission_handler/permission_handler.dart';
import '../utils/logo_helper.dart';

/// Helper class for downloading QR codes and receipts (works on both web and mobile)
class PaymentReceiptDownloads {
  /// Download QR code as PNG image (works on both web and mobile)
  static Future<void> downloadQRCode(
    BuildContext context,
    String receiptNumber,
    String referenceNumber,
    String payerName,
    double amountPaid,
    String paymentDate,
    Function(String, Color) showToast,
  ) async {
    try {
      // Generate QR code data
      final qrData = '$receiptNumber|$payerName|$amountPaid|$paymentDate';
      final encodedData = Uri.encodeComponent(qrData);
      final qrUrl =
          'https://api.qrserver.com/v1/create-qr-code/?size=300x300&ecc=H&data=$encodedData';

      // Download QR code image
      final response = await http.get(Uri.parse(qrUrl));
      if (response.statusCode != 200) {
        throw Exception('Failed to download QR code');
      }

      final qrImageBytes = response.bodyBytes;
      final fileName = 'QR_Receipt_${referenceNumber.replaceAll(RegExp(r'[^a-zA-Z0-9]'), '_')}.png';

      if (kIsWeb) {
        // Web: Use HTML5 download
        await _downloadQRCodeWeb(qrImageBytes, fileName, referenceNumber, showToast);
      } else {
        // Mobile: Save to device and share
        await _downloadQRCodeMobile(context, qrImageBytes, fileName, showToast);
      }
    } catch (e) {
      if (context.mounted) {
        showToast('Failed to download QR code: ${e.toString()}', Colors.red);
      }
    }
  }

  /// Web implementation for QR code download
  static Future<void> _downloadQRCodeWeb(
    Uint8List qrImageBytes,
    String fileName,
    String referenceNumber,
    Function(String, Color) showToast,
  ) async {
    if (kIsWeb) {
      try {
        final qrImageBlob = html.Blob([qrImageBytes]);
        final qrImageUrl = html.Url.createObjectUrlFromBlob(qrImageBlob);

        // Load QR code image
        final qrImage = html.ImageElement();
        qrImage.src = qrImageUrl;
        await qrImage.onLoad.first;

        // Load logo image
        final logoUrl = LogoHelper.getLogoUrl();
        final logoImage = html.ImageElement();
        logoImage.src = logoUrl;
        logoImage.crossOrigin = 'anonymous';

        bool logoLoaded = false;
        try {
          await logoImage.onLoad.first.timeout(const Duration(seconds: 3));
          logoLoaded = true;
        } catch (e) {
          logoLoaded = false;
        }

        final qrSize = 300;
        final logoDrawSize = logoLoaded ? 50 : 0;
        final padding = 24;
        final spacing = 12;
        final titleHeight = 20;
        final refNumberHeight = 16;

        // Calculate canvas dimensions
        final canvasWidth = qrSize + (padding * 2);
        final totalHeight = padding +
            (logoDrawSize > 0 ? logoDrawSize + spacing : 0) +
            titleHeight +
            spacing +
            qrSize +
            spacing +
            refNumberHeight +
            padding;

        // Create canvas
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

        double currentY = padding.toDouble();

        // Draw logo
        if (logoLoaded && logoDrawSize > 0) {
          final logoX = (canvas.width! - logoDrawSize) / 2;
          canvasCtx.drawImageScaled(logoImage, logoX.toDouble(), currentY.toDouble(), logoDrawSize.toDouble(), logoDrawSize.toDouble());
          currentY += logoDrawSize + spacing;
        }

        // Draw ClearPay title
        canvasCtx.fillStyle = '#0d6efd';
        canvasCtx.font = 'bold 18px Arial';
        canvasCtx.textAlign = 'center';
        canvasCtx.textBaseline = 'top';
        canvasCtx.fillText('ClearPay', (canvas.width! / 2).toDouble(), currentY);
        currentY += titleHeight + spacing;

        // Draw QR code
        final qrX = (canvas.width! - qrSize) / 2;
        canvasCtx.drawImageScaled(qrImage, qrX.toDouble(), currentY, qrSize.toDouble(), qrSize.toDouble());
        currentY += qrSize + spacing;

        // Draw reference number
        canvasCtx.fillStyle = '#212529';
        canvasCtx.font = '12px Arial';
        canvasCtx.textAlign = 'center';
        canvasCtx.fillText(referenceNumber, (canvas.width! / 2).toDouble(), currentY);

        // Convert to blob and download
        final blob = await canvas.toBlob();
        if (blob != null) {
          final url = html.Url.createObjectUrlFromBlob(blob);
          final anchor = html.AnchorElement(href: url)
            ..setAttribute('download', fileName)
            ..click();
          html.Url.revokeObjectUrl(url);
        }

        html.Url.revokeObjectUrl(qrImageUrl);
        showToast('QR code downloaded successfully', Colors.green);
      } catch (e) {
        showToast('Failed to download QR code: ${e.toString()}', Colors.red);
      }
    }
  }

  /// Mobile implementation for QR code download
  static Future<void> _downloadQRCodeMobile(
    BuildContext context,
    Uint8List qrImageBytes,
    String fileName,
    Function(String, Color) showToast,
  ) async {
    try {
      // Request storage permission (Android)
      if (Platform.isAndroid) {
        final status = await Permission.storage.request();
        if (!status.isGranted) {
          showToast('Storage permission is required to save files', Colors.orange);
          return;
        }
      }

      // Get temporary directory
      final directory = await getTemporaryDirectory();
      final filePath = '${directory.path}/$fileName';
      final file = File(filePath);

      // Write file
      await file.writeAsBytes(qrImageBytes);

      // Share the file
      final xFile = XFile(filePath);
      await Share.shareXFiles([xFile], text: 'ClearPay Payment Receipt QR Code');

      showToast('QR code saved and ready to share', Colors.green);
    } catch (e) {
      showToast('Failed to save QR code: ${e.toString()}', Colors.red);
    }
  }

  /// Download receipt as PDF/HTML (works on both web and mobile)
  static Future<void> downloadReceipt(
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
    Function(String, Color) showToast,
  ) async {
    try {
      // Generate QR code data URL
      final qrData = '$receiptNumber|$payerName|$amountPaid|${payment['payment_date'] ?? payment['created_at'] ?? ''}';
      final encodedData = Uri.encodeComponent(qrData);
      final qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data=$encodedData';

      // Download QR code
      final qrResponse = await http.get(Uri.parse(qrUrl));
      final qrImageBytes = qrResponse.bodyBytes;
      final qrBase64 = base64Encode(qrImageBytes);
      final qrDataUrl = 'data:image/png;base64,$qrBase64';

      // Get logo URL
      final logoUrl = LogoHelper.getLogoUrl();

      // Create HTML content
      final statusText = status.toLowerCase() == 'fully paid' ? 'COMPLETED' : status.toUpperCase();
      final statusColor = (status.toLowerCase() == 'fully paid' || status.toLowerCase() == 'completed') ? '#198754' : '#6c757d';

      final htmlContent = _generateReceiptHTML(
        receiptNumber,
        formattedDate,
        payerName,
        payerId,
        contactNumber,
        emailAddress,
        contributionTitle,
        amountPaid,
        paymentMethod,
        statusText,
        statusColor,
        referenceNumber,
        qrDataUrl,
        logoUrl,
      );

      if (kIsWeb) {
        await _downloadReceiptWeb(htmlContent, receiptNumber, showToast);
      } else {
        await _downloadReceiptMobile(context, htmlContent, receiptNumber, showToast);
      }
    } catch (e) {
      if (context.mounted) {
        showToast('Failed to download receipt: ${e.toString()}', Colors.red);
      }
    }
  }

  /// Web implementation for receipt download
  static Future<void> _downloadReceiptWeb(
    String htmlContent,
    String receiptNumber,
    Function(String, Color) showToast,
  ) async {
    if (kIsWeb) {
      try {
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

        final blob = html.Blob([htmlWithScript], 'text/html');
        final url = html.Url.createObjectUrlFromBlob(blob);
        window.open(url, '_blank');

        Future.delayed(const Duration(seconds: 2), () {
          html.Url.revokeObjectUrl(url);
        });

        showToast('Receipt opened for printing. Use "Save as PDF" in the print dialog.', Colors.green);
      } catch (e) {
        showToast('Failed to open receipt: ${e.toString()}', Colors.red);
      }
    }
  }

  /// Mobile implementation for receipt download
  static Future<void> _downloadReceiptMobile(
    BuildContext context,
    String htmlContent,
    String receiptNumber,
    Function(String, Color) showToast,
  ) async {
    try {
      // Request storage permission (Android)
      if (Platform.isAndroid) {
        final status = await Permission.storage.request();
        if (!status.isGranted) {
          showToast('Storage permission is required to save files', Colors.orange);
          return;
        }
      }

      // Get temporary directory
      final directory = await getTemporaryDirectory();
      final fileName = 'Receipt_${receiptNumber.replaceAll(RegExp(r'[^a-zA-Z0-9]'), '_')}.html';
      final filePath = '${directory.path}/$fileName';
      final file = File(filePath);

      // Write HTML file
      await file.writeAsString(htmlContent);

      // Share the file
      final xFile = XFile(filePath);
      await Share.shareXFiles([xFile], text: 'ClearPay Payment Receipt');

      showToast('Receipt saved and ready to share', Colors.green);
    } catch (e) {
      showToast('Failed to save receipt: ${e.toString()}', Colors.red);
    }
  }

  /// Generate HTML content for receipt
  static String _generateReceiptHTML(
    String receiptNumber,
    String formattedDate,
    String payerName,
    String payerId,
    String contactNumber,
    String emailAddress,
    String contributionTitle,
    double amountPaid,
    String paymentMethod,
    String statusText,
    String statusColor,
    String referenceNumber,
    String qrDataUrl,
    String logoUrl,
  ) {
    return '''
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Payment Receipt - $receiptNumber</title>
  <style>
    @page { size: A4; margin: 20mm; }
    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #212529; }
    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0d6efd; padding-bottom: 20px; }
    .header img { width: 64px; height: 64px; margin-bottom: 10px; object-fit: contain; }
    .header h1 { color: #0d6efd; margin: 0; font-size: 24px; font-weight: bold; }
    .header p { color: #6c757d; margin: 5px 0 0 0; font-size: 14px; }
    .receipt-info { display: flex; justify-content: space-between; margin-bottom: 30px; }
    .info-card { background: #f8f9fa; padding: 15px; border-radius: 8px; flex: 1; margin: 0 10px; }
    .info-card:first-child { margin-left: 0; }
    .info-card:last-child { margin-right: 0; }
    .info-card label { display: block; font-size: 11px; color: #6c757d; margin-bottom: 5px; }
    .info-card .value { display: block; font-size: 14px; font-weight: bold; color: #212529; }
    .section { margin-bottom: 25px; border-left: 3px solid #0d6efd; padding-left: 15px; }
    .section-title { color: #0d6efd; font-size: 14px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
    .detail-row { display: flex; margin-bottom: 10px; }
    .detail-label { width: 120px; font-size: 12px; color: #6c757d; font-weight: 500; }
    .detail-value { flex: 1; font-size: 12px; color: #212529; }
    .detail-value.bold { font-weight: bold; }
    .detail-value.green { color: #198754; font-weight: bold; }
    .status-badge { display: inline-block; padding: 4px 12px; background: $statusColor; color: white; border-radius: 12px; font-size: 10px; font-weight: bold; }
    .qr-section { text-align: center; margin: 30px 0; padding: 20px; border: 2px solid #0d6efd; border-radius: 8px; }
    .qr-section img { max-width: 150px; max-height: 150px; margin: 10px 0; }
    .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 20px; }
    @media print { body { margin: 0; padding: 10px; } .qr-section { page-break-inside: avoid; } }
  </style>
</head>
<body>
  <div class="header">
    <img src="$logoUrl" alt="ClearPay Logo" onerror="this.style.display='none';">
    <h1>ClearPay</h1>
    <p>Payment Receipt</p>
  </div>
  <div class="receipt-info">
    <div class="info-card"><label>Receipt Number</label><div class="value">$receiptNumber</div></div>
    <div class="info-card"><label>Payment Date</label><div class="value">$formattedDate</div></div>
  </div>
  <div class="section">
    <div class="section-title">👤 Payer Information</div>
    <div class="detail-row"><div class="detail-label">Name:</div><div class="detail-value bold">$payerName</div></div>
    <div class="detail-row"><div class="detail-label">ID:</div><div class="detail-value">$payerId</div></div>
    <div class="detail-row"><div class="detail-label">Contact:</div><div class="detail-value">$contactNumber</div></div>
    <div class="detail-row"><div class="detail-label">Email:</div><div class="detail-value">$emailAddress</div></div>
  </div>
  <div class="section">
    <div class="section-title">💳 Payment Details</div>
    <div class="detail-row"><div class="detail-label">Contribution:</div><div class="detail-value bold">$contributionTitle</div></div>
    <div class="detail-row"><div class="detail-label">Amount Paid:</div><div class="detail-value green">₱${NumberFormat('#,##0.00').format(amountPaid)}</div></div>
    <div class="detail-row"><div class="detail-label">Payment Method:</div><div class="detail-value">$paymentMethod</div></div>
    <div class="detail-row"><div class="detail-label">Status:</div><div class="detail-value"><span class="status-badge">$statusText</span></div></div>
  </div>
  ${referenceNumber != 'N/A' ? '<div class="section"><div class="section-title">📱 Reference Number</div><div class="detail-row"><div class="detail-value bold">$referenceNumber</div></div></div>' : ''}
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
</html>''';
  }
}

