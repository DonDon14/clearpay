import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../utils/logo_helper.dart';
import 'login_screen.dart';
import 'email_verification_dialog.dart';

class SignupScreen extends StatefulWidget {
  const SignupScreen({super.key});

  @override
  State<SignupScreen> createState() => _SignupScreenState();
}

class _SignupScreenState extends State<SignupScreen> {
  final _formKey = GlobalKey<FormState>();
  final _payerIdController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _payerNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _contactNumberController = TextEditingController();
  final _courseDepartmentController = TextEditingController();
  
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _payerIdController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _payerNameController.dispose();
    _emailController.dispose();
    _contactNumberController.dispose();
    _courseDepartmentController.dispose();
    super.dispose();
  }

  Future<void> _handleSignup() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await ApiService.signup(
        payerId: _payerIdController.text.trim(),
        password: _passwordController.text,
        confirmPassword: _confirmPasswordController.text,
        payerName: _payerNameController.text.trim(),
        emailAddress: _emailController.text.trim().isNotEmpty 
            ? _emailController.text.trim() 
            : null,
        contactNumber: _contactNumberController.text.trim().isNotEmpty
            ? _contactNumberController.text.trim()
            : null,
        courseDepartment: _courseDepartmentController.text.trim().isNotEmpty
            ? _courseDepartmentController.text.trim()
            : null,
      );

      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });

      if (response['success'] == true) {
        if (response['requires_verification'] == true) {
          // Show email verification dialog
          final email = response['email'] ?? _emailController.text.trim();
          final emailSent = response['email_sent'] ?? true; // Default to true (assume email was sent)
          // Only show test code if email failed to send
          // If email was sent successfully, user should check their email for the code
          final verificationCode = !emailSent 
              ? response['verification_code'] 
              : null;
          
          await showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => EmailVerificationDialog(
              email: email,
              verificationCode: verificationCode,
              emailSent: emailSent,
            ),
          );
        } else {
          // No email verification needed - show success and navigate to login
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(response['message'] ?? 'Account created successfully!'),
                backgroundColor: Colors.green,
                duration: const Duration(seconds: 3),
              ),
            );
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (_) => const LoginScreen()),
            );
          }
        }
      } else {
        setState(() {
          _errorMessage = response['error'] ?? 'Registration failed. Please try again.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = 'An error occurred: ${e.toString()}';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    // Web portal color scheme
    const primaryBlue = Color(0xFF3B82F6);
    const darkGray = Color(0xFF1F2937);
    const mediumGray = Color(0xFF6B7280);
    const lightGray = Color(0xFF9CA3AF);
    
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 32.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Back button
                Align(
                  alignment: Alignment.centerLeft,
                  child: IconButton(
                    icon: const Icon(Icons.arrow_back),
                    color: darkGray,
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                ),
                
                const SizedBox(height: 20),
                
                // Branding - ClearPay Logo (center-aligned, matching login screen)
                Center(
                  child: Image.network(
                    LogoHelper.getLogoUrl(),
                    width: 80,
                    height: 80,
                    fit: BoxFit.contain,
                    errorBuilder: (context, error, stackTrace) {
                      // Fallback to text if image fails to load
                      return const Text(
                        'ClearPay',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 32,
                          fontWeight: FontWeight.w700,
                          color: primaryBlue,
                        ),
                      );
                    },
                    loadingBuilder: (context, child, loadingProgress) {
                      if (loadingProgress == null) return child;
                      return const SizedBox(
                        width: 80,
                        height: 80,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(primaryBlue),
                        ),
                      );
                    },
                  ),
                ),
                
                const SizedBox(height: 16),
                
                // Branding - ClearPay Text (center-aligned)
                const Text(
                  'ClearPay',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.w700,
                    color: primaryBlue,
                  ),
                ),
                
                const SizedBox(height: 24),
                
                // Headline - Create Account (center-aligned)
                const Text(
                  'Create Account',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 30,
                    fontWeight: FontWeight.w700,
                    color: darkGray,
                    height: 1.3,
                  ),
                ),
                
                const SizedBox(height: 12),
                
                // Sub-headline (center-aligned)
                const Text(
                  'Sign up to access your payment information',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 15,
                    color: mediumGray,
                  ),
                ),
                
                const SizedBox(height: 32),
                
                // Error message (matching web portal alert box)
                if (_errorMessage != null)
                  Container(
                    margin: const EdgeInsets.only(bottom: 24),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFFECACA)),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.error_outline,
                          color: Color(0xFFDC2626),
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            _errorMessage!,
                            style: const TextStyle(
                              color: Color(0xFFDC2626),
                              fontSize: 14,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                
                // Student ID / Username Field (matching web portal)
                TextFormField(
                  controller: _payerIdController,
                  decoration: InputDecoration(
                    labelText: 'Student ID / Username *',
                    hintText: 'Enter your Student ID',
                    prefixIcon: const Icon(Icons.credit_card, color: lightGray),
                    helperText: 'This will be your username for login',
                    helperStyle: TextStyle(color: mediumGray, fontSize: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: primaryBlue, width: 1),
                    ),
                    errorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red),
                    ),
                    focusedErrorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red, width: 1),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF374151),
                    ),
                  ),
                  keyboardType: TextInputType.text,
                  textInputAction: TextInputAction.next,
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Please enter your Student ID';
                    }
                    if (value.trim().length < 3) {
                      return 'Student ID must be at least 3 characters';
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: 24),
                
                // Password Field (matching web portal)
                TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  decoration: InputDecoration(
                    labelText: 'Password *',
                    hintText: 'Enter your password',
                    prefixIcon: const Icon(Icons.lock, color: lightGray),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword ? Icons.visibility : Icons.visibility_off,
                        color: lightGray,
                      ),
                      onPressed: () {
                        setState(() {
                          _obscurePassword = !_obscurePassword;
                        });
                      },
                    ),
                    helperText: 'Minimum 6 characters',
                    helperStyle: TextStyle(color: mediumGray, fontSize: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: primaryBlue, width: 1),
                    ),
                    errorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red),
                    ),
                    focusedErrorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red, width: 1),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF374151),
                    ),
                  ),
                  textInputAction: TextInputAction.next,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Please enter your password';
                    }
                    if (value.length < 6) {
                      return 'Password must be at least 6 characters';
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: 24),
                
                // Confirm Password Field (matching web portal)
                TextFormField(
                  controller: _confirmPasswordController,
                  obscureText: _obscureConfirmPassword,
                  decoration: InputDecoration(
                    labelText: 'Confirm Password *',
                    hintText: 'Confirm your password',
                    prefixIcon: const Icon(Icons.lock, color: lightGray),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscureConfirmPassword ? Icons.visibility : Icons.visibility_off,
                        color: lightGray,
                      ),
                      onPressed: () {
                        setState(() {
                          _obscureConfirmPassword = !_obscureConfirmPassword;
                        });
                      },
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: primaryBlue, width: 1),
                    ),
                    errorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red),
                    ),
                    focusedErrorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red, width: 1),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF374151),
                    ),
                  ),
                  textInputAction: TextInputAction.next,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Please confirm your password';
                    }
                    if (value != _passwordController.text) {
                      return 'Passwords do not match';
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: 24),
                
                // Full Name Field (matching web portal)
                TextFormField(
                  controller: _payerNameController,
                  decoration: InputDecoration(
                    labelText: 'Full Name *',
                    hintText: 'Enter your full name',
                    prefixIcon: const Icon(Icons.person, color: lightGray),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: primaryBlue, width: 1),
                    ),
                    errorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red),
                    ),
                    focusedErrorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red, width: 1),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF374151),
                    ),
                  ),
                  keyboardType: TextInputType.name,
                  textInputAction: TextInputAction.next,
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Please enter your full name';
                    }
                    if (value.trim().length < 3) {
                      return 'Name must be at least 3 characters';
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: 24),
                
                // Email Field (Optional) - matching web portal
                TextFormField(
                  controller: _emailController,
                  decoration: InputDecoration(
                    labelText: 'Email Address',
                    hintText: 'Enter your email address (optional)',
                    prefixIcon: const Icon(Icons.email, color: lightGray),
                    helperText: 'Recommended: Having an email helps you receive important updates and notifications',
                    helperStyle: TextStyle(color: Colors.orange[700], fontSize: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: primaryBlue, width: 1),
                    ),
                    errorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red),
                    ),
                    focusedErrorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red, width: 1),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF374151),
                    ),
                  ),
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  validator: (value) {
                    if (value != null && value.trim().isNotEmpty) {
                      final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
                      if (!emailRegex.hasMatch(value.trim())) {
                        return 'Please enter a valid email address';
                      }
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: 24),
                
                // Contact Number Field (Optional) - matching web portal
                TextFormField(
                  controller: _contactNumberController,
                  decoration: InputDecoration(
                    labelText: 'Contact Number',
                    hintText: '09123456789',
                    prefixIcon: const Icon(Icons.phone, color: lightGray),
                    helperText: 'Must be exactly 11 digits (numbers only)',
                    helperStyle: TextStyle(color: mediumGray, fontSize: 12),
                    counterText: '',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: primaryBlue, width: 1),
                    ),
                    errorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red),
                    ),
                    focusedErrorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red, width: 1),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF374151),
                    ),
                  ),
                  keyboardType: TextInputType.phone,
                  textInputAction: TextInputAction.next,
                  maxLength: 11,
                  validator: (value) {
                    if (value != null && value.trim().isNotEmpty) {
                      final phoneRegex = RegExp(r'^\d{11}$');
                      if (!phoneRegex.hasMatch(value.trim())) {
                        return 'Contact number must be exactly 11 digits';
                      }
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: 24),
                
                // Course/Department Field (Optional) - matching web portal
                TextFormField(
                  controller: _courseDepartmentController,
                  decoration: InputDecoration(
                    labelText: 'Course/Department',
                    hintText: 'e.g., BS Computer Science, IT Department',
                    prefixIcon: const Icon(Icons.school, color: lightGray),
                    helperText: 'Your course or department name',
                    helperStyle: TextStyle(color: mediumGray, fontSize: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: primaryBlue, width: 1),
                    ),
                    errorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red),
                    ),
                    focusedErrorBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: Colors.red, width: 1),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF374151),
                    ),
                  ),
                  keyboardType: TextInputType.text,
                  textInputAction: TextInputAction.done,
                  onFieldSubmitted: (_) => _handleSignup(),
                ),
                
                const SizedBox(height: 24),
                
                // Create Account Button (matching web portal with icon)
                ElevatedButton(
                  onPressed: _isLoading ? null : _handleSignup,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryBlue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    elevation: 0,
                    disabledBackgroundColor: primaryBlue.withOpacity(0.6),
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
                      : Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: const [
                            Icon(Icons.person_add, size: 18),
                            SizedBox(width: 8),
                            Text(
                              'Create Account',
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                ),
                
                const SizedBox(height: 24),
                
                // Login Link (matching web portal with divider)
                Container(
                  padding: const EdgeInsets.only(top: 24),
                  decoration: const BoxDecoration(
                    border: Border(
                      top: BorderSide(color: Color(0xFFE5E7EB), width: 1),
                    ),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Already have an account? ',
                        style: TextStyle(
                          fontSize: 14,
                          color: mediumGray,
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          Navigator.of(context).pushReplacement(
                            MaterialPageRoute(builder: (_) => const LoginScreen()),
                          );
                        },
                        style: TextButton.styleFrom(
                          padding: EdgeInsets.zero,
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: const Text(
                          'Login Here',
                          style: TextStyle(
                            fontSize: 14,
                            color: primaryBlue,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

