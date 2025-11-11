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
  static String get baseUrl {
    // Production URL for InfinityFree hosting
    const String productionUrl = 'https://clearpay.infinityfreeapp.com';
    
    if (kIsWeb) {
      // For Flutter Web - use production URL or localhost for development
      // Change to 'http://localhost' for local development
      return productionUrl;
      // return 'http://localhost'; // Uncomment for local development
    } else {
      // For mobile platforms - use production URL
      // For local development/testing, change to:
      // - Android Emulator: 'http://10.0.2.2'
      // - iOS Simulator: 'http://localhost'
      // - Physical Device: 'http://YOUR_COMPUTER_IP'
      return productionUrl;
    }
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
      // Use payer/loginPost instead of api/payer/login to avoid InfinityFree security blocking
      // The endpoint detects mobile requests by Accept: application/json header and returns JSON
      final url = Uri.parse('$baseUrl/payer/loginPost');
      
      print('=== LOGIN ATTEMPT ===');
      print('URL: $url');
      print('Base URL: $baseUrl');
      print('Platform: ${kIsWeb ? "Web" : "Mobile"}');
      
      // Make request look like it's from a browser to bypass InfinityFree security
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json, text/plain, */*',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Origin': 'https://clearpay.infinityfreeapp.com',
          'Referer': 'https://clearpay.infinityfreeapp.com/payer/login',
          'Accept-Language': 'en-US,en;q=0.9',
          'Accept-Encoding': 'gzip, deflate, br',
          'Connection': 'keep-alive',
        },
        body: jsonEncode({
          'payer_id': payerId,
          'password': password,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('Connection timeout after 15 seconds. Please check your internet connection and try again.');
        },
      );

      print('Response status: ${response.statusCode}');
      print('Response headers: ${response.headers}');
      final responsePreview = response.body.length > 200 ? response.body.substring(0, 200) : response.body;
      print('Response body preview: $responsePreview');

      if (response.statusCode != 200) {
        // Check if response is HTML (error page)
        final contentType = response.headers['content-type'] ?? '';
        if (contentType.contains('text/html') || response.body.trim().startsWith('<')) {
          return {
            'success': false,
            'error': 'Server returned HTML instead of JSON. The endpoint may be blocked or not accessible.',
          };
        }
        return {
          'success': false,
          'error': 'Server error: ${response.statusCode}. ${response.body.length > 100 ? response.body.substring(0, 100) : response.body}',
        };
      }

      // Check if response is HTML (should be JSON)
      if (response.body.trim().startsWith('<')) {
        return {
          'success': false,
          'error': 'Server returned HTML instead of JSON. The endpoint may be blocked.',
        };
      }

      Map<String, dynamic> data;
      try {
        data = jsonDecode(response.body);
      } catch (e) {
        // If JSON decode fails, it's likely HTML
        return {
          'success': false,
          'error': 'Invalid response format. Server returned: ${response.body.substring(0, 100)}...',
        };
      }
      
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
    } on Exception catch (e) {
      // Check if it's a SocketException (DNS/host lookup failure)
      final errorString = e.toString().toLowerCase();
      if (errorString.contains('socket') || errorString.contains('host lookup') || errorString.contains('failed host lookup')) {
        print('SocketException/DNS error: ${e.toString()}');
        return {
          'success': false,
          'error': 'Cannot connect to server. Please check:\n1. Your internet connection\n2. The server URL is correct\n3. DNS is working\n\nError: ${e.toString()}',
        };
      }
      // Re-throw if not a socket error
      rethrow;
    } on http.ClientException catch (e) {
      print('ClientException: ${e.toString()}');
      return {
        'success': false,
        'error': 'Connection failed. Please ensure:\n1. Your device has internet connection\n2. The server is running\n3. The URL is correct\n\nError: ${e.message}',
      };
    } catch (e) {
      print('General error: ${e.toString()}');
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

  static Future<Map<String, dynamic>> getContributions() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/contributions?payer_id=$userId');
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

  static Future<Map<String, dynamic>> getPaymentHistory() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/payment-history?payer_id=$userId');
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

  static Future<Map<String, dynamic>> getAnnouncements() async {
    try {
      final url = Uri.parse('$baseUrl/api/payer/announcements');
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

  static Future<Map<String, dynamic>> getPaymentRequests() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/payment-requests?payer_id=$userId');
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

  static Future<Map<String, dynamic>> getActivePaymentMethods() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/payment-methods');
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${authToken ?? ''}',
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
      
      // Add form fields (same as web app)
      request.fields['payer_id'] = userId.toString(); // Add payer_id for API endpoint
      request.fields['contribution_id'] = contributionId.toString();
      request.fields['requested_amount'] = requestedAmount.toString();
      request.fields['payment_method'] = paymentMethod;
      request.fields['notes'] = notes ?? '';
      if (paymentSequence != null && paymentSequence.isNotEmpty) {
        request.fields['payment_sequence'] = paymentSequence;
      }
      
      // Add file if provided (web only for now)
      if (proofOfPaymentFile != null && kIsWeb) {
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
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
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
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
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

  static Future<Map<String, dynamic>> updateProfile({
    String? emailAddress,
    String? contactNumber,
  }) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/payer/update-profile');
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
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
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

      final url = Uri.parse('$baseUrl/payer/upload-profile-picture');
      final request = http.MultipartRequest('POST', url);
      request.files.add(await http.MultipartFile.fromPath('profile_picture', filePath));

      final streamedResponse = await request.send().timeout(const Duration(seconds: 30));
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
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

  static Future<Map<String, dynamic>> getAllNotifications() async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/get-all-activities?payer_id=$userId');
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
  // Use payer/forgotPasswordPost instead of api/payer/forgot-password to avoid InfinityFree security blocking
  static Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final url = Uri.parse('$baseUrl/payer/forgotPasswordPost?format=json');
      print('=== FORGOT PASSWORD ===');
      print('URL: $url');
      
      // Make request look like it's from a browser to bypass InfinityFree security
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json, text/plain, */*',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Origin': 'https://clearpay.infinityfreeapp.com',
          'Referer': 'https://clearpay.infinityfreeapp.com/payer/forgotPassword',
          'Accept-Language': 'en-US,en;q=0.9',
          'Accept-Encoding': 'gzip, deflate, br',
          'Connection': 'keep-alive',
        },
        body: jsonEncode({
          'email': email,
        }),
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          throw Exception('Connection timeout. Please check your internet connection.');
        },
      );

      print('Response status: ${response.statusCode}');
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {'success': false, 'error': 'Server error: ${response.statusCode}'};
      }
    } on Exception catch (e) {
      // Check if it's a SocketException (DNS/host lookup failure)
      final errorString = e.toString().toLowerCase();
      if (errorString.contains('socket') || errorString.contains('host lookup') || errorString.contains('failed host lookup')) {
        return {
          'success': false,
          'error': 'Cannot connect to server. Please check your internet connection.\n\nError: ${e.toString()}',
        };
      }
      // Re-throw if not a socket error
      rethrow;
    } catch (e) {
      return {'success': false, 'error': 'Network error: ${e.toString()}'};
    }
  }

  static Future<Map<String, dynamic>> verifyResetCode(String email, String resetCode) async {
    try {
      // Use payer/verifyResetCode instead of api/payer/verify-reset-code to avoid InfinityFree security blocking
      final url = Uri.parse('$baseUrl/payer/verifyResetCode?format=json');
      // Make request look like it's from a browser to bypass InfinityFree security
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json, text/plain, */*',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Origin': 'https://clearpay.infinityfreeapp.com',
          'Referer': 'https://clearpay.infinityfreeapp.com/payer/forgotPassword',
          'Accept-Language': 'en-US,en;q=0.9',
          'Accept-Encoding': 'gzip, deflate, br',
          'Connection': 'keep-alive',
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
      // Use payer/resetPassword instead of api/payer/reset-password to avoid InfinityFree security blocking
      final url = Uri.parse('$baseUrl/payer/resetPassword?format=json');
      // Make request look like it's from a browser to bypass InfinityFree security
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json, text/plain, */*',
          'X-Requested-With': 'XMLHttpRequest',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
          'Origin': 'https://clearpay.infinityfreeapp.com',
          'Referer': 'https://clearpay.infinityfreeapp.com/payer/forgotPassword',
          'Accept-Language': 'en-US,en;q=0.9',
          'Accept-Encoding': 'gzip, deflate, br',
          'Connection': 'keep-alive',
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

  static Future<Map<String, dynamic>> getContributionDetails(int contributionId) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/get-contribution-details?contribution_id=$contributionId&payer_id=$userId');
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

  static Future<Map<String, dynamic>> markNotificationRead(int activityId) async {
    try {
      final userId = await getUserId();
      if (userId == null) {
        return {'success': false, 'error': 'Not authenticated'};
      }

      final url = Uri.parse('$baseUrl/api/payer/mark-activity-read/$activityId');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
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
      
      final headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };

      if (authToken != null) {
        headers['Authorization'] = 'Bearer $authToken';
      }

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

