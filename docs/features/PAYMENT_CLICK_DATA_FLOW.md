# Payment Click Data Flow - Complete Breakdown

## 📍 Overview

When you click on a payment row in the Payments page, here's the complete flow of how data is fetched:

---

## 🔄 Complete Data Flow

### **1. Frontend: Click Handler**
**File**: `app/Views/admin/payments.php`

**Location**: Lines 324-334

```javascript
// When a payment row is clicked
$(document).on('click', '.payment-group-row', function(e) {
    // Don't trigger if clicking on buttons
    if ($(e.target).closest('.view-payment-history-btn, .add-payment-btn').length > 0) {
        return;
    }
    
    // Get data from the clicked row
    const payerId = $(this).data('payer-id');
    const contributionId = $(this).data('contribution-id');
    const paymentSequence = $(this).data('payment-sequence') || 1;
    
    // Call the function to fetch payment history
    viewPaymentHistory(payerId, contributionId, paymentSequence);
});
```

**What it does**: 
- Listens for clicks on payment rows (`.payment-group-row`)
- Extracts `payer_id`, `contribution_id`, and `payment_sequence` from data attributes
- Calls `viewPaymentHistory()` function

---

### **2. Frontend: AJAX Request**
**File**: `app/Views/admin/payments.php`

**Location**: Lines 478-499

```javascript
function viewPaymentHistory(payerId, contributionId, paymentSequence = 1) {
    // Make AJAX request to backend
    fetch(`<?= base_url('payments/get-payment-history') ?>?payer_id=${payerId}&contribution_id=${contributionId}&payment_sequence=${paymentSequence}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display the payment history in modal
            displayPaymentHistory(data.payments);
            $('#paymentHistoryModal').modal('show');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching payment history.');
    });
}
```

**What it does**:
- Makes a GET request to `/payments/get-payment-history`
- Sends `payer_id`, `contribution_id`, and `payment_sequence` as query parameters
- Uses `base_url()` helper to generate the full URL
- Handles the response and displays it in a modal

**⚠️ Critical**: The `base_url()` helper uses the `baseURL` from `.env` or `app/Config/App.php`. If this is wrong, the request will fail!

---

### **3. Routing: Route Definition**
**File**: `app/Config/Routes.php`

**Location**: Line 135

```php
$routes->get('/payments/get-payment-history', 'Admin\PaymentsController::getPaymentHistory', ['filter' => 'auth']);
```

**What it does**:
- Maps the URL `/payments/get-payment-history` to the controller method
- Requires authentication (`auth` filter)
- Only accepts GET requests

---

### **4. Controller: Request Handler**
**File**: `app/Controllers/Admin/PaymentsController.php`

**Location**: Lines 69-135

```php
public function getPaymentHistory()
{
    // Validate request
    if (!$this->request->isAJAX() && $this->request->getMethod() !== 'GET') {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }

    // Get parameters from query string
    $payerId = $this->request->getGet('payer_id');
    $contributionId = $this->request->getGet('contribution_id');
    $paymentSequence = $this->request->getGet('payment_sequence') ?? 1;

    // Validate required parameters
    if (!$payerId || !$contributionId) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Payer ID and Contribution ID are required'
        ]);
    }

    try {
        // Call model to fetch data
        $paymentModel = new PaymentModel();
        $payments = $paymentModel->getPaymentsByPayerAndContribution($payerId, $contributionId, $paymentSequence);

        // Add refund information for each payment
        $refundModel = new \App\Models\RefundModel();
        foreach ($payments as &$group) {
            // ... refund processing logic ...
        }

        // Return JSON response
        return $this->response->setJSON([
            'success' => true,
            'payments' => $payments
        ]);

    } catch (\Exception $e) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
```

**What it does**:
- Validates the request (must be AJAX/GET)
- Extracts query parameters (`payer_id`, `contribution_id`, `payment_sequence`)
- Calls the model method to fetch data
- Adds refund information to each payment
- Returns JSON response

---

### **5. Model: Database Query**
**File**: `app/Models/PaymentModel.php`

**Location**: Lines 254-288+

```php
public function getPaymentsByPayerAndContribution($payerId, $contributionId, $paymentSequence = null)
{
    $builder = $this->select('
        payments.*,
        payers.payer_name,
        payers.payer_id as payer_student_id,
        payers.contact_number,
        payers.email_address,
        payers.profile_picture,
        contributions.title as contribution_title,
        contributions.description as contribution_description,
        contributions.amount as contribution_amount,
        contributions.contribution_code,
        users.username as recorded_by_name
    ')
    ->join('payers', 'payers.id = payments.payer_id', 'left')
    ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
    ->join('users', 'users.id = payments.recorded_by', 'left')
    ->where('payments.payer_id', $payerId)
    ->where('payments.contribution_id', $contributionId)
    ->where('payments.deleted_at', null);
    
    // Filter by payment sequence if provided
    if ($paymentSequence !== null) {
        $builder->where('payments.payment_sequence', $paymentSequence);
    }
    
    $payments = $builder->orderBy('payments.payment_sequence', 'ASC')
        ->orderBy('payments.payment_date', 'DESC')
        ->findAll();
    
    // Group payments by sequence
    $groupedPayments = [];
    // ... grouping logic ...
    
    return $groupedPayments;
}
```

**What it does**:
- Builds a SQL query with JOINs to `payers`, `contributions`, and `users` tables
- Filters by `payer_id`, `contribution_id`, and optionally `payment_sequence`
- Excludes soft-deleted payments (`deleted_at IS NULL`)
- Groups payments by sequence
- Returns array of payment data

---

### **6. Frontend: Display Results**
**File**: `app/Views/admin/payments.php`

**Location**: Lines 501+

```javascript
function displayPaymentHistory(paymentGroups) {
    // Renders the payment history in the modal
    // Creates HTML table rows for each payment
    // Shows payment details, dates, amounts, etc.
}
```

**What it does**:
- Takes the JSON response from the server
- Renders HTML to display payment history
- Shows the modal with payment details

---

## 🗂️ Files Involved (Summary)

| File | Purpose | Key Lines |
|------|---------|-----------|
| `app/Views/admin/payments.php` | Frontend click handler & AJAX call | 324-334, 478-499 |
| `app/Config/Routes.php` | Route definition | 135 |
| `app/Controllers/Admin/PaymentsController.php` | Request processing | 69-135 |
| `app/Models/PaymentModel.php` | Database query | 254-288+ |

---

## ⚠️ Common Issues & Where They Occur

### **Issue 1: "An error occurred while fetching payment history"**

**Possible Causes**:
1. **Wrong baseURL** (`.env` or `app/Config/App.php`)
   - ❌ `baseURL = 'https://clearpay.fwh.is/'` (wrong domain)
   - ✅ `baseURL = 'https://clearpay.infinityfreeapp.com/'` (correct domain)
   - **Location**: `app/Views/admin/payments.php` line 479 uses `base_url()`

2. **CORS Error**
   - Browser blocks cross-origin request
   - **Location**: `app/Config/Cors.php` - check `allowedOrigins`

3. **Database Connection Error**
   - Wrong database credentials in `.env`
   - **Location**: `app/Models/PaymentModel.php` line 254+ (database query fails)

4. **Authentication Failure**
   - User not logged in
   - **Location**: `app/Config/Routes.php` line 135 (`auth` filter)

5. **Missing Parameters**
   - `payer_id` or `contribution_id` not passed
   - **Location**: `app/Controllers/Admin/PaymentsController.php` lines 78-86

---

## 🔍 Debugging Steps

1. **Check Browser Console** (F12 → Console tab)
   - Look for JavaScript errors
   - Check Network tab for failed requests

2. **Check Request URL**
   - Open Network tab → Click payment → See actual URL being called
   - Should be: `https://clearpay.infinityfreeapp.com/payments/get-payment-history?...`

3. **Check Server Logs**
   - `writable/logs/log-[date].php`
   - Look for PHP errors or exceptions

4. **Check Database**
   - Verify database connection in `.env`
   - Test query manually in phpMyAdmin

---

## 📝 Key Points

1. **baseURL is critical** - Must match your actual domain
2. **Authentication required** - User must be logged in
3. **AJAX request** - Uses `fetch()` API with JSON response
4. **Database joins** - Fetches data from multiple tables (payments, payers, contributions, users)
5. **Error handling** - Both frontend and backend have try-catch blocks

---

**This is the complete flow from clicking a payment to displaying its history!** ✅

