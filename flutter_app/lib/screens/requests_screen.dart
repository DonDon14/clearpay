import 'package:flutter/material.dart';
import 'payment_requests_screen.dart';
import 'refund_requests_screen.dart';

class RequestsScreen extends StatefulWidget {
  final int initialTab;
  
  const RequestsScreen({super.key, this.initialTab = 0});

  @override
  State<RequestsScreen> createState() => _RequestsScreenState();
}

class _RequestsScreenState extends State<RequestsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this, initialIndex: widget.initialTab);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Requests'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              // Refresh both tabs
              setState(() {});
            },
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(
              icon: Icon(Icons.payment),
              text: 'Payment Requests',
            ),
            Tab(
              icon: Icon(Icons.undo),
              text: 'Refund Requests',
            ),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: const [
          PaymentRequestsScreen(showAppBar: false),
          RefundRequestsScreen(showAppBar: false),
        ],
      ),
    );
  }
}
