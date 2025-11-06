import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../models/dashboard_data.dart';

class DashboardProvider with ChangeNotifier {
  bool _isLoading = false;
  String? _errorMessage;
  DashboardData? _dashboardData;

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  DashboardData? get dashboardData => _dashboardData;
  bool get hasData => _dashboardData != null;

  Future<void> loadDashboard() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await ApiService.getDashboard();

      _isLoading = false;

      if (response['success'] == true && response['data'] != null) {
        _dashboardData = DashboardData.fromJson(response['data']);
        _errorMessage = null;
      } else {
        _errorMessage = response['error'] ?? 'Failed to load dashboard';
      }

      notifyListeners();
    } catch (e) {
      _isLoading = false;
      _errorMessage = 'An error occurred: ${e.toString()}';
      notifyListeners();
    }
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}




