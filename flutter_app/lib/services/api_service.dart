import 'dart:convert';
import 'dart:async';
import 'dart:typed_data';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
// Conditional import for web-only features
import '../utils/html_stub.dart' if (dart.library.html) 'dart:html' as html show File, FileReader;

class ApiService {
  // Automatically detect platform and set base URL
  // ============================================
  // SERVER CONFIGURATION
  // ============================================
  // Render deployment URL (Development - Active Service)
  static const String productionUrl = 'https://clearpay-web-dev-k3h3.onrender.com';
  
  // Render deployment URL (Production - if you want to use production instead)
  // static const String productionUrl = 'https://clearpay-web.onrender.com';
  
  // Your server PC's IP address (for local network access - development only)
  static const String serverIp = '192.168.18.2';
  
  // Your ClearPay project path in XAMPP (development only)
  static const String projectPath = '/ClearPay/public';
  
  // Expose these for use in UI
  static String get serverIpAddress => serverIp;
  static String get projectPathValue => projectPath;
  
  // Get base URL - always use Render deployment URL
  static Future<String> getBaseUrl() async {
    // Always use production URL (Render deployment)
    return productionUrl;
  }
  
  // Synchronous getter for backward compatibility
  static String get baseUrl {
    // Always use production URL (Render deployment)
    return productionUrl;
  }
  
  static String? authToken;

  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    authToken = prefs.getString('auth_token');
  }

  static Future<void> setAuthToken(String? token) async {
    authToken = token;
    final prefs = await SharedPreferences.getInstance();
    if (token != null) {
      await prefs.setString('auth_token', token);
    } else {
      await prefs.remove('auth_token');
    }
  }

  static Future<void> setUserId(int? userId) async {
    final prefs = await SharedPreferences.getInstance();
    if (userId != null) {
      await prefs.setInt('user_id', userId);
    } else {
      await prefs.remove('user_id');
    }
  }

  static Future<Map<String, dynamic>> login({
    required String payerId,
    required String password,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/login');
      
      print('Attempting login to: $url'); // Debug log
      
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'payer_id': payerId,
          'password': password,
        }),
      ).timeout(
        const Duration(seconds: 10),
        onTimeout: () {
          throw Exception('Connection timeout. Please check your server and network connection.');
        },
      );

      print('Response status: ${response.statusCode}'); // Debug log
      print('Response body: ${response.body}'); // Debug log

      if (response.statusCode != 200) {
        return {
          'success': false,
          'error': 'Server error: ${response.statusCode}',
        };
      }

      final data = jsonDecode(response.body);
      
      if (data['success'] == true) {
        // Store token and user ID
        if (data['token'] != null) {
          await setAuthToken(data['token']);
        }
        if (data['data'] != null && data['data']['id'] != null) {
          // Handle both string and int types for ID
          final id = data['data']['id'];
          final userId = id is int ? id : int.tryParse(id.toString()) ?? 0;
          if (userId > 0) {
            await setUserId(userId);
          }
        }
      }
      
      return data;
    } on http.ClientException catch (e) {
      return {
        'success': false,
        'error': 'Connection failed. Please ensure your backend server is running and the URL is correct. Error: ${e.message}',
      };
    } on TimeoutException {
      return {
        'success': false,
        'error': 'Connection timeout. Please check your server and network connection.',
      };
    } catch (e) {
      return {
        'success': false,
        'error': 'Network error: ${e.toString()}',
      };
    }
  }

  static Future<void> logout() async {
    await setAuthToken(null);
    await setUserId(null);
  }

  // Get user ID from storage
  static Future<int?> getUserId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt('user_id');
  }

  // Helper method to get headers with authentication
  static Map<String, String> _getHeaders({bool includeAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    
    if (includeAuth && authToken != null) {
      headers['Authorization'] = 'Bearer $authToken';
    }
    
    return headers;
  }

  // Dashboard API methods
  static Future<Map<String, dynamic>> getDashboard() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/dashboard?payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getContributions() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/contributions?payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getPaymentHistory() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/payment-history?payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getAnnouncements() async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/announcements');
      final response = await http.get(
        url,
        headers: _getHeaders(includeAuth: false), // Announcements don't require auth
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getPaymentRequests() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/payment-requests?payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getActivePaymentMethods() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/payment-methods');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getPaymentMethodInstructions(String methodName) async {
    try {
      final url = Uri.parse('$baseUrl/admin/settings/payment-methods/instructions/$methodName');
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> submitPaymentRequest({
    required int contributionId,
    required double requestedAmount,
    required String paymentMethod,
    String? notes,
    String? paymentSequence,
    String? proofOfPaymentPath,
    dynamic proofOfPaymentFile, // For web: html.File, for mobile: File
    Uint8List? proofOfPaymentBytes, // For web: file bytes from file_picker
    String? proofOfPaymentFileName, // For web: file name from file_picker
  }) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      // Use API endpoint for mobile/Flutter app
      final url = Uri.parse('$baseUrl/api/payer/submit-payment-request');
      
      // Always use multipart/form-data (same as web app) - even without file
      final request = http.MultipartRequest('POST', url);
      
      // Add headers (same as web app)
      request.headers['X-Requested-With'] = 'XMLHttpRequest';
      request.headers['Accept'] = 'application/json';
      if (authToken != null) {
        request.headers['Authorization'] = 'Bearer $authToken';
      }
      
      // Add form fields (same as web app)
      request.fields['payer_id'] = userId.toString(); // Add payer_id for API endpoint
      request.fields['contribution_id'] = contributionId.toString();
      request.fields['requested_amount'] = requestedAmount.toString();
      request.fields['payment_method'] = paymentMethod;
      request.fields['notes'] = notes ?? '';
      if (paymentSequence != null && paymentSequence.isNotEmpty) {
        request.fields['payment_sequence'] = paymentSequence;
      }
      
      // Add file if provided (web and mobile)
      if (proofOfPaymentBytes != null && proofOfPaymentFileName != null) {
        // Web: Use bytes from file_picker
        final multipartFile = http.MultipartFile.fromBytes(
          'proof_of_payment',
          proofOfPaymentBytes,
          filename: proofOfPaymentFileName,
        );
        request.files.add(multipartFile);
      } else if (proofOfPaymentFile != null) {
        if (kIsWeb) {
          // Web: html.File (legacy)
          final htmlFile = proofOfPaymentFile as html.File;
          final fileName = htmlFile.name;
          
          // Read file as bytes using FileReader
          final completer = Completer<Uint8List>();
          final reader = html.FileReader();
          reader.onLoadEnd.listen((e) {
            if (reader.result != null) {
              try {
                // FileReader.result is an ArrayBuffer when readAsArrayBuffer is used
                final arrayBuffer = reader.result as dynamic;
                completer.complete(Uint8List.view(arrayBuffer));
              } catch (e) {
                completer.completeError('Failed to convert file: $e');
              }
            } else {
              completer.completeError('Failed to read file');
            }
          });
          reader.onError.listen((e) {
            completer.completeError('Failed to read file');
          });
          reader.readAsArrayBuffer(htmlFile);
          
          final fileBytes = await completer.future;
          final multipartFile = http.MultipartFile.fromBytes(
            'proof_of_payment',
            fileBytes,
            filename: fileName,
          );
          request.files.add(multipartFile);
        } else {
          // Mobile: proofOfPaymentFile is a path string
          final filePath = proofOfPaymentFile as String;
          final fileName = filePath.split('/').last;
          // Use path directly - don't create File object to avoid type issues
          final multipartFile = await http.MultipartFile.fromPath(
            'proof_of_payment',
            filePath,
            filename: fileName,
          );
          request.files.add(multipartFile);
        }
      }
      
      // Send request
      final streamedResponse = await request.send().timeout(const Duration(seconds: 30));
      final response = await http.Response.fromStream(streamedResponse);
      
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}', 'body': response.body};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getRefundRequests() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      // Use API endpoint for mobile/Flutter app
      final url = Uri.parse('$baseUrl/api/payer/refund-requests?payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final decoded = jsonDecode(response.body);
        // Backend already returns {success: true, data: {...}}, so return it directly
        if (decoded is Map<String, dynamic> && decoded['success'] == true) {
          return decoded;
        } else {
          // Fallback: wrap if structure is different
          return {'success': true, 'data': decoded};
        }
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getActiveRefundMethods() async {
    try {
      // Use API endpoint for mobile/Flutter app
      final url = Uri.parse('$baseUrl/api/payer/refund-methods');
      final response = await http.get(
        url,
        headers: _getHeaders(includeAuth: false), // Payment/refund methods are public
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> submitRefundRequest({
    required int paymentId,
    required double refundAmount,
    required String refundMethod,
    String? refundReason,
  }) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      // Use API endpoint for mobile/Flutter app
      final url = Uri.parse('$baseUrl/api/payer/submit-refund-request');
      final requestBody = {
        'payer_id': userId,
        'payment_id': paymentId,
        'refund_amount': refundAmount,
        'refund_method': refundMethod,
        'refund_reason': refundReason ?? '',
      };

      final response = await http.post(
        url,
        headers: _getHeaders(),
        body: jsonEncode(requestBody),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getRefundDetails(int refundId) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      // Use API endpoint for mobile/Flutter app
      final url = Uri.parse('$baseUrl/api/payer/refund-details?refund_id=$refundId&payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> updateProfile({
    String? emailAddress,
    String? contactNumber,
  }) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/update-profile');
      final requestBody = <String, dynamic>{
        'payer_id': userId.toString(), // Add payer_id for API requests
      };
      
      // Only include fields that are provided
      if (emailAddress != null) {
        requestBody['email_address'] = emailAddress;
      }
      if (contactNumber != null) {
        requestBody['contact_number'] = contactNumber;
      }

      final response = await http.post(
        url,
        headers: _getHeaders(),
        body: jsonEncode(requestBody),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> uploadProfilePicture(String filePath) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      // Use API endpoint for mobile/Flutter app (same as web)
      final url = Uri.parse('$baseUrl/api/payer/upload-profile-picture');
      final request = http.MultipartRequest('POST', url);
      
      // Add payer_id to form fields
      request.fields['payer_id'] = userId.toString();
      
      // Add file
      request.files.add(await http.MultipartFile.fromPath('profile_picture', filePath));

      // Add headers
      request.headers.addAll({
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      });
      if (authToken != null) {
        request.headers['Authorization'] = 'Bearer $authToken';
      }

      final streamedResponse = await request.send().timeout(const Duration(seconds: 30));
      final response = await http.Response.fromStream(streamedResponse);

      final responseBody = jsonDecode(response.body);
      
      if (response.statusCode == 200) {
        return responseBody;
      } else {
        // Return error message from backend if available
        final errorMessage = responseBody['message'] ?? responseBody['error'] ?? 'Server error: ${response.statusCode}';
        return {'success': false, 'message': errorMessage, 'error': errorMessage};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: ${e.toString()}', 'error': 'Network error: ${e.toString()}'};
    }
  }

  // Upload profile picture for web (using bytes)
  static Future<Map<String, dynamic>> uploadProfilePictureWeb(Uint8List fileBytes, String fileName) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/upload-profile-picture');
      final request = http.MultipartRequest('POST', url);
      
      // Add payer_id to form fields
      request.fields['payer_id'] = userId.toString();
      
      // Add file
      request.files.add(http.MultipartFile.fromBytes(
        'profile_picture',
        fileBytes,
        filename: fileName,
      ));

      // Add headers
      request.headers.addAll({
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      });
      if (authToken != null) {
        request.headers['Authorization'] = 'Bearer $authToken';
      }

      final streamedResponse = await request.send().timeout(const Duration(seconds: 30));
      final response = await http.Response.fromStream(streamedResponse);

      final responseBody = jsonDecode(response.body);
      
      if (response.statusCode == 200) {
        return responseBody;
      } else {
        // Return error message from backend if available
        final errorMessage = responseBody['message'] ?? responseBody['error'] ?? 'Server error: ${response.statusCode}';
        return {'success': false, 'message': errorMessage, 'error': errorMessage};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: ${e.toString()}', 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getNotifications({int? lastShownId}) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/check-new-activities?payer_id=$userId${lastShownId != null ? '&last_shown_id=$lastShownId' : ''}');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getAllNotifications() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/get-all-activities?payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  // Signup API methods
  static Future<Map<String, dynamic>> signup({
    required String payerId,
    required String password,
    required String confirmPassword,
    required String payerName,
    String? emailAddress,
    String? contactNumber,
    String? courseDepartment,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/signup');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'payer_id': payerId,
          'password': password,
          'confirm_password': confirmPassword,
          'payer_name': payerName,
          'email_address': emailAddress ?? '',
          'contact_number': contactNumber ?? '',
          'course_department': courseDepartment ?? '',
        }),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> verifyEmail(String verificationCode, {String? email}) async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/verify-email');
      final body = <String, dynamic>{
        'verification_code': verificationCode,
      };
      if (email != null && email.isNotEmpty) {
        body['email'] = email;
      }
      
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> resendVerificationCode() async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/resend-verification');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  // Forgot Password API methods
  static Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/forgot-password');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
        }),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> verifyResetCode(String email, String resetCode) async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/verify-reset-code');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'reset_code': resetCode,
        }),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> resetPassword({
    required String email,
    required String resetCode,
    required String password,
    required String confirmPassword,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/reset-password');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'reset_code': resetCode,
          'password': password,
          'confirm_password': confirmPassword,
        }),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getContributionPayments(int contributionId, {int? paymentSequence}) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      String url = '$baseUrl/api/payer/get-contribution-payments/$contributionId?payer_id=$userId';
      if (paymentSequence != null) {
        url += '&sequence=$paymentSequence';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> getContributionDetails(int contributionId) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/get-contribution-details?contribution_id=$contributionId&payer_id=$userId');
      final response = await http.get(
        url,
        headers: _getHeaders(),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> markNotificationRead(int activityId) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/mark-activity-read/$activityId');
      final response = await http.post(
        url,
        headers: _getHeaders(),
        body: jsonEncode({
          'payer_id': userId.toString(),
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  // Helper method for authenticated requests
  static Future<Map<String, dynamic>> authenticatedRequest(
    String endpoint, {
    String method = 'GET',
    Map<String, dynamic>? body,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/$endpoint');
      
      final headers = _getHeaders();

      http.Response response;
      
      switch (method.toUpperCase()) {
        case 'POST':
          response = await http.post(
            url,
            headers: headers,
            body: body != null ? jsonEncode(body) : null,
          );
          break;
        case 'PUT':
          response = await http.put(
            url,
            headers: headers,
            body: body != null ? jsonEncode(body) : null,
          );
          break;
        case 'DELETE':
          response = await http.delete(url, headers: headers);
          break;
        default:
          response = await http.get(url, headers: headers);
      }

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'success': false,
          'error': 'Server error: ${response.statusCode}',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'error': 'Network error: ${e.toString()}',
      };
    }
  }
}

