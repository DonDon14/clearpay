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
    return DashboardData(
      payer: json['payer'] ?? {},
      totalPaid: (json['total_paid'] ?? 0).toDouble(),
      recentPayments: json['recent_payments'] ?? [],
      announcements: json['announcements'] ?? [],
      pendingRequests: json['pending_requests'] ?? 0,
      totalPayments: json['total_payments'] ?? 0,
    );
  }
}




