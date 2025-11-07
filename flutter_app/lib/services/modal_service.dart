import 'package:flutter/material.dart';
import '../screens/payment_requests_screen.dart' as payment_screen;
import '../screens/refund_requests_screen.dart' as refund_screen;
import '../services/api_service.dart';

class ModalService {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  /// Show payment request modal from anywhere in the app
  static Future<void> showPaymentRequestModal({Map<String, dynamic>? preSelectedContribution}) async {
    final context = navigatorKey.currentContext;
    if (context == null) {
      debugPrint('ModalService: No navigator context available');
      return;
    }

    try {
      // Load contributions and payment methods
      final response = await ApiService.getPaymentRequests();
      
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];
        final contributions = data['contributions'] ?? [];
        
        if (contributions.isEmpty && preSelectedContribution == null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('No active contributions available')),
          );
          return;
        }

        showDialog(
          context: context,
          builder: (context) => payment_screen.PaymentRequestDialog(
            contributions: contributions,
            preSelectedContribution: preSelectedContribution,
            onSubmitted: () {
              Navigator.pop(context);
            },
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['error'] ?? 'Failed to load contributions'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: ${e.toString()}'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  /// Show refund request modal from anywhere in the app
  static Future<void> showRefundRequestModal() async {
    final context = navigatorKey.currentContext;
    if (context == null) {
      debugPrint('ModalService: No navigator context available');
      return;
    }

    try {
      // Load refundable payments and refund methods
      final refundRequestsResponse = await ApiService.getRefundRequests();
      final refundMethodsResponse = await ApiService.getActiveRefundMethods();

      List<dynamic> refundablePayments = [];
      List<dynamic> refundMethods = [];

      if (refundRequestsResponse['success'] == true) {
        final data = refundRequestsResponse['data'];
        refundablePayments = data['refundable_payments'] ?? data['refundablePayments'] ?? [];
      }

      if (refundMethodsResponse['success'] == true) {
        refundMethods = refundMethodsResponse['methods'] ?? [];
      }

      if (refundablePayments.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No refundable payments available')),
        );
        return;
      }

      showDialog(
        context: context,
        builder: (context) => refund_screen.RefundRequestDialog(
          refundablePayments: refundablePayments,
          refundMethods: refundMethods,
          onSubmitted: () {
            Navigator.pop(context);
          },
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: ${e.toString()}'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}

