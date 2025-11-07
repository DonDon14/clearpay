import 'package:flutter/foundation.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  bool _isLoading = false;
  String? _errorMessage;
  Map<String, dynamic>? _user;

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  Map<String, dynamic>? get user => _user;
  bool get isAuthenticated => _user != null;

  Future<bool> login(String payerId, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiService.login(
        payerId: payerId,
        password: password,
      );

      _isLoading = false;

      if (response['success'] == true) {
        // Ensure ID is properly converted to int if it's a string
        final userData = Map<String, dynamic>.from(response['data']);
        if (userData['id'] != null) {
          final id = userData['id'];
          userData['id'] = id is int ? id : int.tryParse(id.toString()) ?? 0;
        }
        _user = userData;
        _errorMessage = null;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response['error'] ?? 'Login failed';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _isLoading = false;
      _errorMessage = 'An error occurred: ${e.toString()}';
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await ApiService.logout();
    _user = null;
    _errorMessage = null;
    notifyListeners();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  void updateUserData(Map<String, dynamic> userData) {
    _user = Map<String, dynamic>.from(userData);
    notifyListeners();
  }
}

