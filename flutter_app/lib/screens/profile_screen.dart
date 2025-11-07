import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'dart:async';
import 'dart:typed_data';
import 'dart:html' as html show File, FileReader, FileUploadInputElement;
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
    if (!kIsWeb) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Profile picture upload is only available on web')),
      );
      return;
    }

    // Create file input element
    final input = html.FileUploadInputElement();
    input.accept = 'image/*';
    input.click();

    input.onChange.listen((e) async {
      final files = input.files;
      if (files == null || files.isEmpty) return;

      final file = files[0];
      
      // Validate file type
      final allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
      if (!allowedTypes.contains(file.type)) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Invalid file type. Only JPEG, PNG, and GIF are allowed.'),
              backgroundColor: Colors.red,
            ),
          );
        }
        return;
      }

      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('File size too large. Maximum 2MB allowed.'),
              backgroundColor: Colors.red,
            ),
          );
        }
        return;
      }

      // Show loading dialog
      if (mounted) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => const Center(
            child: CircularProgressIndicator(),
          ),
        );
      }

      try {
        // Read file as bytes
        final reader = html.FileReader();
        final completer = Completer<Uint8List>();
        
        reader.onLoadEnd.listen((e) {
          completer.complete(reader.result as Uint8List);
        });
        
        reader.onError.listen((e) {
          completer.completeError('Failed to read file');
        });
        
        reader.readAsArrayBuffer(file);
        final fileBytes = await completer.future;

        // Upload profile picture
        final response = await ApiService.uploadProfilePictureWeb(fileBytes, file.name);

        if (mounted) {
          Navigator.pop(context); // Close loading dialog
        }

        if (response['success'] == true) {
          // Update user data in AuthProvider
          final authProvider = Provider.of<AuthProvider>(context, listen: false);
          if (authProvider.user != null) {
            final updatedUser = Map<String, dynamic>.from(authProvider.user!);
            // Extract path from full URL
            final profilePictureUrl = response['profile_picture'] as String?;
            if (profilePictureUrl != null) {
              // Remove base URL to get relative path
              final relativePath = profilePictureUrl.replaceFirst(ApiService.baseUrl, '').replaceFirst(RegExp(r'^/'), '');
              updatedUser['profile_picture'] = relativePath;
              authProvider.updateUserData(updatedUser);
            }
          }

          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Profile picture uploaded successfully'),
                backgroundColor: Colors.green,
              ),
            );
            setState(() {}); // Refresh UI
          }
        } else {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(response['message'] ?? 'Failed to upload profile picture'),
                backgroundColor: Colors.red,
              ),
            );
          }
        }
      } catch (e) {
        if (mounted) {
          Navigator.pop(context); // Close loading dialog
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Error: ${e.toString()}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    });
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
        title: const Text('My Data'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Profile Picture Section - Matching web portal
              Card(
                elevation: 2,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.camera_alt, size: 20, color: Color(0xFF6366F1)),
                          const SizedBox(width: 8),
                          const Text(
                            'Profile Picture',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Center(
                        child: GestureDetector(
                          onTap: _uploadProfilePicture,
                          child: Stack(
                            children: [
                              Container(
                                width: 150,
                                height: 150,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(color: Colors.grey[300]!, width: 4),
                                  color: Colors.grey[200],
                                ),
                                child: user['profile_picture'] != null
                                    ? ClipOval(
                                        child: Image.network(
                                          '${ApiService.baseUrl}/${user['profile_picture']}',
                                          width: 150,
                                          height: 150,
                                          fit: BoxFit.cover,
                                          errorBuilder: (context, error, stackTrace) {
                                            return const Icon(Icons.person, size: 60, color: Colors.grey);
                                          },
                                        ),
                                      )
                                    : const Icon(Icons.person, size: 60, color: Colors.grey),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Click to upload a new profile picture',
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Max size: 2MB | Formats: JPEG, PNG, GIF',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey[500],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),
              
              // Personal Information Card - Matching web portal
              Card(
                elevation: 2,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Card Header with Edit button
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: const BoxDecoration(
                        border: Border(bottom: BorderSide(color: Color(0xFFE9E9E7), width: 1)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.person, size: 20, color: Color(0xFF6366F1)),
                              const SizedBox(width: 8),
                              const Text(
                                'Personal Information',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                          if (!_isEditing)
                            OutlinedButton.icon(
                              icon: const Icon(Icons.edit, size: 16),
                              label: const Text('Edit'),
                              onPressed: () {
                                setState(() {
                                  _isEditing = true;
                                });
                              },
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF6366F1),
                                side: const BorderSide(color: Color(0xFF6366F1)),
                              ),
                            ),
                        ],
                      ),
                    ),
                    // Card Body
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Payer ID (Read-only)
                          _buildInfoRow(
                            'Payer ID',
                            user['payer_id'] ?? 'N/A',
                            Icons.badge,
                            isReadOnly: true,
                          ),
                          const Divider(height: 32),
                          
                          // Full Name (Read-only)
                          _buildInfoRow(
                            'Full Name',
                            user['payer_name'] ?? 'N/A',
                            Icons.person,
                            isReadOnly: true,
                          ),
                          const Divider(height: 32),
                          
                          // Email Address
                          if (!_isEditing)
                            _buildInfoRow(
                              'Email Address',
                              user['email_address'] ?? '',
                              Icons.email,
                              isReadOnly: true,
                            )
                          else
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
                          const Divider(height: 32),
                          
                          // Contact Number
                          if (!_isEditing)
                            _buildInfoRow(
                              'Contact Number',
                              user['contact_number'] ?? '',
                              Icons.phone,
                              isReadOnly: true,
                            )
                          else
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
                          const Divider(height: 32),
                          
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
                    
                    // Edit Mode Footer with Save/Cancel buttons
                    if (_isEditing)
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: const BoxDecoration(
                          border: Border(top: BorderSide(color: Color(0xFFE9E9E7), width: 1)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            OutlinedButton.icon(
                              icon: const Icon(Icons.close, size: 16),
                              label: const Text('Cancel'),
                              onPressed: () {
                                setState(() {
                                  _isEditing = false;
                                  _loadProfileData();
                                });
                              },
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.grey[700],
                                side: BorderSide(color: Colors.grey[300]!),
                              ),
                            ),
                            const SizedBox(width: 12),
                            ElevatedButton.icon(
                              icon: _isLoading
                                  ? const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                      ),
                                    )
                                  : const Icon(Icons.save, size: 16),
                              label: Text(_isLoading ? 'Saving...' : 'Save Changes'),
                              onPressed: _isLoading ? null : _updateProfile,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF6366F1),
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                              ),
                            ),
                          ],
                        ),
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
