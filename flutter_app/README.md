# ClearPay Payer Mobile App

Flutter mobile application for the ClearPay payment management system.

## Features

- ✅ Login functionality
- ✅ Dashboard with quick actions
- 🚧 Payment history (coming soon)
- 🚧 Payment requests (coming soon)
- 🚧 Announcements (coming soon)
- 🚧 Profile management (coming soon)

## Prerequisites

- Flutter SDK (3.0.0 or higher)
- Dart SDK
- Android Studio / VS Code with Flutter extensions
- Physical device or emulator for testing

## Setup Instructions

1. **Navigate to the Flutter project directory:**
   ```bash
   cd flutter_app
   ```

2. **Install dependencies:**
   ```bash
   flutter pub get
   ```

3. **Configure API Base URL:**
   
   Open `lib/services/api_service.dart` and update the `baseUrl`:
   
   For Android Emulator:
   ```dart
   static String baseUrl = 'http://10.0.2.2/api';
   ```
   
   For iOS Simulator:
   ```dart
   static String baseUrl = 'http://localhost/api';
   ```
   
   For Physical Device (replace with your computer's IP):
   ```dart
   static String baseUrl = 'http://192.168.1.XXX/api'; // Your local IP
   ```

4. **Run the app:**
   ```bash
   flutter run
   ```

## Testing Login

To test the login functionality:

1. Make sure your ClearPay backend is running on XAMPP
2. Ensure you have a payer account in the database
3. Use the Student ID (payer_id) and password to login
4. The app should navigate to the dashboard upon successful login

## Project Structure

```
flutter_app/
├── lib/
│   ├── main.dart                 # App entry point
│   ├── screens/
│   │   ├── login_screen.dart     # Login page
│   │   └── dashboard_screen.dart # Dashboard page
│   ├── providers/
│   │   └── auth_provider.dart    # Authentication state management
│   └── services/
│       └── api_service.dart      # API communication layer
├── pubspec.yaml                  # Dependencies
└── README.md                     # This file
```

## API Endpoints

### Login
- **Endpoint:** `POST /api/payer/login`
- **Request Body:**
  ```json
  {
    "payer_id": "student_id",
    "password": "password"
  }
  ```
- **Success Response:**
  ```json
  {
    "success": true,
    "message": "Login successful",
    "data": {
      "id": 1,
      "payer_id": "154989",
      "payer_name": "John Doe",
      "email": "john@example.com",
      "profile_picture": null,
      "phone_number": ""
    },
    "token": "base64_encoded_token"
  }
  ```
- **Error Response:**
  ```json
  {
    "success": false,
    "error": "Invalid Username or Password"
  }
  ```

## Troubleshooting

### Connection Issues

If you're unable to connect to the backend:

1. **Check your base URL** - Make sure it's correct for your device type
2. **Check XAMPP** - Ensure Apache and MySQL are running
3. **Check firewall** - Make sure your firewall allows connections
4. **Check network** - Ensure your device and computer are on the same network (for physical devices)

### Android Emulator Network

Android emulator uses `10.0.2.2` to access `localhost` on your host machine.

### iOS Simulator Network

iOS simulator can access `localhost` directly.

## Next Steps

- [ ] Implement payment history screen
- [ ] Implement payment requests screen
- [ ] Implement announcements screen
- [ ] Implement profile management
- [ ] Add image uploading for profile pictures
- [ ] Implement forgot password flow
- [ ] Add sign up functionality
- [ ] Implement JWT token authentication
- [ ] Add push notifications

## Notes

- The current implementation uses a simple base64 token for authentication
- For production, implement proper JWT token authentication
- Update the base URL to your production server URL when deploying

