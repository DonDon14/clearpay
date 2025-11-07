import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../widgets/notion_app_bar.dart';
import '../widgets/navigation_drawer.dart';

class RefundRequestsScreen extends StatefulWidget {
  final bool showAppBar;
  
  const RefundRequestsScreen({super.key, this.showAppBar = true});

  @override
  State<RefundRequestsScreen> createState() => _RefundRequestsScreenState();
}

class _RefundRequestsScreenState extends State<RefundRequestsScreen> {
  bool _isLoading = true;
  List<dynamic> _refundRequests = [];
  List<dynamic> _refundablePayments = [];
  List<dynamic> _refundMethods = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final refundRequestsResponse = await ApiService.getRefundRequests();
      
      // Debug: Print response structure
      print('Refund Requests Response: $refundRequestsResponse');
      
      if (refundRequestsResponse['success'] == true) {
        final data = refundRequestsResponse['data'];
        print('Refund Requests Data: $data');
        
        setState(() {
          // API returns snake_case, handle both formats
          final refundRequestsList = data['refund_requests'] ?? data['refundRequests'];
          final refundablePaymentsList = data['refundable_payments'] ?? data['refundablePayments'];
          
          _refundRequests = refundRequestsList is List ? refundRequestsList : [];
          _refundablePayments = refundablePaymentsList is List ? refundablePaymentsList : [];
          
          print('Refund Requests Count: ${_refundRequests.length}');
          print('Refundable Payments Count: ${_refundablePayments.length}');
        });
      } else {
        setState(() {
          _errorMessage = refundRequestsResponse['error'] ?? refundRequestsResponse['message'] ?? 'Failed to load refund requests';
        });
      }

      final refundMethodsResponse = await ApiService.getActiveRefundMethods();
      if (refundMethodsResponse['success'] == true) {
        setState(() {
          _refundMethods = refundMethodsResponse['methods'] ?? [];
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Failed to load data: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _showRefundRequestDialog() {
    if (_refundablePayments.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No refundable payments available')),
      );
      return;
    }

    showDialog(
      context: context,
      builder: (context) => RefundRequestDialog(
        refundablePayments: _refundablePayments,
        refundMethods: _refundMethods,
        onSubmitted: () {
          Navigator.pop(context);
          _loadData();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final body = _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? _buildErrorWidget()
              : RefreshIndicator(
                  onRefresh: _loadData,
                  child: _refundRequests.isEmpty
                      ? _buildEmptyState()
                      : _buildRefundRequestsList(),
                );
    
    if (widget.showAppBar) {
      return Scaffold(
        drawer: const AppNavigationDrawer(),
        appBar: NotionAppBar(
          title: 'Refund Requests',
          onRefresh: _loadData,
        ),
        body: body,
        floatingActionButton: FloatingActionButton.extended(
          onPressed: _showRefundRequestDialog,
          icon: const Icon(Icons.add),
          label: const Text('Request Refund'),
          backgroundColor: const Color(0xFFFF9800),
        ),
      );
    } else {
      return Scaffold(
        drawer: const AppNavigationDrawer(),
        body: body,
        floatingActionButton: FloatingActionButton.extended(
          onPressed: _showRefundRequestDialog,
          icon: const Icon(Icons.add),
          label: const Text('Request Refund'),
          backgroundColor: const Color(0xFFFF9800),
        ),
      );
    }
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
            onPressed: _loadData,
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
            'No Refund Requests',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
          const SizedBox(height: 8),
          Text(
            'You haven\'t submitted any refund requests yet.',
            style: TextStyle(color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildRefundRequestsList() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _refundRequests.length,
      itemBuilder: (context, index) {
        final request = _refundRequests[index];
        return _buildRefundRequestCard(request);
      },
    );
  }

  // Helper function to safely convert value to double
  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value) ?? 0.0;
    }
    return 0.0;
  }

  Widget _buildRefundRequestCard(Map<String, dynamic> request) {
    final status = request['status'] ?? 'pending';
    final date = request['requested_at'] ?? request['created_at'] ?? '';
    final amount = _parseDouble(request['refund_amount']);
    final reference = request['refund_reference'] ?? 'N/A';
    final method = request['refund_method'] ?? 'N/A';
    final contribution = request['contribution_title'] ?? 'N/A';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
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
                      Text(
                        contribution,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _formatDate(date),
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: _getStatusColor(status).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    status.toUpperCase(),
                    style: TextStyle(
                      color: _getStatusColor(status),
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Refund Amount',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '₱${NumberFormat('#,##0.00').format(amount)}',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.orange,
                      ),
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'Method',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      method.toString().replaceAll('_', ' ').toUpperCase(),
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(Icons.receipt, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Text(
                  'Reference: $reference',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return Colors.green;
      case 'processing':
        return Colors.blue;
      case 'pending':
        return Colors.orange;
      case 'rejected':
        return Colors.red;
      case 'cancelled':
        return Colors.grey;
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

class RefundRequestDialog extends StatefulWidget {
  final List<dynamic> refundablePayments;
  final List<dynamic> refundMethods;
  final VoidCallback onSubmitted;

  const RefundRequestDialog({
    super.key,
    required this.refundablePayments,
    required this.refundMethods,
    required this.onSubmitted,
  });

  @override
  State<RefundRequestDialog> createState() => _RefundRequestDialogState();
}

class _RefundRequestDialogState extends State<RefundRequestDialog> {
  final _formKey = GlobalKey<FormState>();
  int? _selectedPaymentId;
  double _refundAmount = 0.0;
  double _originalAmount = 0.0;
  double _availableRefund = 0.0;
  String _refundStatus = 'no_refund';
  String? _selectedRefundMethod;
  final _reasonController = TextEditingController();
  final _refundAmountController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void dispose() {
    _reasonController.dispose();
    _refundAmountController.dispose();
    super.dispose();
  }

  // Helper function to safely convert value to double
  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value) ?? 0.0;
    }
    return 0.0;
  }

  void _updateRefundAmount() {
    if (_selectedPaymentId != null) {
      final payment = widget.refundablePayments.firstWhere(
        (p) {
          // Convert payment ID to int for comparison (API returns it as string)
          final pId = p['id'] is int ? p['id'] : int.tryParse(p['id'].toString());
          return pId == _selectedPaymentId;
        },
        orElse: () => null,
      );
      if (payment != null) {
        // Handle both string and numeric values from API
        final amountPaidValue = payment['amount_paid'];
        final availableRefundValue = payment['available_refund'];
        
        final originalAmount = amountPaidValue is num 
            ? amountPaidValue.toDouble() 
            : (amountPaidValue is String 
                ? double.tryParse(amountPaidValue) ?? 0.0 
                : 0.0);
        
        final availableRefund = availableRefundValue is num 
            ? availableRefundValue.toDouble() 
            : (availableRefundValue is String 
                ? double.tryParse(availableRefundValue) ?? 0.0 
                : 0.0);
        
        final refundStatus = payment['refund_status'] ?? 'no_refund';
        
        setState(() {
          _refundAmount = _parseDouble(payment['available_refund']);
        });
        // Update the text field controller
        _refundAmountController.text = availableRefund > 0
            ? NumberFormat('#,##0.00').format(availableRefund)
            : '';
      }
    } else {
      setState(() {
        _refundAmount = 0.0;
        _originalAmount = 0.0;
        _availableRefund = 0.0;
        _refundStatus = 'no_refund';
      });
      _refundAmountController.clear();
    }
  }
  
  String _getRefundStatusText() {
    switch (_refundStatus) {
      case 'partially_refunded':
        return 'Partially Refunded';
      case 'fully_refunded':
        return 'Fully Refunded';
      default:
        return 'No Refund';
    }
  }
  
  Color _getRefundStatusColor() {
    switch (_refundStatus) {
      case 'partially_refunded':
        return Colors.orange;
      case 'fully_refunded':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  Future<void> _submitRefundRequest() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedPaymentId == null || _selectedRefundMethod == null) {
      _showToast(
        context,
        'Please fill in all required fields',
        Colors.orange,
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      final response = await ApiService.submitRefundRequest(
        paymentId: _selectedPaymentId!,
        refundAmount: _refundAmount,
        refundMethod: _selectedRefundMethod!,
        refundReason: _reasonController.text.isEmpty ? null : _reasonController.text,
      );

      if (response['success'] == true) {
        if (mounted) {
          final message = response['message'] ?? 'Refund request submitted successfully';
          final reference = response['reference_number'] ?? response['reference'] ?? '';
          final fullMessage = reference.isNotEmpty 
              ? '$message${message.contains('Reference') ? '' : ' Reference: $reference'}'
              : message;
          _showToast(
            context,
            fullMessage,
            Colors.green,
          );
          widget.onSubmitted();
        }
      } else {
        if (mounted) {
          _showToast(
            context,
            response['message'] ?? 'Failed to submit refund request',
            Colors.red,
          );
        }
      }
    } catch (e) {
      if (mounted) {
        _showToast(
          context,
          'Error: ${e.toString()}',
          Colors.red,
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

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

  @override
  Widget build(BuildContext context) {
    return Dialog(
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 600, maxHeight: 700),
        child: SizedBox(
          width: 600,
          child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header - Blue background with icon and close button
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: const BoxDecoration(
                color: Color(0xFF2196F3),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(8),
                  topRight: Radius.circular(8),
                ),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.max,
                children: [
                  const Icon(Icons.undo, color: Colors.white, size: 22),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Text(
                      'Request Refund',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 8),
                  SizedBox(
                    width: 32,
                    height: 32,
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: () => Navigator.pop(context),
                        borderRadius: BorderRadius.circular(16),
                        child: const Icon(Icons.close, color: Colors.white, size: 20),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            // Body
            Flexible(
              child: Form(
                key: _formKey,
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Select Payment
                      DropdownButtonFormField<int>(
                        decoration: const InputDecoration(
                          labelText: 'Select Payment *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.payment),
                        ),
                        isExpanded: true,
                        value: _selectedPaymentId,
                        selectedItemBuilder: (context) {
                          // Shorter display for selected value
                          return widget.refundablePayments.map((payment) {
                            final contribution = payment['contribution_title'] ?? 'N/A';
                            final paymentId = payment['id'] is int 
                                ? payment['id'] 
                                : int.tryParse(payment['id'].toString());
                            if (paymentId == null || paymentId != _selectedPaymentId) {
                              return const SizedBox.shrink();
                            }
                            return Text(
                              contribution,
                              style: const TextStyle(fontSize: 16),
                              overflow: TextOverflow.ellipsis,
                            );
                          }).whereType<Widget>().toList();
                        },
                        items: widget.refundablePayments.map((payment) {
                          final receipt = payment['receipt_number'] ?? 'N/A';
                          final contribution = payment['contribution_title'] ?? 'N/A';
                          
                          // Handle both string and numeric values from API
                          final amountPaidValue = payment['amount_paid'];
                          final availableRefundValue = payment['available_refund'];
                          
                          final amount = amountPaidValue is num 
                              ? amountPaidValue.toDouble() 
                              : (amountPaidValue is String 
                                  ? double.tryParse(amountPaidValue) ?? 0.0 
                                  : 0.0);
                          
                          final available = availableRefundValue is num 
                              ? availableRefundValue.toDouble() 
                              : (availableRefundValue is String 
                                  ? double.tryParse(availableRefundValue) ?? 0.0 
                                  : 0.0);
                          final paymentId = payment['id'] is int 
                              ? payment['id'] 
                              : int.tryParse(payment['id'].toString());
                          if (paymentId == null) return null;
                          return DropdownMenuItem<int>(
                            value: paymentId,
                            child: SizedBox(
                              width: double.infinity,
                              child: Text(
                                '$contribution - Receipt: $receipt - Amount: ₱${NumberFormat('#,##0.00').format(amount)} (Available: ₱${NumberFormat('#,##0.00').format(available)})',
                                style: const TextStyle(fontSize: 14),
                                overflow: TextOverflow.ellipsis,
                                maxLines: 1,
                              ),
                            ),
                          );
                        }).whereType<DropdownMenuItem<int>>().toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedPaymentId = value;
                          });
                          _updateRefundAmount();
                        },
                        validator: (value) {
                          if (value == null) return 'Please select a payment';
                          return null;
                        },
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Only payments with available refund amounts are shown',
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 16),
                      // Payment Details Section (shown when payment is selected)
                      if (_selectedPaymentId != null) ...[
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.grey[100],
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'Original Amount:',
                                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          '₱${NumberFormat('#,##0.00').format(_originalAmount)}',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'Available for Refund:',
                                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          '₱${NumberFormat('#,##0.00').format(_availableRefund)}',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                            color: Colors.green,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              Text(
                                'Refund Status:',
                                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                              ),
                              const SizedBox(height: 4),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: _getRefundStatusColor(),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  _getRefundStatusText(),
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),
                      ],
                      // Refund Amount
                      TextFormField(
                        controller: _refundAmountController,
                        decoration: InputDecoration(
                          labelText: 'Refund Amount *',
                          border: const OutlineInputBorder(),
                          prefixText: '₱',
                          suffixIcon: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.arrow_drop_up),
                                onPressed: () {
                                  if (_availableRefund > 0) {
                                    final newAmount = (_refundAmount + 0.01).clamp(0.01, _availableRefund);
                                    setState(() => _refundAmount = newAmount);
                                    _refundAmountController.text = NumberFormat('#,##0.00').format(newAmount);
                                  }
                                },
                                padding: EdgeInsets.zero,
                                constraints: const BoxConstraints(),
                              ),
                              IconButton(
                                icon: const Icon(Icons.arrow_drop_down),
                                onPressed: () {
                                  if (_refundAmount > 0.01) {
                                    final newAmount = (_refundAmount - 0.01).clamp(0.01, _availableRefund);
                                    setState(() => _refundAmount = newAmount);
                                    _refundAmountController.text = NumberFormat('#,##0.00').format(newAmount);
                                  }
                                },
                                padding: EdgeInsets.zero,
                                constraints: const BoxConstraints(),
                              ),
                            ],
                          ),
                        ),
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        onChanged: (value) {
                          final amount = double.tryParse(value.replaceAll(',', ''));
                          if (amount != null) {
                            final clampedAmount = amount.clamp(0.01, _availableRefund);
                            setState(() => _refundAmount = clampedAmount);
                            if (amount != clampedAmount) {
                              _refundAmountController.text = NumberFormat('#,##0.00').format(clampedAmount);
                            }
                          }
                        },
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter a refund amount';
                          }
                          final amount = double.tryParse(value.replaceAll(',', ''));
                          if (amount == null || amount <= 0) {
                            return 'Refund amount must be greater than 0';
                          }
                          if (amount > _availableRefund) {
                            return 'Refund amount cannot exceed available amount (₱${NumberFormat('#,##0.00').format(_availableRefund)})';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Maximum available: ₱${NumberFormat('#,##0.00').format(_availableRefund)}',
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 16),
                      // Refund Method
                      DropdownButtonFormField<String>(
                        decoration: const InputDecoration(
                          labelText: 'Refund Method *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.payment),
                        ),
                        value: _selectedRefundMethod,
                        items: widget.refundMethods.map((method) {
                          return DropdownMenuItem<String>(
                            value: method['code'],
                            child: Text(method['name']),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedRefundMethod = value;
                          });
                        },
                        validator: (value) {
                          if (value == null) return 'Please select a refund method';
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      // Reason for Refund
                      TextFormField(
                        controller: _reasonController,
                        decoration: const InputDecoration(
                          labelText: 'Reason for Refund',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.note),
                          hintText: 'Please provide a reason for requesting this refund...',
                        ),
                        maxLines: 3,
                        maxLength: 500,
                      ),
                    ],
                  ),
                ),
              ),
            ),
            // Footer
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border(top: BorderSide(color: Colors.grey[300]!)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton(
                    onPressed: _isSubmitting ? null : () => Navigator.pop(context),
                    child: const Text('Cancel'),
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton.icon(
                    onPressed: _isSubmitting ? null : _submitRefundRequest,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF2196F3),
                      foregroundColor: Colors.white,
                    ),
                    icon: _isSubmitting
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Icon(Icons.send, size: 18),
                    label: Text(_isSubmitting ? 'Submitting...' : 'Submit Request'),
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
}

