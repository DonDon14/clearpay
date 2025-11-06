import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class RefundRequestsScreen extends StatefulWidget {
  const RefundRequestsScreen({super.key});

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
      
      if (refundRequestsResponse['success'] == true) {
        final data = refundRequestsResponse['data'];
        setState(() {
          _refundRequests = data['refundRequests'] ?? [];
          _refundablePayments = data['refundablePayments'] ?? [];
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
      builder: (context) => _RefundRequestDialog(
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
    return Scaffold(
      appBar: AppBar(
        title: const Text('Refund Requests'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? _buildErrorWidget()
              : RefreshIndicator(
                  onRefresh: _loadData,
                  child: _refundRequests.isEmpty
                      ? _buildEmptyState()
                      : _buildRefundRequestsList(),
                ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showRefundRequestDialog,
        icon: const Icon(Icons.add),
        label: const Text('Request Refund'),
        backgroundColor: const Color(0xFFFF9800),
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

  Widget _buildRefundRequestCard(Map<String, dynamic> request) {
    final status = request['status'] ?? 'pending';
    final date = request['requested_at'] ?? request['created_at'] ?? '';
    final amount = (request['refund_amount'] ?? 0).toDouble();
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

class _RefundRequestDialog extends StatefulWidget {
  final List<dynamic> refundablePayments;
  final List<dynamic> refundMethods;
  final VoidCallback onSubmitted;

  const _RefundRequestDialog({
    required this.refundablePayments,
    required this.refundMethods,
    required this.onSubmitted,
  });

  @override
  State<_RefundRequestDialog> createState() => _RefundRequestDialogState();
}

class _RefundRequestDialogState extends State<_RefundRequestDialog> {
  final _formKey = GlobalKey<FormState>();
  int? _selectedPaymentId;
  double _refundAmount = 0.0;
  String? _selectedRefundMethod;
  final _reasonController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  void _updateRefundAmount() {
    if (_selectedPaymentId != null) {
      final payment = widget.refundablePayments.firstWhere(
        (p) => p['id'] == _selectedPaymentId,
        orElse: () => null,
      );
      if (payment != null) {
        setState(() {
          _refundAmount = (payment['available_refund'] ?? 0).toDouble();
        });
      }
    }
  }

  Future<void> _submitRefundRequest() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedPaymentId == null || _selectedRefundMethod == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in all required fields')),
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
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'Refund request submitted successfully'),
              backgroundColor: Colors.green,
            ),
          );
          widget.onSubmitted();
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'Failed to submit refund request'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
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

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: Container(
        constraints: const BoxConstraints(maxWidth: 500, maxHeight: 600),
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Request Refund',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 24),
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    children: [
                      DropdownButtonFormField<int>(
                        decoration: const InputDecoration(
                          labelText: 'Select Payment *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.payment),
                        ),
                        value: _selectedPaymentId,
                        items: widget.refundablePayments.map((payment) {
                          final receipt = payment['receipt_number'] ?? 'N/A';
                          final contribution = payment['contribution_title'] ?? 'N/A';
                          final available = (payment['available_refund'] ?? 0).toDouble();
                          return DropdownMenuItem<int>(
                            value: payment['id'],
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text('$contribution'),
                                Text(
                                  'Receipt: $receipt • Available: ₱${NumberFormat('#,##0.00').format(available)}',
                                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
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
                      const SizedBox(height: 16),
                      TextFormField(
                        decoration: const InputDecoration(
                          labelText: 'Refund Amount *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.attach_money),
                        ),
                        keyboardType: TextInputType.number,
                        readOnly: true,
                        initialValue: _refundAmount > 0
                            ? NumberFormat('#,##0.00').format(_refundAmount)
                            : '',
                        onChanged: (value) {
                          final amount = double.tryParse(value.replaceAll(',', ''));
                          if (amount != null) {
                            setState(() {
                              _refundAmount = amount;
                            });
                          }
                        },
                        validator: (value) {
                          if (_refundAmount <= 0) {
                            return 'Please select a payment first';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
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
                      TextFormField(
                        controller: _reasonController,
                        decoration: const InputDecoration(
                          labelText: 'Reason (Optional)',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.note),
                        ),
                        maxLines: 3,
                        maxLength: 500,
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: TextButton(
                      onPressed: _isSubmitting ? null : () => Navigator.pop(context),
                      child: const Text('Cancel'),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _submitRefundRequest,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFFF9800),
                      ),
                      child: _isSubmitting
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Submit'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

