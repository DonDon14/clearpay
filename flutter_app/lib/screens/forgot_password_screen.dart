import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'login_screen.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  int _currentStep = 1; // 1: Email, 2: Code, 3: New Password
  final _formKey = GlobalKey<FormState>();
  
  // Step 1: Email
  final _emailController = TextEditingController();
  bool _isLoadingEmail = false;
  String? _emailError;
  String? _userEmail;
  int? _testResetCode; // For testing purposes
  
  // Step 2: Reset Code
  final _resetCodeController = TextEditingController();
  bool _isLoadingCode = false;
  String? _codeError;
  
  // Step 3: New Password
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  bool _isLoadingReset = false;
  String? _resetError;

  @override
  void dispose() {
    _emailController.dispose();
    _resetCodeController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _handleSendCode() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoadingEmail = true;
      _emailError = null;
    });

    try {
      final response = await ApiService.forgotPassword(_emailController.text.trim());

      if (!mounted) return;

      setState(() {
        _isLoadingEmail = false;
      });

      if (response['success'] == true) {
        final emailSent = response['email_sent'] ?? true;
        setState(() {
          _userEmail = response['email'] ?? _emailController.text.trim();
          // Only show test code if email failed to send
          _testResetCode = !emailSent ? response['reset_code'] : null;
          _currentStep = 2;
        });
      } else {
        setState(() {
          _emailError = response['error'] ?? 'Failed to send reset code. Please try again.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoadingEmail = false;
        _emailError = 'An error occurred: ${e.toString()}';
      });
    }
  }

  Future<void> _handleVerifyCode() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoadingCode = true;
      _codeError = null;
    });

    try {
      final response = await ApiService.verifyResetCode(
        _userEmail ?? _emailController.text.trim(),
        _resetCodeController.text.trim(),
      );

      if (!mounted) return;

      setState(() {
        _isLoadingCode = false;
      });

      if (response['success'] == true) {
        setState(() {
          _currentStep = 3;
        });
      } else {
        setState(() {
          _codeError = response['error'] ?? 'Invalid reset code.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoadingCode = false;
        _codeError = 'An error occurred: ${e.toString()}';
      });
    }
  }

  Future<void> _handleResetPassword() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoadingReset = true;
      _resetError = null;
    });

    try {
      final response = await ApiService.resetPassword(
        email: _userEmail ?? _emailController.text.trim(),
        resetCode: _resetCodeController.text.trim(),
        password: _passwordController.text,
        confirmPassword: _confirmPasswordController.text,
      );

      if (!mounted) return;

      setState(() {
        _isLoadingReset = false;
      });

      if (response['success'] == true) {
        // Show success message and navigate to login
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'Password reset successfully!'),
            backgroundColor: Colors.green,
            duration: const Duration(seconds: 3),
          ),
        );
        
        // Navigate to login after a delay
        Future.delayed(const Duration(seconds: 1), () {
          if (mounted) {
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (_) => const LoginScreen()),
            );
          }
        });
      } else {
        setState(() {
          _resetError = response['error'] ?? 'Failed to reset password. Please try again.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoadingReset = false;
        _resetError = 'An error occurred: ${e.toString()}';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: 20),
                
                // Back button
                Align(
                  alignment: Alignment.centerLeft,
                  child: IconButton(
                    icon: const Icon(Icons.arrow_back),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                ),
                
                const SizedBox(height: 20),
                
                // Logo/Icon
                Icon(
                  Icons.lock_reset,
                  size: 80,
                  color: Theme.of(context).primaryColor,
                ),
                
                const SizedBox(height: 20),
                
                // App Title
                Text(
                  'ClearPay',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
                
                const SizedBox(height: 8),
                
                // Subtitle
                Text(
                  _currentStep == 1 
                      ? 'Reset Your Password'
                      : _currentStep == 2
                          ? 'Verify Reset Code'
                          : 'Set New Password',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 18,
                    color: Colors.grey[600],
                  ),
                ),
                
                const SizedBox(height: 8),
                
                // Step indicator
                Text(
                  'Step $_currentStep of 3',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[500],
                  ),
                ),
                
                const SizedBox(height: 32),
                
                // Step 1: Email Input
                if (_currentStep == 1) ...[
                  if (_emailError != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.red[50],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.red[200]!),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.error_outline, color: Colors.red[700]),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              _emailError!,
                              style: TextStyle(color: Colors.red[700]),
                            ),
                          ),
                        ],
                      ),
                    ),
                  
                  TextFormField(
                    controller: _emailController,
                    decoration: InputDecoration(
                      labelText: 'Email Address',
                      hintText: 'Enter your email address',
                      prefixIcon: const Icon(Icons.email),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      filled: true,
                      fillColor: Colors.grey[50],
                    ),
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.done,
                    onFieldSubmitted: (_) => _handleSendCode(),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Please enter your email address';
                      }
                      final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
                      if (!emailRegex.hasMatch(value.trim())) {
                        return 'Please enter a valid email address';
                      }
                      return null;
                    },
                  ),
                  
                  const SizedBox(height: 24),
                  
                  ElevatedButton(
                    onPressed: _isLoadingEmail ? null : _handleSendCode,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isLoadingEmail
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Text(
                            'Send Verification Code',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                  ),
                ],
                
                // Step 2: Reset Code
                if (_currentStep == 2) ...[
                  Text(
                    'We sent a verification code to',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _userEmail ?? '',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                  const SizedBox(height: 24),
                  
                  if (_codeError != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.red[50],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.red[200]!),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.error_outline, color: Colors.red[700]),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              _codeError!,
                              style: TextStyle(color: Colors.red[700]),
                            ),
                          ),
                        ],
                      ),
                    ),
                  
                  // Test code display (only shown if email failed to send)
                  if (_testResetCode != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.orange[50],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.orange[200]!),
                      ),
                      child: Column(
                        children: [
                          Text(
                            'Test Reset Code (Email sending failed):',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: Colors.orange[700],
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '$_testResetCode',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: Colors.orange[700],
                              letterSpacing: 2,
                            ),
                          ),
                        ],
                      ),
                    ),
                  
                  TextFormField(
                    controller: _resetCodeController,
                    decoration: InputDecoration(
                      labelText: 'Verification Code',
                      hintText: 'Enter 6-digit code',
                      prefixIcon: const Icon(Icons.lock_outline),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      filled: true,
                      fillColor: Colors.grey[50],
                    ),
                    keyboardType: TextInputType.number,
                    textInputAction: TextInputAction.done,
                    maxLength: 6,
                    onFieldSubmitted: (_) => _handleVerifyCode(),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Please enter the verification code';
                      }
                      if (value.trim().length != 6) {
                        return 'Verification code must be 6 digits';
                      }
                      return null;
                    },
                  ),
                  
                  const SizedBox(height: 24),
                  
                  ElevatedButton(
                    onPressed: _isLoadingCode ? null : _handleVerifyCode,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isLoadingCode
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Text(
                            'Verify Code',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                  ),
                  
                  const SizedBox(height: 12),
                  
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _currentStep = 1;
                        _resetCodeController.clear();
                        _codeError = null;
                      });
                    },
                    child: const Text('Back to Email Entry'),
                  ),
                ],
                
                // Step 3: New Password
                if (_currentStep == 3) ...[
                  if (_resetError != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.red[50],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.red[200]!),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.error_outline, color: Colors.red[700]),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              _resetError!,
                              style: TextStyle(color: Colors.red[700]),
                            ),
                          ),
                        ],
                      ),
                    ),
                  
                  TextFormField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    decoration: InputDecoration(
                      labelText: 'New Password',
                      hintText: 'Enter new password',
                      prefixIcon: const Icon(Icons.lock),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword ? Icons.visibility : Icons.visibility_off,
                        ),
                        onPressed: () {
                          setState(() {
                            _obscurePassword = !_obscurePassword;
                          });
                        },
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      filled: true,
                      fillColor: Colors.grey[50],
                    ),
                    textInputAction: TextInputAction.next,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Please enter your new password';
                      }
                      if (value.length < 6) {
                        return 'Password must be at least 6 characters';
                      }
                      return null;
                    },
                  ),
                  
                  const SizedBox(height: 16),
                  
                  TextFormField(
                    controller: _confirmPasswordController,
                    obscureText: _obscureConfirmPassword,
                    decoration: InputDecoration(
                      labelText: 'Confirm New Password',
                      hintText: 'Confirm new password',
                      prefixIcon: const Icon(Icons.lock_outline),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscureConfirmPassword ? Icons.visibility : Icons.visibility_off,
                        ),
                        onPressed: () {
                          setState(() {
                            _obscureConfirmPassword = !_obscureConfirmPassword;
                          });
                        },
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      filled: true,
                      fillColor: Colors.grey[50],
                    ),
                    textInputAction: TextInputAction.done,
                    onFieldSubmitted: (_) => _handleResetPassword(),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Please confirm your new password';
                      }
                      if (value != _passwordController.text) {
                        return 'Passwords do not match';
                      }
                      return null;
                    },
                  ),
                  
                  const SizedBox(height: 24),
                  
                  ElevatedButton(
                    onPressed: _isLoadingReset ? null : _handleResetPassword,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isLoadingReset
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Text(
                            'Reset Password',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                  ),
                  
                  const SizedBox(height: 12),
                  
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _currentStep = 2;
                        _passwordController.clear();
                        _confirmPasswordController.clear();
                        _resetError = null;
                      });
                    },
                    child: const Text('Back to Code Verification'),
                  ),
                ],
                
                const SizedBox(height: 24),
                
                // Back to Login Link
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Remember your password? ',
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                    TextButton(
                      onPressed: () {
                        Navigator.of(context).pushReplacement(
                          MaterialPageRoute(builder: (_) => const LoginScreen()),
                        );
                      },
                      child: const Text('Back to Login'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

