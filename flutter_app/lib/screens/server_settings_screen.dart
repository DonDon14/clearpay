import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/notion_app_bar.dart';
import '../widgets/notion_card.dart';
import '../widgets/notion_text.dart';

class ServerSettingsScreen extends StatefulWidget {
  const ServerSettingsScreen({super.key});

  @override
  State<ServerSettingsScreen> createState() => _ServerSettingsScreenState();
}

class _ServerSettingsScreenState extends State<ServerSettingsScreen> {
  final _ngrokUrlController = TextEditingController();
  bool _isLoading = false;
  bool _isFetching = false;
  String? _currentUrl;
  String? _errorMessage;
  String? _successMessage;

  @override
  void initState() {
    super.initState();
    _loadCurrentUrl();
  }

  @override
  void dispose() {
    _ngrokUrlController.dispose();
    super.dispose();
  }

  Future<void> _loadCurrentUrl() async {
    final url = await ApiService.getNgrokUrl();
    setState(() {
      _currentUrl = url;
      _ngrokUrlController.text = url ?? '';
    });
  }

  Future<void> _fetchFromNgrokApi() async {
    setState(() {
      _isFetching = true;
      _errorMessage = null;
      _successMessage = null;
    });

    try {
      final url = await ApiService.fetchNgrokUrlFromApi();
      if (url != null) {
        setState(() {
          _ngrokUrlController.text = url;
          _currentUrl = url;
          _successMessage = 'Successfully fetched ngrok URL!';
          _errorMessage = null;
        });
      } else {
        setState(() {
          _errorMessage = 'Could not fetch ngrok URL. Make sure ngrok is running and accessible at http://127.0.0.1:4040';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error fetching ngrok URL: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isFetching = false;
      });
    }
  }

  Future<void> _saveUrl() async {
    final url = _ngrokUrlController.text.trim();
    
    if (url.isEmpty) {
      // Clear ngrok URL (use local network)
      await ApiService.setNgrokUrl(null);
      setState(() {
        _currentUrl = null;
        _successMessage = 'ngrok URL cleared. App will use local network IP.';
        _errorMessage = null;
      });
      return;
    }

    // Validate URL format
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      setState(() {
        _errorMessage = 'URL must start with http:// or https://';
        _successMessage = null;
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _successMessage = null;
    });

    try {
      await ApiService.setNgrokUrl(url);
      setState(() {
        _currentUrl = url;
        _successMessage = 'Server URL saved successfully!';
        _errorMessage = null;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Error saving URL: ${e.toString()}';
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
      backgroundColor: const Color(0xFFFFFBFE),
      appBar: NotionAppBar(
        title: 'Server Settings',
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            NotionCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  NotionText(
                    'Server Configuration',
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                  const SizedBox(height: 8),
                  NotionText(
                    'Configure how the app connects to your ClearPay server. '
                    'You can use a local network IP or an ngrok URL for external access.',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            NotionCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  NotionText(
                    'ngrok URL (Optional)',
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                  const SizedBox(height: 8),
                  NotionText(
                    'If you\'re using ngrok for external access, enter your ngrok URL here. '
                    'Leave empty to use local network IP.',
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _ngrokUrlController,
                    decoration: InputDecoration(
                      labelText: 'ngrok URL',
                      hintText: 'https://abc123.ngrok.io/ClearPay/public',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(3),
                      ),
                      prefixIcon: const Icon(Icons.link),
                    ),
                    keyboardType: TextInputType.url,
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _isFetching ? null : _fetchFromNgrokApi,
                          icon: _isFetching
                              ? const SizedBox(
                                  width: 16,
                                  height: 16,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Icon(Icons.refresh),
                          label: Text(_isFetching ? 'Fetching...' : 'Auto-Fetch from ngrok'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF37352F),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _isLoading ? null : _saveUrl,
                          icon: _isLoading
                              ? const SizedBox(
                                  width: 16,
                                  height: 16,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Icon(Icons.save),
                          label: Text(_isLoading ? 'Saving...' : 'Save'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF2196F3),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (_currentUrl != null) ...[
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE3F2FD),
                        borderRadius: BorderRadius.circular(3),
                        border: Border.all(color: const Color(0xFF2196F3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.info_outline, color: Color(0xFF2196F3)),
                          const SizedBox(width: 8),
                          Expanded(
                            child: NotionText(
                              'Current: $_currentUrl',
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                  if (_errorMessage != null) ...[
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFEBEE),
                        borderRadius: BorderRadius.circular(3),
                        border: Border.all(color: Colors.red),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline, color: Colors.red),
                          const SizedBox(width: 8),
                          Expanded(
                            child: NotionText(
                              _errorMessage!,
                              fontSize: 12,
                              color: Colors.red,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                  if (_successMessage != null) ...[
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE8F5E9),
                        borderRadius: BorderRadius.circular(3),
                        border: Border.all(color: Colors.green),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.check_circle_outline, color: Colors.green),
                          const SizedBox(width: 8),
                          Expanded(
                            child: NotionText(
                              _successMessage!,
                              fontSize: 12,
                              color: Colors.green,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 16),
            NotionCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  NotionText(
                    'Local Network',
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                  const SizedBox(height: 8),
                  NotionText(
                    'If ngrok URL is not set, the app will use your local network IP:',
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF5F5F5),
                      borderRadius: BorderRadius.circular(3),
                    ),
                    child: NotionText(
                      'http://${ApiService.serverIpAddress}${ApiService.projectPathValue}',
                      // Note: fontFamily not directly supported in NotionText
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            NotionCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  NotionText(
                    'How to Update ngrok URL',
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                  const SizedBox(height: 8),
                  NotionText(
                    '1. Start ngrok: ngrok http 80\n'
                    '2. Get your URL from http://127.0.0.1:4040\n'
                    '3. Click "Auto-Fetch" button above, or\n'
                    '4. Manually enter: https://YOUR_URL.ngrok.io/ClearPay/public\n'
                    '5. Click "Save"\n\n'
                    'The app will automatically use the new URL for all API requests.',
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

