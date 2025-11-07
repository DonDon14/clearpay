class DashboardData {
  final Map<String, dynamic> payer;
  final double totalPaid;
  final List<dynamic> recentPayments;
  final List<dynamic> announcements;
  final int pendingRequests;
  final int totalPayments;

  DashboardData({
    required this.payer,
    required this.totalPaid,
    required this.recentPayments,
    required this.announcements,
    required this.pendingRequests,
    required this.totalPayments,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    // Safe double parsing
    double _parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) {
        return double.tryParse(value.replaceAll(',', '')) ?? 0.0;
      }
      return 0.0;
    }
    
    return DashboardData(
      payer: json['payer'] ?? {},
      totalPaid: _parseDouble(json['total_paid']),
      recentPayments: json['recent_payments'] ?? [],
      announcements: json['announcements'] ?? [],
      pendingRequests: json['pending_requests'] ?? 0,
      totalPayments: json['total_payments'] ?? 0,
    );
  }
}





