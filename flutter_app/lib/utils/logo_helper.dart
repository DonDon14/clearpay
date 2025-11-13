import '../services/api_service.dart';

/// Utility class for logo-related operations
class LogoHelper {
  /// Get the logo URL from the server
  /// Uses ImageController route to ensure CORS headers for Flutter Web
  static String getLogoUrl() {
    // Construct logo URL using the same baseUrl as API service
    // Logo is in public/uploads/logo.png
    // Route goes through ImageController for CORS headers (Flutter Web compatibility)
    final baseUrl = ApiService.baseUrl.replaceAll(RegExp(r'/$'), '');
    // Use ImageController route: /uploads/logo.png (routed through CodeIgniter for CORS)
    return '$baseUrl/uploads/logo.png';
  }
}

