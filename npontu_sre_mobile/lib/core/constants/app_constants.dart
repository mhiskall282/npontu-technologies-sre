// lib/core/constants/app_constants.dart

class AppConstants {
  static const String appName = 'Npontu SRE';
  static const String appTitle = 'Npontu SRE Support Activity Tracker';
  static const String version = '1.0.0';

  // Storage Keys
  static const String authTokenKey = 'npontu_auth_token';
  static const String userDataKey = 'npontu_user_data';
  static const String themeModeKey = 'npontu_theme_mode';
  static const String baseUrlKey = 'npontu_base_url';

  // SRE Priority Tiers
  static const String priorityCritical = 'critical'; // P1
  static const String priorityHigh = 'high'; // P2
  static const String priorityMedium = 'medium'; // P3
  static const String priorityLow = 'low'; // P4

  // Checkoff Statuses
  static const String statusPending = 'pending';
  static const String statusDone = 'done';

  // Shifts
  static const String shiftMorning = 'morning';
  static const String shiftAfternoon = 'afternoon';
  static const String shiftNight = 'night';

  // Conversation Types
  static const String convTypeDirect = 'direct';
  static const String convTypeTeam = 'team';
  static const String convTypeGroup = 'group';
}
