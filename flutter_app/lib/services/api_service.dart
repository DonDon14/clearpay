import 'dart:convert';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Automatically detect platform and set base URL
  static String get baseUrl {
    if (kIsWeb) {
      // For Flutter Web - use localhost
      // Based on your ClearPay setup, the app runs at http://localhost/
      // NOT http://localhost/ClearPay/public/
      return 'http://localhost';
    } else {
      // For mobile platforms - check platform at runtime
      // We'll use a simple approach: default to Android emulator URL
      // Users can manually change this based on their device
      return 'http://10.0.2.2'; // Android Emulator
      // For iOS Simulator, change to: 'http://localhost'
      // For Physical Device, change to: 'http://YOUR_COMPUTER_IP'
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
        'error': 'Connection failed. Please ensure your backend server is running and the URL is correct.',
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

