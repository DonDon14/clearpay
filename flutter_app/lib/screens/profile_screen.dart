import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _isEditing = false;
  bool _isLoading = false;
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _contactController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadProfileData();
  }

  void _loadProfileData() {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.user;
    if (user != null) {
      _emailController.text = user['email_address'] ?? '';
      _contactController.text = user['contact_number'] ?? '';
    }
  }

  Future<void> _updateProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await ApiService.updateProfile(
        emailAddress: _emailController.text,
        contactNumber: _contactController.text,
      );

      if (response['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Profile updated successfully'),
              backgroundColor: Colors.green,
            ),
          );
          setState(() {
            _isEditing = false;
          });
          // Update user data in provider
          final authProvider = Provider.of<AuthProvider>(context, listen: false);
          if (authProvider.user != null) {
            final updatedUser = Map<String, dynamic>.from(authProvider.user!);
            updatedUser['email_address'] = _emailController.text;
            updatedUser['contact_number'] = _contactController.text;
            // Note: In a real app, you'd reload from API or update the provider
          }
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'Failed to update profile'),
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
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _uploadProfilePicture() async {
    // Note: For web, we'll need to use file picker
    // This is a simplified version that shows the intent
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Profile picture upload requires file picker. Feature coming soon.'),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _contactController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;
    
    if (user == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Profile')),
        body: const Center(child: Text('Not logged in')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Profile'),
        actions: [
          if (!_isEditing)
            IconButton(
              icon: const Icon(Icons.edit),
              onPressed: () {
                setState(() {
                  _isEditing = true;
                });
              },
            )
          else
            IconButton(
              icon: const Icon(Icons.close),
              onPressed: () {
                setState(() {
                  _isEditing = false;
                  _loadProfileData();
                });
              },
            ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Profile Picture Section
              Center(
                child: Stack(
                  children: [
                    CircleAvatar(
                      radius: 60,
                      backgroundColor: Colors.grey[300],
                      backgroundImage: user['profile_picture'] != null
                          ? NetworkImage('${ApiService.baseUrl}/${user['profile_picture']}')
                          : null,
                      child: user['profile_picture'] == null
                          ? const Icon(Icons.person, size: 60, color: Colors.grey)
                          : null,
                    ),
                    if (_isEditing)
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: CircleAvatar(
                          radius: 20,
                          backgroundColor: const Color(0xFF2196F3),
                          child: IconButton(
                            icon: const Icon(Icons.camera_alt, size: 20, color: Colors.white),
                            onPressed: _uploadProfilePicture,
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              
              // Personal Information Card
              Card(
                elevation: 2,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Personal Information',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const Divider(height: 32),
                      
                      // Payer ID (Read-only)
                      _buildInfoRow(
                        'Payer ID',
                        user['payer_id'] ?? 'N/A',
                        Icons.badge,
                        isReadOnly: true,
                      ),
                      const SizedBox(height: 16),
                      
                      // Full Name (Read-only)
                      _buildInfoRow(
                        'Full Name',
                        user['payer_name'] ?? 'N/A',
                        Icons.person,
                        isReadOnly: true,
                      ),
                      const SizedBox(height: 16),
                      
                      // Email Address
                      _buildEditableField(
                        'Email Address',
                        _emailController,
                        Icons.email,
                        TextInputType.emailAddress,
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter an email address';
                          }
                          if (!value.contains('@')) {
                            return 'Please enter a valid email address';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      
                      // Contact Number
                      _buildEditableField(
                        'Contact Number',
                        _contactController,
                        Icons.phone,
                        TextInputType.phone,
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter a contact number';
                          }
                          if (value.length < 10) {
                            return 'Please enter a valid contact number';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      
                      // Member Since (Read-only)
                      _buildInfoRow(
                        'Member Since',
                        user['created_at'] != null
                            ? DateFormat('MMM dd, yyyy').format(DateTime.parse(user['created_at']))
                            : 'N/A',
                        Icons.calendar_today,
                        isReadOnly: true,
                      ),
                    ],
                  ),
                ),
              ),
              
              if (_isEditing) ...[
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: _isLoading ? null : _updateProfile,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2196F3),
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        )
                      : const Text(
                          'Save Changes',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, IconData icon, {bool isReadOnly = false}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: Colors.grey[600]),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildEditableField(
    String label,
    TextEditingController controller,
    IconData icon,
    TextInputType keyboardType, {
    String? Function(String?)? validator,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 20, color: Colors.grey[600]),
            const SizedBox(width: 12),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          enabled: _isEditing,
          decoration: InputDecoration(
            border: const OutlineInputBorder(),
            filled: !_isEditing,
            fillColor: _isEditing ? null : Colors.grey[100],
          ),
          validator: validator,
        ),
      ],
    );
  }
}
