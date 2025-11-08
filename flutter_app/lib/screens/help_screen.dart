import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

// Web portal color scheme
const _primaryBlue = Color(0xFF3B82F6);
const _darkGray = Color(0xFF1F2937);
const _mediumGray = Color(0xFF6B7280);
const _lightGray = Color(0xFFF3F4F6);

class HelpScreen extends StatefulWidget {
  const HelpScreen({super.key});

  @override
  State<HelpScreen> createState() => _HelpScreenState();
}

class _HelpScreenState extends State<HelpScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _searchHelp(String query) {
    if (query.isEmpty) return;
    
    // Scroll to relevant section based on search query
    final queryLower = query.toLowerCase();
    if (queryLower.contains('getting started') || queryLower.contains('start')) {
      _scrollToSection('getting-started');
    } else if (queryLower.contains('faq') || queryLower.contains('question')) {
      _scrollToSection('faq');
    } else if (queryLower.contains('contact') || queryLower.contains('support')) {
      _scrollToSection('contact');
    } else if (queryLower.contains('mobile') || queryLower.contains('app')) {
      _scrollToSection('mobile-app');
    } else if (queryLower.contains('payment request')) {
      _scrollToSection('payment-requests');
    } else if (queryLower.contains('refund')) {
      _scrollToSection('refund-requests');
    } else {
      // Show message that search found results
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Found results for "$query". Scroll to see relevant sections.'),
          duration: const Duration(seconds: 2),
        ),
      );
    }
  }

  void _scrollToSection(String sectionId) {
    // Simple scroll implementation - in a real app, you'd use keys for sections
    // For now, we'll just show a message
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Scrolling to $sectionId section...'),
        duration: const Duration(seconds: 1),
      ),
    );
  }

  Future<void> _launchEmail() async {
    final Uri emailUri = Uri(
      scheme: 'mailto',
      path: 'support@clearpay.com',
      query: 'subject=ClearPay Support Request',
    );
    try {
      if (await canLaunchUrl(emailUri)) {
        await launchUrl(emailUri, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Could not launch email client')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not launch email client')),
        );
      }
    }
  }

  Future<void> _launchPhone() async {
    final Uri phoneUri = Uri(scheme: 'tel', path: '+631234567890');
    try {
      if (await canLaunchUrl(phoneUri)) {
        await launchUrl(phoneUri, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Could not launch phone dialer')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not launch phone dialer')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text('Help & Support'),
        backgroundColor: Colors.white,
        foregroundColor: _darkGray,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        controller: _scrollController,
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Page Header
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Help & Support',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w600,
                        color: _darkGray,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Get assistance and find answers to common questions',
                      style: TextStyle(
                        fontSize: 14,
                        color: _mediumGray,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Search Help
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Search Help',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        color: _darkGray,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _searchController,
                            decoration: InputDecoration(
                              hintText: 'Search for help topics, FAQs, or guides...',
                              prefixIcon: const Icon(Icons.search, color: _mediumGray),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
                              ),
                              filled: true,
                              fillColor: Colors.white,
                            ),
                            onSubmitted: _searchHelp,
                          ),
                        ),
                        const SizedBox(width: 8),
                        ElevatedButton.icon(
                          onPressed: () => _searchHelp(_searchController.text),
                          icon: const Icon(Icons.search),
                          label: const Text('Search'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: _primaryBlue,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Quick Links
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Quick Links',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        color: _darkGray,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: _QuickLinkCard(
                            icon: Icons.play_circle_outline,
                            iconColor: _primaryBlue,
                            title: 'Getting Started',
                            subtitle: 'New user guide',
                            onTap: () => _scrollToSection('getting-started'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _QuickLinkCard(
                            icon: Icons.help_outline,
                            iconColor: Colors.cyan,
                            title: 'FAQ',
                            subtitle: 'Frequently asked questions',
                            onTap: () => _scrollToSection('faq'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _QuickLinkCard(
                            icon: Icons.email_outlined,
                            iconColor: Colors.green,
                            title: 'Contact Support',
                            subtitle: 'Get in touch with us',
                            onTap: () => _scrollToSection('contact'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _QuickLinkCard(
                            icon: Icons.phone_android,
                            iconColor: Colors.orange,
                            title: 'Mobile App',
                            subtitle: 'Using the mobile app',
                            onTap: () => _scrollToSection('mobile-app'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Getting Started
            _SectionCard(
              title: 'Getting Started',
              children: [
                _HelpAccordion(
                  title: 'Creating Your Account',
                  icon: Icons.person_add,
                  iconColor: _primaryBlue,
                  children: [
                    _buildNumberedList([
                      'Go to the Sign Up page',
                      'Fill in your information:\n  • Student ID (required)\n  • Password\n  • Full Name\n  • Email Address\n  • Contact Number\n  • Course/Department',
                      'Click "Sign Up"',
                      'Check your email for the verification code',
                      'Enter the verification code to complete registration',
                    ]),
                  ],
                ),
                _HelpAccordion(
                  title: 'Logging In',
                  icon: Icons.login,
                  iconColor: Colors.green,
                  children: [
                    _buildNumberedList([
                      'Go to the Login page',
                      'Enter your Student ID and Password',
                      'Click "Login"',
                      'You will be redirected to your dashboard',
                    ]),
                    const SizedBox(height: 12),
                    Text(
                      'Forgot Password? Click on "Forgot Password?" and follow the instructions to reset your password.',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: _darkGray,
                      ),
                    ),
                  ],
                ),
                _HelpAccordion(
                  title: 'Viewing Your Contributions',
                  icon: Icons.account_balance_wallet,
                  iconColor: Colors.cyan,
                  children: [
                    _buildNumberedList([
                      'Navigate to Contributions from the sidebar',
                      'You will see all active contributions that you need to pay',
                      'Each contribution shows:\n  • Contribution title and description\n  • Amount due per payer\n  • Due date\n  • Your payment status (Unpaid, Partially Paid, Fully Paid)',
                      'Click on a contribution to view details and payment options',
                    ]),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Payment Requests
            _SectionCard(
              title: 'Submitting Payment Requests',
              children: [
                _HelpAccordion(
                  title: 'How to Submit a Payment Request',
                  icon: Icons.send,
                  iconColor: _primaryBlue,
                  children: [
                    _buildNumberedList([
                      'Go to Contributions from the sidebar',
                      'Select the contribution you want to pay',
                      'Click "Request Payment" or "Submit Payment Request"',
                      'Fill in the payment request details:\n  • Select payment method (GCash, Bank Transfer, etc.)\n  • Enter the amount you are paying\n  • Enter reference number (transaction ID)\n  • Upload proof of payment (screenshot or photo)\n  • Add any notes (optional)',
                      'Click "Submit" to send your payment request',
                    ]),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Note: Your payment request will be reviewed by the admin. You will receive a notification when it is approved or rejected.',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          color: _darkGray,
                        ),
                      ),
                    ),
                  ],
                ),
                _HelpAccordion(
                  title: 'Payment Request Status',
                  icon: Icons.access_time,
                  iconColor: Colors.orange,
                  children: [
                    const Text(
                      'Your payment requests can have the following statuses:',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 8),
                    _buildBulletList([
                      'Pending: Your request is waiting for admin approval',
                      'Approved: Your payment has been approved and recorded',
                      'Rejected: Your payment request was rejected. Check admin notes for the reason',
                      'Processed: Your payment has been fully processed',
                    ]),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Refund Requests
            _SectionCard(
              title: 'Submitting Refund Requests',
              children: [
                _HelpAccordion(
                  title: 'How to Request a Refund',
                  icon: Icons.undo,
                  iconColor: _primaryBlue,
                  children: [
                    _buildNumberedList([
                      'Navigate to Refund Requests from the sidebar',
                      'Click "Request Refund"',
                      'Select the payment you want to refund',
                      'Fill in refund details:\n  • Refund amount\n  • Refund method (how you want to receive the refund)\n  • Reason for refund\n  • Additional notes (optional)',
                      'Click "Submit Refund Request"',
                    ]),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Note: Refund requests are subject to admin approval. You will be notified of the decision.',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          color: _darkGray,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Payment History
            _SectionCard(
              title: 'Viewing Payment History',
              children: [
                const Text('To view your payment history:'),
                const SizedBox(height: 12),
                _buildNumberedList([
                  'Navigate to Payment History from the sidebar',
                  'You will see all your payment records',
                  'Each record shows:\n  • Payment date\n  • Contribution name\n  • Amount paid\n  • Payment method\n  • Reference number\n  • Payment status',
                  'You can filter and search your payment history',
                ]),
              ],
            ),
            const SizedBox(height: 16),

            // My Data / Profile
            _SectionCard(
              title: 'Managing Your Profile',
              children: [
                const Text('To update your profile information:'),
                const SizedBox(height: 12),
                _buildNumberedList([
                  'Navigate to My Data from the sidebar',
                  'Click "Edit Profile"',
                  'Update your information:\n  • Full Name\n  • Email Address\n  • Contact Number\n  • Course/Department\n  • Profile Picture',
                  'Click "Save Changes" to update your profile',
                ]),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'Note: Some information (like Student ID) cannot be changed. Contact support if you need to update restricted fields.',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: _darkGray,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Announcements
            _SectionCard(
              title: 'Viewing Announcements',
              children: [
                const Text('To view announcements:'),
                const SizedBox(height: 12),
                _buildNumberedList([
                  'Navigate to Announcements from the sidebar',
                  'You will see all announcements sent to students',
                  'Announcements are sorted by date (newest first)',
                  'Click on an announcement to view full details',
                ]),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'Note: Important announcements may appear as popup notifications when you log in.',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: _darkGray,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Mobile App
            _SectionCard(
              title: 'Using the Mobile App',
              children: [
                const Text(
                  'ClearPay has a mobile app available for Android and iOS devices. The mobile app provides the same features as the web portal:',
                ),
                const SizedBox(height: 12),
                _buildBulletList([
                  'View your dashboard and payment summary',
                  'View contributions and payment status',
                  'Submit payment requests',
                  'View payment history',
                  'Submit refund requests',
                  'View announcements',
                  'Receive push notifications',
                  'Update your profile',
                ]),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'Download: The mobile app can be downloaded from the app store. Use the same login credentials as the web portal.',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: _darkGray,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // FAQ
            _SectionCard(
              title: 'Frequently Asked Questions',
              children: [
                _HelpAccordion(
                  title: 'How long does it take for my payment request to be approved?',
                  children: [
                    const Text(
                      'Payment requests are typically reviewed within 24-48 hours during business days. You will receive a notification once your request has been processed.',
                    ),
                  ],
                ),
                _HelpAccordion(
                  title: 'What should I do if my payment request is rejected?',
                  children: [
                    const Text(
                      'If your payment request is rejected, check the admin notes for the reason. Common reasons include:',
                    ),
                    const SizedBox(height: 8),
                    _buildBulletList([
                      'Incorrect reference number',
                      'Unclear proof of payment',
                      'Payment amount mismatch',
                      'Wrong payment method',
                    ]),
                    const SizedBox(height: 8),
                    const Text(
                      'You can submit a new payment request with corrected information.',
                    ),
                  ],
                ),
                _HelpAccordion(
                  title: 'How do I know if my payment was received?',
                  children: [
                    const Text('Once your payment request is approved, you will:'),
                    const SizedBox(height: 8),
                    _buildBulletList([
                      'Receive a notification',
                      'See the payment in your Payment History',
                      'See your contribution status update (Partially Paid or Fully Paid)',
                    ]),
                  ],
                ),
                _HelpAccordion(
                  title: 'Can I pay in installments?',
                  children: [
                    const Text(
                      'Yes, you can make partial payments. The system will track your payment progress and show your status as "Partially Paid" until the full amount is received.',
                    ),
                  ],
                ),
                _HelpAccordion(
                  title: 'How do I request a refund?',
                  children: [
                    _buildNumberedList([
                      'Go to Refund Requests',
                      'Click "Request Refund"',
                      'Select the payment you want to refund',
                      'Fill in the refund details and reason',
                      'Submit your request',
                    ]),
                    const SizedBox(height: 8),
                    const Text(
                      'Refund requests are subject to admin approval and policy.',
                    ),
                  ],
                ),
                _HelpAccordion(
                  title: 'I forgot my password. What should I do?',
                  children: [
                    _buildNumberedList([
                      'Go to the Login page',
                      'Click "Forgot Password?"',
                      'Enter your email address',
                      'Check your email for the reset code',
                      'Enter the reset code and create a new password',
                    ]),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Contact Support
            _SectionCard(
              title: 'Contact Support',
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Card(
                        elevation: 2,
                        child: InkWell(
                          onTap: _launchEmail,
                          borderRadius: BorderRadius.circular(8),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: _primaryBlue.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: const Icon(
                                    Icons.email,
                                    color: _primaryBlue,
                                    size: 32,
                                  ),
                                ),
                                const SizedBox(height: 12),
                                const Text(
                                  'Email Support',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                    color: _darkGray,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'support@clearpay.com',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: _mediumGray,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'Send us an email and we\'ll get back to you within 24 hours.',
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: _mediumGray,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Card(
                        elevation: 2,
                        child: InkWell(
                          onTap: _launchPhone,
                          borderRadius: BorderRadius.circular(8),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.green.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: const Icon(
                                    Icons.phone,
                                    color: Colors.green,
                                    size: 32,
                                  ),
                                ),
                                const SizedBox(height: 12),
                                const Text(
                                  'Phone Support',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                    color: _darkGray,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  '+63 (0) 123 456 7890',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: _mediumGray,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'Available Monday to Friday, 9:00 AM - 5:00 PM (PHT).',
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: _mediumGray,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildNumberedList(List<String> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: items.asMap().entries.map((entry) {
            return Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '${entry.key + 1}.',
                style: const TextStyle(
                  fontWeight: FontWeight.w600,
                  color: _darkGray,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  entry.value,
                  style: const TextStyle(color: _darkGray),
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildBulletList(List<String> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: items.map((item) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                '•',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: _darkGray,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  item,
                  style: const TextStyle(color: _darkGray),
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

class _QuickLinkCard extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _QuickLinkCard({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Icon(icon, color: iconColor, size: 48),
              const SizedBox(height: 12),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: _darkGray,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),
              Text(
                subtitle,
                style: const TextStyle(
                  fontSize: 12,
                  color: _mediumGray,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SectionCard extends StatelessWidget {
  final String title;
  final List<Widget> children;

  const _SectionCard({
    required this.title,
    required this.children,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: _darkGray,
              ),
            ),
            const SizedBox(height: 16),
            ...children,
          ],
        ),
      ),
    );
  }
}

class _HelpAccordion extends StatefulWidget {
  final String title;
  final IconData? icon;
  final Color? iconColor;
  final List<Widget> children;
  final bool initiallyExpanded;

  const _HelpAccordion({
    required this.title,
    this.icon,
    this.iconColor,
    required this.children,
    this.initiallyExpanded = false,
  });

  @override
  State<_HelpAccordion> createState() => _HelpAccordionState();
}

class _HelpAccordionState extends State<_HelpAccordion> {
  bool _isExpanded = false;

  @override
  void initState() {
    super.initState();
    _isExpanded = widget.initiallyExpanded;
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 12),
      child: ExpansionTile(
        initiallyExpanded: widget.initiallyExpanded,
        title: Row(
          children: [
            if (widget.icon != null) ...[
              Icon(
                widget.icon,
                color: widget.iconColor ?? _primaryBlue,
                size: 20,
              ),
              const SizedBox(width: 8),
            ],
            Expanded(
              child: Text(
                widget.title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: _darkGray,
                ),
              ),
            ),
          ],
        ),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: widget.children,
            ),
          ),
        ],
      ),
    );
  }
}

