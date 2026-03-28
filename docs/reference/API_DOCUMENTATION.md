# ClearPay API Documentation

Complete documentation of all APIs used in the ClearPay project, including external services and internal REST endpoints.

---

## Table of Contents

1. [External APIs](#external-apis)
   - [Brevo Email API](#brevo-email-api)
   - [Cloudinary Media API](#cloudinary-media-api)
2. [Internal REST API Endpoints](#internal-rest-api-endpoints)
   - [Authentication](#authentication)
   - [Payer Management](#payer-management)
   - [Dashboard & Data](#dashboard--data)
   - [Contributions](#contributions)
   - [Payments](#payments)
   - [Payment Requests](#payment-requests)
   - [Refunds](#refunds)
   - [Announcements](#announcements)
   - [Notifications](#notifications)
   - [Profile Management](#profile-management)
3. [Web Portal Routes](#web-portal-routes)
4. [Response Formats](#response-formats)
5. [Authentication](#authentication-details)
6. [Error Handling](#error-handling)

---

## External APIs

### Brevo Email API

**Purpose:** Send transactional emails (verification codes, password resets, test emails)

**Base URL:** `https://api.brevo.com/v3/smtp/email`

**Authentication:** API Key (Bearer Token)

**Environment Variable:** `BREVO_API_KEY`

**API Key Format:** `xkeysib-...` (API key, different from SMTP key `xsmtpsib-...`)

**Where to Get:** Brevo Dashboard → Settings → SMTP & API → API Keys

**Usage:**
- Email verification during signup
- Password reset codes
- Test emails from admin panel

**Implementation:**
- Service: `app/Services/BrevoEmailService.php`
- Used in: `app/Controllers/Payer/SignupController.php`, `app/Controllers/Admin/EmailSettingsController.php`

**Why Used:**
- Bypasses Render's port blocking (uses HTTPS instead of SMTP ports)
- More reliable on cloud platforms
- Works on Render free tier

**Example Request:**
```php
POST https://api.brevo.com/v3/smtp/email
Headers:
  api-key: xkeysib-...
  Content-Type: application/json

Body:
{
  "sender": {"name": "ClearPay", "email": "project.clearpay@gmail.com"},
  "to": [{"email": "user@example.com"}],
  "subject": "Email Verification - ClearPay",
  "htmlContent": "<html>...</html>",
  "textContent": "Plain text version"
}
```

---

### Cloudinary Media API

**Purpose:** Cloud storage for profile pictures and payment proof images

**Base URL:** `https://api.cloudinary.com/v1_1/{cloud_name}`

**Authentication:** API Key + API Secret

**Environment Variables:**
- `CLOUDINARY_CLOUD_NAME` - Your cloud name (e.g., `dgiycv3x6`)
- `CLOUDINARY_API_KEY` - Your API key
- `CLOUDINARY_API_SECRET` - Your API secret

**Where to Get:** Cloudinary Dashboard → Settings → Security

**Usage:**
- Upload profile pictures
- Store payment proof images
- Automatic image optimization and CDN delivery

**Implementation:**
- Service: `app/Services/CloudinaryService.php`
- Used in: `app/Controllers/Payer/DashboardController.php`

**Why Used:**
- Solves ephemeral filesystem issue on Render
- Files persist across deployments
- Automatic image optimization
- CDN delivery for fast loading

**Operations:**
- **Upload:** `POST /image/upload`
- **Delete:** `DELETE /image/destroy`

**Example Upload:**
```php
POST https://api.cloudinary.com/v1_1/{cloud_name}/image/upload
Body (multipart/form-data):
  file: [binary]
  folder: profile
  public_id: payer_2_1234567890
  overwrite: true
```

**Response:**
```json
{
  "secure_url": "https://res.cloudinary.com/{cloud_name}/image/upload/v1234567890/profile/payer_2_1234567890.jpg",
  "public_id": "profile/payer_2_1234567890",
  "format": "jpg",
  "width": 400,
  "height": 400,
  "bytes": 45678
}
```

---

## Internal REST API Endpoints

**Base URL:** `https://clearpay-web-dev-k3h3.onrender.com` (Production)  
**Base URL:** `http://localhost/ClearPay/public` (Local Development)

All API endpoints are prefixed with `/api/payer/` for mobile/Flutter app access.

**Content-Type:** `application/json`  
**Accept:** `application/json`

---

### Authentication

#### Login

**Endpoint:** `POST /api/payer/login`

**Description:** Authenticate payer and receive access token

**Request Body:**
```json
{
  "payer_id": "student123",
  "password": "password123"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "data": {
    "id": 2,
    "payer_id": "student123",
    "payer_name": "John Doe",
    "email_address": "john@example.com",
    "contact_number": "09123456789",
    "profile_picture": "https://res.cloudinary.com/.../profile.jpg"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "error": "Invalid credentials"
}
```

**CORS:** Supported (OPTIONS preflight available)

---

#### Signup

**Endpoint:** `POST /api/payer/signup`

**Description:** Register a new payer account

**Request Body:**
```json
{
  "payer_id": "student123",
  "password": "password123",
  "confirm_password": "password123",
  "payer_name": "John Doe",
  "email_address": "john@example.com",
  "contact_number": "09123456789",
  "course_department": "Computer Science"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Account created successfully. Please verify your email.",
  "data": {
    "payer_id": "student123",
    "email": "john@example.com"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "error": "A payer with this Student ID already exists"
}
```

---

#### Verify Email

**Endpoint:** `POST /api/payer/verify-email`

**Description:** Verify email address with verification code

**Request Body:**
```json
{
  "verification_code": "123456",
  "email": "john@example.com"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

---

#### Resend Verification Code

**Endpoint:** `POST /api/payer/resend-verification`

**Description:** Resend email verification code

**Request Body:** None (uses session/authenticated user)

**Response:**
```json
{
  "success": true,
  "message": "Verification code sent to your email"
}
```

---

#### Forgot Password

**Endpoint:** `POST /api/payer/forgot-password`

**Description:** Request password reset code

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset code sent to your email"
}
```

---

#### Verify Reset Code

**Endpoint:** `POST /api/payer/verify-reset-code`

**Description:** Verify password reset code

**Request Body:**
```json
{
  "email": "john@example.com",
  "reset_code": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reset code verified"
}
```

---

#### Reset Password

**Endpoint:** `POST /api/payer/reset-password`

**Description:** Reset password with verified code

**Request Body:**
```json
{
  "email": "john@example.com",
  "reset_code": "123456",
  "password": "newpassword123",
  "confirm_password": "newpassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

---

### Dashboard & Data

#### Get Dashboard Data

**Endpoint:** `GET /api/payer/dashboard?payer_id={payer_id}`

**Description:** Get payer dashboard summary

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "payer": {
      "id": 2,
      "payer_id": "student123",
      "payer_name": "John Doe",
      "email_address": "john@example.com",
      "profile_picture": "https://res.cloudinary.com/.../profile.jpg"
    },
    "recent_payments": [...],
    "pending_contributions": [...],
    "total_paid": 5000.00,
    "total_pending": 2000.00
  }
}
```

---

#### Get Contributions

**Endpoint:** `GET /api/payer/contributions?payer_id={payer_id}`

**Description:** Get all contributions for the payer

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "contribution_name": "Tuition Fee",
      "amount": 10000.00,
      "paid_amount": 5000.00,
      "remaining_amount": 5000.00,
      "status": "partial",
      "due_date": "2025-12-31"
    }
  ]
}
```

---

#### Get Contribution Details

**Endpoint:** `GET /api/payer/get-contribution-details?contribution_id={id}&payer_id={payer_id}`

**Description:** Get detailed information about a specific contribution

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "contribution": {...},
    "payments": [...],
    "payment_groups": [...]
  }
}
```

---

#### Get Contribution Payments

**Endpoint:** `GET /api/payer/get-contribution-payments/{contribution_id}?payer_id={payer_id}&sequence={sequence}`

**Description:** Get payments for a specific contribution

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `sequence` (optional): Filter by payment sequence number

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "amount": 2500.00,
      "payment_date": "2025-11-01",
      "payment_method": "GCash",
      "status": "approved"
    }
  ]
}
```

---

### Payments

#### Get Payment History

**Endpoint:** `GET /api/payer/payment-history?payer_id={payer_id}`

**Description:** Get complete payment history

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "contribution_name": "Tuition Fee",
      "amount": 2500.00,
      "payment_date": "2025-11-01",
      "payment_method": "GCash",
      "status": "approved"
    }
  ]
}
```

---

### Payment Requests

#### Get Payment Requests

**Endpoint:** `GET /api/payer/payment-requests?payer_id={payer_id}`

**Description:** Get all payment requests (pending, approved, rejected)

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "contribution_name": "Tuition Fee",
      "requested_amount": 2500.00,
      "payment_method": "GCash",
      "status": "pending",
      "created_at": "2025-11-13 10:00:00"
    }
  ]
}
```

---

#### Get Active Payment Methods

**Endpoint:** `GET /api/payer/payment-methods`

**Description:** Get list of active payment methods

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "method_name": "GCash",
      "instructions": "Send payment to 09123456789",
      "is_active": true
    }
  ]
}
```

---

#### Submit Payment Request

**Endpoint:** `POST /api/payer/submit-payment-request`

**Description:** Submit a new payment request with optional proof of payment

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (multipart/form-data):**
```
payer_id: 2
contribution_id: 1
requested_amount: 2500.00
payment_method: GCash
notes: Payment for first installment
payment_sequence: 1 (optional)
proof_of_payment: [file] (optional)
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Payment request submitted successfully",
  "data": {
    "request_id": 1,
    "status": "pending"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "error": "Invalid contribution ID"
}
```

---

### Refunds

#### Get Refund Requests

**Endpoint:** `GET /api/payer/refund-requests?payer_id={payer_id}`

**Description:** Get all refund requests

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "payment_id": 5,
      "refund_amount": 1000.00,
      "refund_method": "Bank Transfer",
      "status": "pending",
      "created_at": "2025-11-13 10:00:00"
    }
  ]
}
```

---

#### Get Active Refund Methods

**Endpoint:** `GET /api/payer/refund-methods`

**Description:** Get list of active refund methods

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "method_name": "Bank Transfer",
      "is_active": true
    }
  ]
}
```

---

#### Submit Refund Request

**Endpoint:** `POST /api/payer/submit-refund-request`

**Description:** Submit a new refund request

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "payer_id": 2,
  "payment_id": 5,
  "refund_amount": 1000.00,
  "refund_method": "Bank Transfer",
  "refund_reason": "Overpayment"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Refund request submitted successfully"
}
```

---

### Announcements

#### Get Announcements

**Endpoint:** `GET /api/payer/announcements`

**Description:** Get all active announcements (public, no auth required)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Important Notice",
      "content": "Payment deadline extended...",
      "created_at": "2025-11-13 10:00:00",
      "is_active": true
    }
  ]
}
```

---

### Notifications

#### Check New Activities

**Endpoint:** `GET /api/payer/check-new-activities?payer_id={payer_id}&last_shown_id={id}`

**Description:** Get new activities/notifications since last check

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `last_shown_id` (optional): Last activity ID shown to user

**Response:**
```json
{
  "success": true,
  "data": {
    "new_activities": [
      {
        "id": 10,
        "activity_type": "payment_approved",
        "message": "Your payment request has been approved",
        "created_at": "2025-11-13 10:00:00",
        "is_read": false
      }
    ],
    "unread_count": 3
  }
}
```

---

#### Get All Activities

**Endpoint:** `GET /api/payer/get-all-activities?payer_id={payer_id}`

**Description:** Get all activities/notifications

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "activities": [...],
    "unread_count": 3
  }
}
```

---

#### Mark Activity as Read

**Endpoint:** `POST /api/payer/mark-activity-read/{activity_id}`

**Description:** Mark a specific activity as read

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "payer_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Activity marked as read"
}
```

---

### Profile Management

#### Update Profile

**Endpoint:** `POST /api/payer/update-profile`

**Description:** Update payer profile information

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "payer_id": 2,
  "email_address": "newemail@example.com",
  "contact_number": "09123456789"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

---

#### Upload Profile Picture

**Endpoint:** `POST /api/payer/upload-profile-picture`

**Description:** Upload or update profile picture

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (multipart/form-data):**
```
payer_id: 2
profile_picture: [file]
```

**File Requirements:**
- Max size: 2MB
- Formats: JPEG, PNG, GIF
- Recommended: Square images (400x400px)

**Response (Success):**
```json
{
  "success": true,
  "message": "Profile picture uploaded successfully",
  "profile_picture": "https://res.cloudinary.com/.../profile.jpg"
}
```

**Note:** Profile pictures are stored on Cloudinary for persistence across deployments.

---

## Web Portal Routes

The web portal uses traditional form-based routes (not REST API). These are for browser access only.

### Payer Routes

- `GET /payer/login` - Login page
- `POST /payer/loginPost` - Process login
- `GET /payer/signup` - Signup page
- `POST /payer/signupPost` - Process signup
- `GET /payer/dashboard` - Dashboard (requires auth)
- `GET /payer/my-data` - Profile page (requires auth)
- `POST /payer/upload-profile-picture` - Upload profile picture (requires auth)
- `GET /payer/contributions` - Contributions page (requires auth)
- `GET /payer/payment-history` - Payment history (requires auth)
- `GET /payer/payment-requests` - Payment requests (requires auth)
- `POST /payer/submit-payment-request` - Submit payment request (requires auth)
- `GET /payer/refund-requests` - Refund requests (requires auth)
- `POST /payer/submit-refund-request` - Submit refund request (requires auth)
- `GET /payer/announcements` - Announcements (requires auth)
- `GET /payer/logout` - Logout (requires auth)

### Admin Routes

- `GET /` - Admin login
- `GET /dashboard` - Admin dashboard (requires auth)
- `GET /payers` - Manage payers (requires auth)
- `GET /contributions` - Manage contributions (requires auth)
- `GET /payments` - Manage payments (requires auth)
- `GET /payment-requests` - Manage payment requests (requires auth)
- `GET /refunds` - Manage refunds (requires auth)
- `GET /announcements` - Manage announcements (requires auth)
- `GET /settings` - System settings (requires auth)
- `GET /profile` - Admin profile (requires auth)

---

## Response Formats

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response

```json
{
  "success": false,
  "error": "Error message here"
}
```

### Validation Error Response

```json
{
  "success": false,
  "error": "Validation failed: field1 is required, field2 must be valid email"
}
```

---

## Authentication Details

### Token-Based Authentication

Most API endpoints require authentication using a Bearer token.

**How to Get Token:**
1. Call `POST /api/payer/login` with credentials
2. Receive token in response: `{"token": "..."}`
3. Include token in subsequent requests

**How to Use Token:**
```
Authorization: Bearer {token}
```

**Token Storage:**
- Flutter app stores token in `SharedPreferences`
- Token persists across app restarts
- Token is cleared on logout

### Session-Based Authentication (Web)

Web portal uses PHP sessions for authentication:
- Session stored server-side
- Cookie-based (`PHPSESSID`)
- Automatically handled by browser

---

## Error Handling

### HTTP Status Codes

- `200` - Success
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `500` - Internal Server Error

### Common Error Messages

- `"Not authenticated"` - Missing or invalid token
- `"Invalid credentials"` - Wrong payer_id/password
- `"A payer with this Student ID already exists"` - Duplicate payer_id
- `"Server error: {code}"` - Backend error
- `"Network error: {message}"` - Connection/timeout error

### CORS

All API endpoints support CORS for Flutter Web:
- Allowed Origins: `*` (configurable in `app/Config/Cors.php`)
- Allowed Methods: GET, POST, PUT, DELETE, OPTIONS
- Allowed Headers: Content-Type, Accept, Authorization, X-Requested-With

---

## Rate Limiting

Currently, no rate limiting is implemented. Consider implementing for production.

---

## Testing

### Test Endpoints

- `GET /test/cloudinary-status` - Check Cloudinary configuration status
- `GET /health` - Health check endpoint (for Render)

### Postman Collection

You can import these endpoints into Postman for testing:
1. Create new collection: "ClearPay API"
2. Set base URL: `https://clearpay-web-dev-k3h3.onrender.com`
3. Add environment variable: `baseUrl`
4. Import endpoints from this documentation

---

## Changelog

### 2025-11-13
- Added Cloudinary integration for profile picture storage
- Added Brevo API for email sending (bypasses Render port blocking)
- Fixed CORS issues for Flutter Web
- Added comprehensive API documentation

---

## Support

For API issues or questions:
1. Check Render logs: `writable/logs/log-{date}.log`
2. Check browser console (for web)
3. Check Flutter debug console (for mobile)
4. Review error responses for specific error messages

---

**Last Updated:** November 13, 2025  
**API Version:** 1.0  
**Base URL:** `https://clearpay-web-dev-k3h3.onrender.com`

