import '../services/api_service.dart';

/// Utility class for logo-related operations
class LogoHelper {
  /// Get the logo URL from the server
  static String getLogoUrl() {
    // Construct logo URL using the same baseUrl as API service
    // Logo is in public/uploads/logo.png
    final baseUrl = ApiService.baseUrl.replaceAll(RegExp(r'/$'), '');
    return '$baseUrl/uploads/logo.png';
  }
}

