import 'package:flutter/material.dart';
import '../screens/payment_requests_screen.dart' as payment_screen;
import '../screens/refund_requests_screen.dart' as refund_screen;
import '../services/api_service.dart';
import '../utils/toast_helper.dart';

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
          ToastHelper.showWarning(context, 'No active contributions available');
          return;
        }

        // Store parent context for toast overlay (so toast persists after modal closes)
        final parentContext = context;
        
        showDialog(
          context: context,
          builder: (dialogContext) => payment_screen.PaymentRequestDialog(
            contributions: contributions,
            preSelectedContribution: preSelectedContribution,
            parentContext: parentContext, // Pass parent context for toast
            onSubmitted: () {
              Navigator.pop(dialogContext);
            },
          ),
        );
      } else {
        ToastHelper.showError(context, response['error'] ?? 'Failed to load contributions');
      }
    } catch (e) {
      ToastHelper.showError(context, 'Error: ${e.toString()}');
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
        ToastHelper.showWarning(context, 'No refundable payments available');
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
      ToastHelper.showError(context, 'Error: ${e.toString()}');
    }
  }
}

