import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'dart:html' as html show FileUploadInputElement, File;

class PaymentRequestsScreen extends StatefulWidget {
  final bool showAppBar;
  final Map<String, dynamic>? preSelectedContribution;
  
  const PaymentRequestsScreen({super.key, this.showAppBar = true, this.preSelectedContribution});

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
    // Show payment request dialog if contribution is pre-selected
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (widget.preSelectedContribution != null) {
        _showPaymentRequestDialog(preSelectedContribution: widget.preSelectedContribution);
      }
    });
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

  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value.replaceAll(',', '')) ?? 0.0;
    }
    return 0.0;
  }

  void _showPaymentRequestDialog({Map<String, dynamic>? preSelectedContribution}) {
    if (_contributions.isEmpty && preSelectedContribution == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No active contributions available')),
      );
      return;
    }

    showDialog(
      context: context,
      builder: (context) => PaymentRequestDialog(
        contributions: _contributions,
        preSelectedContribution: preSelectedContribution,
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
    final amount = _parseDouble(request['requested_amount'] ?? 0);
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

class PaymentRequestDialog extends StatefulWidget {
  final List<dynamic> contributions;
  final Map<String, dynamic>? preSelectedContribution;
  final VoidCallback onSubmitted;

  const PaymentRequestDialog({
    required this.contributions,
    this.preSelectedContribution,
    required this.onSubmitted,
  });

  @override
  State<PaymentRequestDialog> createState() => _PaymentRequestDialogState();
}

class _PaymentRequestDialogState extends State<PaymentRequestDialog> {
  final _formKey = GlobalKey<FormState>();
  int? _selectedContributionId;
  Map<String, dynamic>? _selectedContribution;
  double _maxAmount = 0.0;
  double _requestedAmount = 0.0;
  String? _selectedPaymentMethod;
  final _notesController = TextEditingController();
  bool _isSubmitting = false;
  bool _isLoadingContribution = false;
  bool _isLoadingInstructions = false;
  String? _paymentInstructions;
  String? _paymentMethodName;
  String? _qrCodePath;
  String? _accountNumber;
  String? _accountName;
  String? _proofOfPaymentPath;
  final _proofOfPaymentController = TextEditingController();
  html.File? _proofOfPaymentFile;

  List<Map<String, dynamic>> _paymentMethods = [];
  bool _isLoadingPaymentMethods = false;

  bool _isFullyPaid = false;
  bool _hasShownFullyPaidWarning = false;

  @override
  void initState() {
    super.initState();
    _loadPaymentMethods();
    // Pre-select contribution if provided
    if (widget.preSelectedContribution != null) {
      final contribution = widget.preSelectedContribution!;
      final contributionId = contribution['id'];
      // Convert to int if it's a string
      if (contributionId != null) {
        _selectedContributionId = contributionId is int 
            ? contributionId 
            : int.tryParse(contributionId.toString());
        _selectedContribution = contribution;
        
        // Check if contribution is fully paid
        final totalPaid = _parseDouble(contribution['total_paid'] ?? 0);
        final contributionAmount = _parseDouble(contribution['amount'] ?? 0);
        final remainingBalance = _parseDouble(contribution['remaining_balance'] ?? contribution['remaining_amount'] ?? 0);
        _isFullyPaid = totalPaid >= contributionAmount || remainingBalance <= 0;
        
        // If fully paid, set max amount to full contribution amount (for new payment group)
        // Otherwise, use remaining balance
        if (_isFullyPaid) {
          // Use contribution amount if available, otherwise use a large number to allow any amount
          _maxAmount = contributionAmount > 0 ? contributionAmount : 999999.0;
          _requestedAmount = contributionAmount > 0 ? contributionAmount : 0.0;
          // Show confirmation dialog for fully paid contributions
          WidgetsBinding.instance.addPostFrameCallback((_) {
            _showFullyPaidConfirmation(contribution);
          });
        } else {
          _maxAmount = remainingBalance > 0 ? remainingBalance : contributionAmount;
          _requestedAmount = _maxAmount;
        }
        
        // Load instructions if payment method is already selected
        if (_selectedPaymentMethod != null && _requestedAmount > 0) {
          _loadPaymentMethodInstructions(_selectedPaymentMethod!, _requestedAmount);
        }
      }
    }
  }

  void _showFullyPaidConfirmation(Map<String, dynamic> contribution) {
    if (_hasShownFullyPaidWarning) return;
    _hasShownFullyPaidWarning = true;
    
    // Get total_paid from the contribution data - use the pre-selected contribution if available
    // as it has the correct total_paid value from the contributions list
    final totalPaid = _parseDouble(contribution['total_paid'] ?? 0);
    // If total_paid is 0, try to get it from payment_groups
    double actualTotalPaid = totalPaid;
    if (totalPaid == 0 && contribution['payment_groups'] != null) {
      final paymentGroups = contribution['payment_groups'] as List?;
      if (paymentGroups != null && paymentGroups.isNotEmpty) {
        // Sum up total_paid from all payment groups
        actualTotalPaid = paymentGroups.fold<double>(0.0, (sum, group) {
          return sum + _parseDouble(group['total_paid'] ?? 0);
        });
      }
    }
    // If still 0, use the contribution amount as fallback
    if (actualTotalPaid == 0) {
      actualTotalPaid = _parseDouble(contribution['amount'] ?? 0);
    }
    
    final contributionAmount = _parseDouble(contribution['amount'] ?? 0);
    final contributionTitle = contribution['title'] ?? 'Contribution';
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.orange[700], size: 24),
            const SizedBox(width: 8),
            const Expanded(
              child: Text(
                'Contribution Already Fully Paid',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue[50],
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Contribution Details',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                  const SizedBox(height: 8),
                  Text('Contribution: $contributionTitle'),
                  Text('Total Amount: ₱${NumberFormat('#,##0.00').format(contributionAmount)}'),
                  Text('Total Paid: ₱${NumberFormat('#,##0.00').format(actualTotalPaid)}'),
                ],
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              '⚠️ Contribution Already Fully Paid',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              'You already have fully paid contribution groups for "$contributionTitle" (₱${NumberFormat('#,##0.00').format(actualTotalPaid)} total).',
            ),
            const SizedBox(height: 16),
            const Text(
              'Add another payment group for this contribution?',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.orange[50],
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Icon(Icons.lightbulb_outline, color: Colors.orange[700], size: 20),
                  const SizedBox(width: 8),
                  const Expanded(
                    child: Text(
                      'Note: Each payment group is independent and tracked separately in your payment history.',
                      style: TextStyle(fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.pop(context); // Close payment request dialog too
            },
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              // User confirmed, continue with payment request
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF6366F1),
            ),
            child: const Text('Yes, Add Another Payment Group'),
          ),
        ],
      ),
    );
  }

  int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
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

  Future<void> _pickProofOfPayment() async {
    if (kIsWeb) {
      // Use HTML file input for web
      final input = html.FileUploadInputElement()
        ..accept = 'image/jpeg,image/jpg,image/png,application/pdf'
        ..click();
      
      input.onChange.listen((e) {
        final files = input.files;
        if (files != null && files.isNotEmpty) {
          final file = files[0];
          // Store file for upload
          _proofOfPaymentFile = file;
          setState(() {
            _proofOfPaymentPath = file.name;
            _proofOfPaymentController.text = file.name;
          });
        }
      });
    } else {
      // For mobile, you'd use image_picker or file_picker package
      // For now, just show a message
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('File picker not implemented for mobile yet')),
      );
    }
  }

  @override
  void dispose() {
    _notesController.dispose();
    _proofOfPaymentController.dispose();
    super.dispose();
  }

  Future<void> _loadPaymentMethods() async {
    setState(() {
      _isLoadingPaymentMethods = true;
    });

    try {
      final response = await ApiService.getActivePaymentMethods();
      if (response['success'] == true && response['methods'] != null) {
        setState(() {
          _paymentMethods = List<Map<String, dynamic>>.from(response['methods']);
        });
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading payment methods: ${response['error'] ?? 'Unknown error'}')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading payment methods: ${e.toString()}')),
      );
    } finally {
      setState(() {
        _isLoadingPaymentMethods = false;
      });
    }
  }

  void _setGenericInstructions(String methodName, double amount, dynamic method, String? qrCodePath, String? accountNumber, String? accountName) {
    String genericInstructions = 'Please prepare the following for ${methodName.replaceAll('_', ' ').toUpperCase()} payment:\n\n'
        'Amount: ₱${amount.toStringAsFixed(2)}\n'
        'Payment Type: ${methodName.replaceAll('_', ' ').toUpperCase()}\n';
    
    if (accountNumber != null && accountNumber.isNotEmpty) {
      genericInstructions += 'Account Number: $accountNumber\n';
    }
    if (accountName != null && accountName.isNotEmpty) {
      genericInstructions += 'Account Name: $accountName\n';
    }
    
    // Generate reference number using seconds (same as PHP time() function)
    // PHP time() returns seconds since epoch, not milliseconds
    String referencePrefix = 'CP';
    if (method != null && method['reference_prefix'] != null && method['reference_prefix'].toString().isNotEmpty) {
      referencePrefix = method['reference_prefix'].toString();
    }
    final timestamp = DateTime.now().millisecondsSinceEpoch ~/ 1000; // Convert to seconds
    final reference = '$referencePrefix-$timestamp';
    
    genericInstructions += 'Reference: $reference\n\n'
        'Instructions:\n'
        '• Follow your preferred ${methodName.replaceAll('_', ' ').toUpperCase()} payment method\n'
        '• Enter amount: ₱${amount.toStringAsFixed(2)}\n'
        '• Add reference: $reference\n'
        '• Upload proof of payment below';
    
    setState(() {
      _paymentInstructions = genericInstructions;
      _paymentMethodName = method != null ? (method['name'] ?? methodName) : methodName;
      _qrCodePath = qrCodePath;
      _accountNumber = accountNumber;
      _accountName = accountName;
    });
  }

  Future<void> _loadContributionDetails(int contributionId) async {
    setState(() {
      _isLoadingContribution = true;
    });

    try {
      final response = await ApiService.getContributionDetails(contributionId);
      if (response['success'] == true && response['contribution'] != null) {
        final contribution = response['contribution'];
        
        // Check if contribution is fully paid
        final totalPaid = _parseDouble(contribution['total_paid'] ?? 0);
        final contributionAmount = _parseDouble(contribution['amount'] ?? 0);
        final remainingBalance = _parseDouble(contribution['remaining_balance'] ?? contribution['remaining_amount'] ?? 0);
        final isFullyPaid = totalPaid >= contributionAmount || remainingBalance <= 0;
        
        setState(() {
          _selectedContribution = contribution;
          _isFullyPaid = isFullyPaid;
          
          // If fully paid, set max amount to full contribution amount (for new payment group)
          // Otherwise, use remaining balance
          if (_isFullyPaid) {
            // Use contribution amount if available, otherwise use a large number to allow any amount
            _maxAmount = contributionAmount > 0 ? contributionAmount : 999999.0;
            _requestedAmount = contributionAmount > 0 ? contributionAmount : 0.0;
            // Show confirmation dialog for fully paid contributions
            WidgetsBinding.instance.addPostFrameCallback((_) {
              _showFullyPaidConfirmation(contribution);
            });
          } else {
            _maxAmount = remainingBalance > 0 ? remainingBalance : contributionAmount;
            _requestedAmount = _maxAmount;
          }
        });
        // Reload instructions if payment method is already selected
        if (_selectedPaymentMethod != null && _requestedAmount > 0) {
          _loadPaymentMethodInstructions(_selectedPaymentMethod!, _requestedAmount);
        }
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

  Future<void> _loadPaymentMethodInstructions(String methodName, double amount) async {
    setState(() {
      _isLoadingInstructions = true;
      _paymentInstructions = null;
      _qrCodePath = null;
      _accountNumber = null;
      _accountName = null;
    });

    try {
      final response = await ApiService.getPaymentMethodInstructions(methodName);
      print('API Response: $response'); // Debug
      
      if (response['success'] == true && response['method'] != null) {
        final method = response['method'];
        print('Method data: $method'); // Debug
        
        // Check if custom instructions exist (EXACT same logic as web app line 224)
        final hasCustomInstructions = method['custom_instructions'] != null && 
                                     method['custom_instructions'].toString().trim().isNotEmpty;
        print('Has custom instructions: $hasCustomInstructions'); // Debug
        
        if (hasCustomInstructions) {
          // Use custom instructions from database (EXACT same as web app line 251)
          String? instructions = method['processed_instructions'] ?? method['custom_instructions'];
          print('Instructions: $instructions'); // Debug
          
          // Get account details (ALWAYS get these from method object)
          String? accountNumber = method['account_number']?.toString();
          String? accountName = method['account_name']?.toString();
          print('Account Number: $accountNumber, Account Name: $accountName'); // Debug
          
          // Get QR code path from method object (ALWAYS check this)
          String? qrCodePath;
          if (method['qr_code_path'] != null && method['qr_code_path'].toString().trim().isNotEmpty) {
            final qrPath = method['qr_code_path'].toString();
            // Format QR code path - it's stored as relative path in DB (e.g., "uploads/payment_methods/qr_codes/...")
            // Need to construct full URL for Flutter web
            if (qrPath.startsWith('http://') || qrPath.startsWith('https://')) {
              qrCodePath = qrPath;
            } else if (qrPath.startsWith('/')) {
              // Path starts with /, so it's relative to base URL
              qrCodePath = '${ApiService.baseUrl}$qrPath';
            } else {
              // Path doesn't start with /, add it (e.g., "uploads/..." becomes "/uploads/...")
              qrCodePath = '${ApiService.baseUrl}/${qrPath.startsWith('uploads/') ? qrPath : 'uploads/$qrPath'}';
            }
            print('QR Code Path (raw): $qrPath'); // Debug
            print('QR Code Path (formatted): $qrCodePath'); // Debug
          }
          
          if (instructions != null && instructions.trim().isNotEmpty) {
            // Replace amount placeholder (EXACT same as web app line 257)
            instructions = instructions.replaceAll('{amount}', amount.toStringAsFixed(2));
            
            // Check if QR code is already in instructions (EXACT same logic as web app lines 265-268)
            final hasQRCodeInInstructions = instructions.toLowerCase().contains('<img') || 
                                           instructions.toLowerCase().contains('qr_code') || 
                                           instructions.toLowerCase().contains('qr') ||
                                           instructions.toLowerCase().contains('qr-code');
            print('Has QR code in instructions: $hasQRCodeInInstructions'); // Debug
            
            // Extract QR code from instructions HTML if present
            if (qrCodePath == null) {
              final qrImageMatchDouble = RegExp(r'<img[^>]+src="([^"]+)"', caseSensitive: false).firstMatch(instructions);
              final qrImageMatchSingle = RegExp(r"<img[^>]+src='([^']+)'", caseSensitive: false).firstMatch(instructions);
              final qrImageMatch = qrImageMatchDouble ?? qrImageMatchSingle;
              
              if (qrImageMatch != null && qrImageMatch.groupCount > 0 && qrImageMatch.group(1) != null) {
                final imgSrc = qrImageMatch.group(1)!;
                // base_url() in CodeIgniter returns full URL, so check if it's already complete
                if (imgSrc.startsWith('http://') || imgSrc.startsWith('https://')) {
                  qrCodePath = imgSrc;
                } else if (imgSrc.startsWith('/')) {
                  qrCodePath = '${ApiService.baseUrl}$imgSrc';
                } else {
                  // Handle relative paths (e.g., "uploads/..." or just the filename)
                  qrCodePath = '${ApiService.baseUrl}/${imgSrc.startsWith('uploads/') ? imgSrc : 'uploads/$imgSrc'}';
                }
                print('Extracted QR Code from HTML (raw): $imgSrc'); // Debug
                print('Extracted QR Code from HTML (formatted): $qrCodePath'); // Debug
              }
            }
            
            // Extract text content from HTML, preserving structure
            // IMPORTANT: The reference number is already in the processed instructions with {timestamp} replaced
            // So we should NOT generate a new one - it's already there!
            // Better HTML parsing to preserve list items and structure
            String cleanInstructions = instructions
                // First, preserve list items with proper formatting
                .replaceAll(RegExp(r'<ul[^>]*>', caseSensitive: false), '\n') // Convert <ul> to newline
                .replaceAll(RegExp(r'</ul>', caseSensitive: false), '\n') // Convert </ul> to newline
                .replaceAll(RegExp(r'<ol[^>]*>', caseSensitive: false), '\n') // Convert <ol> to newline
                .replaceAll(RegExp(r'</ol>', caseSensitive: false), '\n') // Convert </ol> to newline
                .replaceAll(RegExp(r'<li[^>]*>', caseSensitive: false), '• ') // Convert <li> to bullet
                .replaceAll(RegExp(r'</li>', caseSensitive: false), '\n') // Convert </li> to newline
                // Preserve paragraphs and divs
                .replaceAll(RegExp(r'<p[^>]*>', caseSensitive: false), '\n') // Convert <p> to newline
                .replaceAll(RegExp(r'</p>', caseSensitive: false), '\n') // Convert </p> to newline
                .replaceAll(RegExp(r'<div[^>]*>', caseSensitive: false), '\n') // Convert <div> to newline
                .replaceAll(RegExp(r'</div>', caseSensitive: false), '\n') // Convert </div> to newline
                .replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n') // Convert <br> to newlines
                // Preserve headings but make them bold
                .replaceAll(RegExp(r'<h([1-6])[^>]*>', caseSensitive: false), '\n') // Convert <h1-6> to newline
                .replaceAll(RegExp(r'</h[1-6]>', caseSensitive: false), '\n') // Convert </h1-6> to newline
                // Remove remaining HTML tags (but keep text content)
                .replaceAll(RegExp(r'<[^>]*>'), '') // Remove remaining HTML tags
                // Decode HTML entities
                .replaceAll('&nbsp;', ' ')
                .replaceAll('&amp;', '&')
                .replaceAll('&lt;', '<')
                .replaceAll('&gt;', '>')
                .replaceAll('&quot;', '"')
                .replaceAll('&#39;', "'")
                // Clean up whitespace
                .replaceAll(RegExp(r'\n\s*\n\s*\n+'), '\n\n') // Remove multiple consecutive newlines (max 2)
                .replaceAll(RegExp(r'^\s+', multiLine: true), '') // Remove leading whitespace from lines
                .replaceAll(RegExp(r'\s+$', multiLine: true), '') // Remove trailing whitespace from lines
                .trim();
            
            print('Final instructions: $cleanInstructions'); // Debug
            print('Final QR code: $qrCodePath'); // Debug
            print('Final account: $accountNumber / $accountName'); // Debug
            
            setState(() {
              _paymentInstructions = cleanInstructions;
              _paymentMethodName = method['name'] ?? methodName;
              _qrCodePath = qrCodePath;
              _accountNumber = accountNumber;
              _accountName = accountName;
            });
          } else {
            // Fallback to generic if instructions are empty
            _setGenericInstructions(methodName, amount, method, qrCodePath, accountNumber, accountName);
          }
        } else {
          // No custom instructions - but still get account details and QR code if available
          String? accountNumber = method['account_number']?.toString();
          String? accountName = method['account_name']?.toString();
          String? qrCodePath;
          
          if (method['qr_code_path'] != null && method['qr_code_path'].toString().trim().isNotEmpty) {
            final qrPath = method['qr_code_path'].toString();
            String qrCodeSrc = qrPath;
            if (!qrCodeSrc.startsWith('http') && !qrCodeSrc.startsWith('/')) {
              qrCodeSrc = '/$qrCodeSrc';
            }
            qrCodePath = qrCodeSrc.startsWith('http') 
                ? qrCodeSrc 
                : '${ApiService.baseUrl}$qrCodeSrc';
          }
          
          _setGenericInstructions(methodName, amount, method, qrCodePath, accountNumber, accountName);
        }
      } else {
        // API returned success but no method - use generic
        _setGenericInstructions(methodName, amount, null, null, null, null);
      }
    } catch (e) {
      // Show generic instructions on error
      _setGenericInstructions(methodName, amount, null, null, null, null);
    } finally {
      setState(() {
        _isLoadingInstructions = false;
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

    // For fully paid contributions, allow any amount (for new payment group)
    // For partially paid contributions, enforce remaining balance limit
    if (!_isFullyPaid && _requestedAmount > _maxAmount) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Amount cannot exceed remaining balance (₱${NumberFormat('#,##0.00').format(_maxAmount)})')),
      );
      return;
    }
    if (_isFullyPaid && _maxAmount > 0 && _maxAmount < 999999.0 && _requestedAmount > _maxAmount) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Amount cannot exceed contribution amount (₱${NumberFormat('#,##0.00').format(_maxAmount)})')),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      // Get payment_sequence from pre-selected contribution if available
      String? paymentSequence;
      if (_selectedContribution != null && _selectedContribution!['payment_sequence'] != null) {
        final seq = _selectedContribution!['payment_sequence'];
        paymentSequence = seq is int ? seq.toString() : seq.toString();
      }

      final response = await ApiService.submitPaymentRequest(
        contributionId: _selectedContributionId!,
        requestedAmount: _requestedAmount,
        paymentMethod: _selectedPaymentMethod!,
        notes: _notesController.text.isEmpty ? null : _notesController.text,
        paymentSequence: paymentSequence,
        proofOfPaymentFile: _proofOfPaymentFile,
      );

      if (response['success'] == true) {
        if (mounted) {
          _showToast(
            context,
            response['message'] ?? 'Payment request submitted successfully!${response['reference_number'] != null ? ' Reference: ${response['reference_number']}' : ''}',
            Colors.green,
          );
          widget.onSubmitted();
        }
      } else {
        if (mounted) {
          _showToast(
            context,
            response['message'] ?? 'Failed to submit payment request',
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
                        items: [
                          // Add pre-selected contribution if it exists and not in the list
                          if (widget.preSelectedContribution != null) ...[
                            DropdownMenuItem<int>(
                              value: _selectedContributionId,
                              child: Text(widget.preSelectedContribution!['title'] ?? 'N/A'),
                            ),
                          ],
                          // Add all contributions from the list
                          ...widget.contributions.map((contribution) {
                            final contributionId = contribution['id'];
                            final id = contributionId is int 
                                ? contributionId 
                                : int.tryParse(contributionId.toString());
                            // Skip if this is the pre-selected contribution
                            if (widget.preSelectedContribution != null && 
                                id == _selectedContributionId) {
                              return null;
                            }
                            return DropdownMenuItem<int>(
                              value: id,
                              child: Text(contribution['title'] ?? 'N/A'),
                            );
                          }).whereType<DropdownMenuItem<int>>().toList(),
                        ],
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
                        // Warning banner for fully paid contributions
                        if (_isFullyPaid)
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.orange[50],
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: Colors.orange[300]!),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.warning_amber_rounded, color: Colors.orange[700], size: 20),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    'This contribution is already fully paid. Adding a payment will create a new payment group.',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.orange[900],
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        if (_isFullyPaid) const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: _isFullyPaid ? Colors.orange.withOpacity(0.1) : Colors.blue.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _isFullyPaid 
                                    ? 'Contribution Amount: ₱${NumberFormat('#,##0.00').format(_maxAmount)}'
                                    : 'Remaining Balance: ₱${NumberFormat('#,##0.00').format(_maxAmount)}',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: _isFullyPaid ? Colors.orange[700] : Colors.blue,
                                ),
                              ),
                              if (_parseDouble(_selectedContribution!['total_paid'] ?? 0) > 0)
                                Text(
                                  'Total Paid: ₱${NumberFormat('#,##0.00').format(_parseDouble(_selectedContribution!['total_paid'] ?? 0))}',
                                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 16),
                      TextFormField(
                        decoration: InputDecoration(
                          labelText: 'Requested Amount *',
                          border: const OutlineInputBorder(),
                          prefixText: '₱',
                          helperText: 'Enter amount in pesos',
                        ),
                        keyboardType: TextInputType.number,
                        initialValue: _requestedAmount > 0
                            ? NumberFormat('#,##0.00').format(_requestedAmount)
                            : '',
                        onChanged: (value) {
                          final amount = double.tryParse(value.replaceAll(',', '').replaceAll('₱', ''));
                          if (amount != null && amount > 0) {
                            setState(() {
                              _requestedAmount = amount;
                            });
                            // Load instructions if payment method is selected
                            if (_selectedPaymentMethod != null && _selectedPaymentMethod!.isNotEmpty) {
                              _loadPaymentMethodInstructions(_selectedPaymentMethod!, amount);
                            }
                          } else {
                            setState(() {
                              _requestedAmount = 0.0;
                              _paymentInstructions = null;
                              _paymentMethodName = null;
                              _qrCodePath = null;
                              _accountNumber = null;
                              _accountName = null;
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
                          // For fully paid contributions, allow any amount (for new payment group)
                          // For partially paid contributions, enforce remaining balance limit
                          if (!_isFullyPaid) {
                            if (amount > _maxAmount) {
                              return 'Amount cannot exceed remaining balance';
                            }
                          } else {
                            // For fully paid contributions, allow any amount up to the contribution amount
                            // If contribution amount is not available, allow any reasonable amount
                            if (_maxAmount > 0 && _maxAmount < 999999.0 && amount > _maxAmount) {
                              return 'Amount cannot exceed contribution amount';
                            }
                            // If _maxAmount is 999999.0 (fallback), allow any amount
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
                        items: _isLoadingPaymentMethods
                            ? [
                                const DropdownMenuItem<String>(
                                  value: null,
                                  child: Text('Loading payment methods...'),
                                ),
                              ]
                            : _paymentMethods.map((method) {
                                final methodName = method['name'] as String? ?? '';
                                return DropdownMenuItem<String>(
                                  value: methodName,
                                  child: Text(methodName),
                                );
                              }).toList(),
                        onChanged: _isLoadingPaymentMethods
                            ? null
                            : (value) {
                                setState(() {
                                  _selectedPaymentMethod = value;
                                  _paymentInstructions = null;
                                  _paymentMethodName = null;
                                  _qrCodePath = null;
                                  _accountNumber = null;
                                  _accountName = null;
                                });
                                // Load instructions when payment method is selected and amount is available
                                if (value != null) {
                                  // If amount is already set, load instructions immediately
                                  if (_requestedAmount > 0) {
                                    _loadPaymentMethodInstructions(value, _requestedAmount);
                                  }
                                  // Otherwise, instructions will load when amount is entered
                                }
                              },
                        validator: (value) {
                          if (value == null) return 'Please select a payment method';
                          return null;
                        },
                      ),
                      // Payment Instructions
                      if (_isLoadingInstructions) ...[
                        const SizedBox(height: 16),
                        const Padding(
                          padding: EdgeInsets.all(16.0),
                          child: CircularProgressIndicator(),
                        ),
                      ],
                      if (_paymentInstructions != null && _paymentInstructions!.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.blue.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.blue.withOpacity(0.3)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Icon(Icons.info_outline, color: Colors.blue, size: 20),
                                  const SizedBox(width: 8),
                                  Text(
                                    '${_paymentMethodName ?? _selectedPaymentMethod?.replaceAll('_', ' ').toUpperCase() ?? 'Payment'} Instructions',
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.blue,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              // Account Details (if available)
                              if ((_accountNumber != null && _accountNumber!.isNotEmpty) || 
                                  (_accountName != null && _accountName!.isNotEmpty)) ...[
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(color: Colors.blue.withOpacity(0.2)),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      if (_accountNumber != null && _accountNumber!.isNotEmpty) ...[
                                        Row(
                                          children: [
                                            const Text(
                                              'Account Number: ',
                                              style: TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                            Text(
                                              _accountNumber!,
                                              style: const TextStyle(fontSize: 12),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                      ],
                                      if (_accountName != null && _accountName!.isNotEmpty) ...[
                                        Row(
                                          children: [
                                            const Text(
                                              'Account Name: ',
                                              style: TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                            Text(
                                              _accountName!,
                                              style: const TextStyle(fontSize: 12),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 12),
                              ],
                              // QR Code Image
                              if (_qrCodePath != null && _qrCodePath!.isNotEmpty) ...[
                                Column(
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.qr_code, color: Colors.blue, size: 16),
                                        const SizedBox(width: 4),
                                        const Text(
                                          'QR Code',
                                          style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w600,
                                            color: Colors.blue,
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Center(
                                      child: Container(
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: Colors.blue.withOpacity(0.3)),
                                        ),
                                        child: Image.network(
                                          _qrCodePath!,
                                          width: 200,
                                          height: 200,
                                          fit: BoxFit.contain,
                                          headers: const {
                                            'Accept': 'image/*',
                                          },
                                          errorBuilder: (context, error, stackTrace) {
                                            print('QR Code load error: $error'); // Debug
                                            print('QR Code URL: $_qrCodePath'); // Debug
                                            return Column(
                                              children: [
                                                const Icon(Icons.error_outline, color: Colors.red, size: 48),
                                                const SizedBox(height: 8),
                                                const Text(
                                                  'Failed to load QR code',
                                                  style: TextStyle(fontSize: 12, color: Colors.red),
                                                ),
                                                const SizedBox(height: 4),
                                                Padding(
                                                  padding: const EdgeInsets.symmetric(horizontal: 8.0),
                                                  child: Text(
                                                    _qrCodePath!,
                                                    style: const TextStyle(fontSize: 10, color: Colors.grey),
                                                    textAlign: TextAlign.center,
                                                    maxLines: 2,
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                              ],
                                            );
                                          },
                                          loadingBuilder: (context, child, loadingProgress) {
                                            if (loadingProgress == null) return child;
                                            return const SizedBox(
                                              width: 200,
                                              height: 200,
                                              child: Center(
                                                child: CircularProgressIndicator(),
                                              ),
                                            );
                                          },
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    const Text(
                                      'Scan this QR code to make payment',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: Colors.grey,
                                        fontStyle: FontStyle.italic,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                    const SizedBox(height: 12),
                                  ],
                                ),
                              ],
                              // Instructions Text
                              SelectableText(
                                _paymentInstructions!,
                                style: const TextStyle(fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 16),
                      // Proof of Payment Upload
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Proof of Payment',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 8),
                          InkWell(
                            onTap: () async {
                              await _pickProofOfPayment();
                            },
                            child: Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                border: Border.all(color: Colors.grey.shade300),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.attach_file, color: Colors.grey),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: _proofOfPaymentPath != null
                                        ? Text(
                                            _proofOfPaymentController.text,
                                            style: const TextStyle(fontSize: 14),
                                          )
                                        : const Text(
                                            'Upload screenshot or receipt (JPG, PNG, PDF)',
                                            style: TextStyle(
                                              fontSize: 14,
                                              color: Colors.grey,
                                            ),
                                          ),
                                  ),
                                  if (_proofOfPaymentPath != null)
                                    IconButton(
                                      icon: const Icon(Icons.close, size: 20),
                                      onPressed: () {
                                        setState(() {
                                          _proofOfPaymentPath = null;
                                          _proofOfPaymentController.clear();
                                          _proofOfPaymentFile = null;
                                        });
                                      },
                                    ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Upload screenshot or receipt (JPG, PNG, PDF)',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey,
                            ),
                          ),
                        ],
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
