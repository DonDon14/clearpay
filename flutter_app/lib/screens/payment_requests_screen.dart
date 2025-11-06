import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class PaymentRequestsScreen extends StatefulWidget {
  final bool showAppBar;
  
  const PaymentRequestsScreen({super.key, this.showAppBar = true});

  @override
  State<PaymentRequestsScreen> createState() => _PaymentRequestsScreenState();
}

class _PaymentRequestsScreenState extends State<PaymentRequestsScreen> {
  bool _isLoading = true;
  List<dynamic> _paymentRequests = [];
  List<dynamic> _contributions = [];
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
      final response = await ApiService.getPaymentRequests();
      
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];
        setState(() {
          _paymentRequests = data['payment_requests'] ?? [];
          _contributions = data['contributions'] ?? [];
        });
      } else {
        setState(() {
          _errorMessage = response['error'] ?? 'Failed to load payment requests';
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

  void _showPaymentRequestDialog() {
    if (_contributions.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No active contributions available')),
      );
      return;
    }

    showDialog(
      context: context,
      builder: (context) => _PaymentRequestDialog(
        contributions: _contributions,
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
                  child: _paymentRequests.isEmpty
                      ? _buildEmptyState()
                      : _buildPaymentRequestsList(),
                );
    
    if (widget.showAppBar) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Payment Requests'),
          actions: [
            IconButton(
              icon: const Icon(Icons.refresh),
              onPressed: _loadData,
            ),
          ],
        ),
        body: body,
        floatingActionButton: FloatingActionButton.extended(
          onPressed: _showPaymentRequestDialog,
          icon: const Icon(Icons.add),
          label: const Text('Request Payment'),
          backgroundColor: const Color(0xFF4CAF50),
        ),
      );
    } else {
      return Scaffold(
        body: body,
        floatingActionButton: FloatingActionButton.extended(
          onPressed: _showPaymentRequestDialog,
          icon: const Icon(Icons.add),
          label: const Text('Request Payment'),
          backgroundColor: const Color(0xFF4CAF50),
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
            'No Payment Requests',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
          const SizedBox(height: 8),
          Text(
            'You haven\'t submitted any payment requests yet.',
            style: TextStyle(color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentRequestsList() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _paymentRequests.length,
      itemBuilder: (context, index) {
        final request = _paymentRequests[index];
        return _buildPaymentRequestCard(request);
      },
    );
  }

  Widget _buildPaymentRequestCard(Map<String, dynamic> request) {
    final status = request['status'] ?? 'pending';
    final date = request['requested_at'] ?? request['created_at'] ?? '';
    final amount = (request['requested_amount'] ?? 0).toDouble();
    final reference = request['reference_number'] ?? 'N/A';
    final method = request['payment_method'] ?? 'N/A';
    final contribution = request['contribution_title'] ?? 'N/A';
    final proofPath = request['proof_of_payment_path'];

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
                      'Requested Amount',
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
                        color: Colors.green,
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
                Expanded(
                  child: Text(
                    'Reference: $reference',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                ),
                if (proofPath != null && proofPath.toString().isNotEmpty)
                  IconButton(
                    icon: const Icon(Icons.image, size: 20),
                    onPressed: () {
                      // TODO: Show proof of payment image
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Proof of payment view coming soon')),
                      );
                    },
                    tooltip: 'View Proof of Payment',
                  ),
              ],
            ),
            if (request['admin_notes'] != null && request['admin_notes'].toString().isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.amber.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.note, size: 16, color: Colors.amber),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Admin Note: ${request['admin_notes']}',
                          style: const TextStyle(
                            fontSize: 12,
                            color: Colors.amber,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'approved':
        return Colors.green;
      case 'processed':
        return Colors.blue;
      case 'pending':
        return Colors.orange;
      case 'rejected':
        return Colors.red;
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

class _PaymentRequestDialog extends StatefulWidget {
  final List<dynamic> contributions;
  final VoidCallback onSubmitted;

  const _PaymentRequestDialog({
    required this.contributions,
    required this.onSubmitted,
  });

  @override
  State<_PaymentRequestDialog> createState() => _PaymentRequestDialogState();
}

class _PaymentRequestDialogState extends State<_PaymentRequestDialog> {
  final _formKey = GlobalKey<FormState>();
  int? _selectedContributionId;
  Map<String, dynamic>? _selectedContribution;
  double _maxAmount = 0.0;
  double _requestedAmount = 0.0;
  String? _selectedPaymentMethod;
  final _notesController = TextEditingController();
  bool _isSubmitting = false;
  bool _isLoadingContribution = false;

  final List<String> _paymentMethods = [
    'cash',
    'gcash',
    'paymaya',
    'bank_transfer',
    'check',
    'online',
  ];

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _loadContributionDetails(int contributionId) async {
    setState(() {
      _isLoadingContribution = true;
    });

    try {
      final response = await ApiService.getContributionDetails(contributionId);
      if (response['success'] == true && response['contribution'] != null) {
        final contribution = response['contribution'];
        setState(() {
          _selectedContribution = contribution;
          _maxAmount = (contribution['remaining_amount'] ?? 0).toDouble();
          _requestedAmount = _maxAmount;
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading contribution: ${e.toString()}')),
      );
    } finally {
      setState(() {
        _isLoadingContribution = false;
      });
    }
  }

  Future<void> _submitPaymentRequest() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedContributionId == null || _selectedPaymentMethod == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in all required fields')),
      );
      return;
    }

    if (_requestedAmount > _maxAmount) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Amount cannot exceed remaining balance (₱${NumberFormat('#,##0.00').format(_maxAmount)})')),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      final response = await ApiService.submitPaymentRequest(
        contributionId: _selectedContributionId!,
        requestedAmount: _requestedAmount,
        paymentMethod: _selectedPaymentMethod!,
        notes: _notesController.text.isEmpty ? null : _notesController.text,
      );

      if (response['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'Payment request submitted successfully'),
              backgroundColor: Colors.green,
            ),
          );
          widget.onSubmitted();
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'Failed to submit payment request'),
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
                'Request Payment',
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
                          labelText: 'Select Contribution *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.receipt_long),
                        ),
                        value: _selectedContributionId,
                        items: widget.contributions.map((contribution) {
                          return DropdownMenuItem<int>(
                            value: contribution['id'],
                            child: Text(contribution['title'] ?? 'N/A'),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedContributionId = value;
                            _selectedContribution = null;
                            _maxAmount = 0.0;
                            _requestedAmount = 0.0;
                          });
                          if (value != null) {
                            _loadContributionDetails(value);
                          }
                        },
                        validator: (value) {
                          if (value == null) return 'Please select a contribution';
                          return null;
                        },
                      ),
                      if (_isLoadingContribution)
                        const Padding(
                          padding: EdgeInsets.all(16.0),
                          child: CircularProgressIndicator(),
                        ),
                      if (_selectedContribution != null) ...[
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.blue.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Remaining Balance: ₱${NumberFormat('#,##0.00').format(_maxAmount)}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.blue,
                                ),
                              ),
                              if ((_selectedContribution!['total_paid'] ?? 0) > 0)
                                Text(
                                  'Total Paid: ₱${NumberFormat('#,##0.00').format(_selectedContribution!['total_paid'] ?? 0)}',
                                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 16),
                      TextFormField(
                        decoration: const InputDecoration(
                          labelText: 'Requested Amount *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.attach_money),
                        ),
                        keyboardType: TextInputType.number,
                        initialValue: _requestedAmount > 0
                            ? NumberFormat('#,##0.00').format(_requestedAmount)
                            : '',
                        onChanged: (value) {
                          final amount = double.tryParse(value.replaceAll(',', ''));
                          if (amount != null) {
                            setState(() {
                              _requestedAmount = amount;
                            });
                          }
                        },
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter an amount';
                          }
                          final amount = double.tryParse(value.replaceAll(',', ''));
                          if (amount == null || amount <= 0) {
                            return 'Please enter a valid amount';
                          }
                          if (amount > _maxAmount) {
                            return 'Amount cannot exceed remaining balance';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        decoration: const InputDecoration(
                          labelText: 'Payment Method *',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.payment),
                        ),
                        value: _selectedPaymentMethod,
                        items: _paymentMethods.map((method) {
                          return DropdownMenuItem<String>(
                            value: method,
                            child: Text(method.replaceAll('_', ' ').toUpperCase()),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedPaymentMethod = value;
                          });
                        },
                        validator: (value) {
                          if (value == null) return 'Please select a payment method';
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _notesController,
                        decoration: const InputDecoration(
                          labelText: 'Notes (Optional)',
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
                      onPressed: _isSubmitting ? null : _submitPaymentRequest,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4CAF50),
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
