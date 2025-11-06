import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class AnnouncementsScreen extends StatefulWidget {
  const AnnouncementsScreen({super.key});

  @override
  State<AnnouncementsScreen> createState() => _AnnouncementsScreenState();
}

class _AnnouncementsScreenState extends State<AnnouncementsScreen> {
  bool _isLoading = true;
  List<dynamic> _announcements = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadAnnouncements();
  }

  Future<void> _loadAnnouncements() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await ApiService.getAnnouncements();
      
      if (response['success'] == true && response['data'] != null) {
        setState(() {
          _announcements = response['data'];
        });
      } else {
        setState(() {
          _errorMessage = response['error'] ?? 'Failed to load announcements';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Failed to load announcements: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Announcements'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadAnnouncements,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? _buildErrorWidget()
              : RefreshIndicator(
                  onRefresh: _loadAnnouncements,
                  child: _announcements.isEmpty
                      ? _buildEmptyState()
                      : _buildAnnouncementsList(),
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
            onPressed: _loadAnnouncements,
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
          Icon(Icons.notifications_off, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'No Announcements',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
          const SizedBox(height: 8),
          Text(
            'There are currently no announcements available.',
            style: TextStyle(color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildAnnouncementsList() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _announcements.length,
      itemBuilder: (context, index) {
        final announcement = _announcements[index];
        return _buildAnnouncementCard(announcement);
      },
    );
  }

  Widget _buildAnnouncementCard(Map<String, dynamic> announcement) {
    final title = announcement['title'] ?? 'No Title';
    final text = announcement['text'] ?? '';
    final date = announcement['created_at'] ?? '';
    final isImportant = announcement['priority'] == 'high' || announcement['is_important'] == true;

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      color: isImportant ? Colors.amber.withOpacity(0.1) : null,
      child: InkWell(
        onTap: () => _showAnnouncementDetails(announcement),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.orange.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.notifications,
                      color: Colors.orange,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: isImportant ? Colors.amber[900] : null,
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
                  if (isImportant)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.amber,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        'IMPORTANT',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                ],
              ),
              if (text.isNotEmpty) ...[
                const SizedBox(height: 12),
                Text(
                  text.length > 200 ? '${text.substring(0, 200)}...' : text,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[700],
                  ),
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
              if (text.length > 200)
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: Text(
                    'Tap to read more',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.blue[600],
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  void _showAnnouncementDetails(Map<String, dynamic> announcement) {
    final title = announcement['title'] ?? 'No Title';
    final text = announcement['text'] ?? '';
    final date = announcement['created_at'] ?? '';

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.notifications, color: Colors.orange),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                title,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                _formatDate(date),
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 16),
              if (text.isNotEmpty)
                Text(
                  text,
                  style: const TextStyle(
                    fontSize: 14,
                    height: 1.5,
                  ),
                )
              else
                Text(
                  'No content available.',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[600],
                    fontStyle: FontStyle.italic,
                  ),
                ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
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
