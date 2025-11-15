import 'package:flutter/material.dart';

/// Helper class for showing toast notifications throughout the app
class ToastHelper {
  /// Show a toast notification at the top of the screen
  /// 
  /// [context] - BuildContext to get overlay
  /// [message] - Message to display
  /// [backgroundColor] - Background color of the toast (default: Colors.green for success)
  /// [duration] - How long to show the toast (default: 3 seconds)
  /// [icon] - Optional icon to show (default: check_circle for green, error for red/orange)
  static void showToast(
    BuildContext context,
    String message, {
    Color? backgroundColor,
    Duration? duration,
    IconData? icon,
  }) {
    // Determine background color and icon if not provided
    final bgColor = backgroundColor ?? Colors.green;
    final toastIcon = icon ?? _getIconForColor(bgColor);
    final toastDuration = duration ?? const Duration(seconds: 3);

    final overlay = Overlay.of(context);
    final overlayEntry = OverlayEntry(
      builder: (context) => Positioned(
        top: MediaQuery.of(context).padding.top + 16,
        left: 16,
        right: 16,
        child: Material(
          color: Colors.transparent,
          child: SafeArea(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(8),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.2),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    toastIcon,
                    color: Colors.white,
                    size: 20,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      message,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                      ),
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );

    overlay.insert(overlayEntry);

    // Remove after duration
    Future.delayed(toastDuration, () {
      overlayEntry.remove();
    });
  }

  /// Get appropriate icon based on background color
  static IconData _getIconForColor(Color color) {
    if (color == Colors.green || color == const Color(0xFF198754)) {
      return Icons.check_circle;
    } else if (color == Colors.red || color == Colors.redAccent) {
      return Icons.error;
    } else if (color == Colors.orange || color == Colors.orangeAccent) {
      return Icons.warning;
    } else {
      return Icons.info;
    }
  }

  /// Show success toast (green)
  static void showSuccess(BuildContext context, String message) {
    showToast(context, message, backgroundColor: Colors.green);
  }

  /// Show error toast (red)
  static void showError(BuildContext context, String message) {
    showToast(context, message, backgroundColor: Colors.red);
  }

  /// Show warning toast (orange)
  static void showWarning(BuildContext context, String message) {
    showToast(context, message, backgroundColor: Colors.orange);
  }

  /// Show info toast (blue)
  static void showInfo(BuildContext context, String message) {
    showToast(context, message, backgroundColor: Colors.blue);
  }
}

