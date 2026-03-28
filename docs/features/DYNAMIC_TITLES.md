# Dynamic Page Titles in ClearPay

## How to Set Custom Page Titles and Subtitles

The header now supports dynamic page titles and subtitles that can be customized for each page.

### 1. In Your Controller

Pass `pageTitle` and `pageSubtitle` in your data array:

```php
public function index()
{
    $data = [
        'title' => 'Page Title for Browser Tab',          // For <title> tag
        'pageTitle' => 'Your Page Title',                 // For header h1
        'pageSubtitle' => 'Your descriptive subtitle',    // For header subtitle
        'username' => session()->get('username'),
        // ... other data
    ];

    return view('your/view', $data);
}
```

### 2. Examples by Page Type

#### Dashboard Page
```php
$data = [
    'title' => 'Admin Dashboard',
    'pageTitle' => 'Dashboard',
    'pageSubtitle' => 'Welcome back to your ClearPay dashboard',
];
```

#### Payments Page
```php
$data = [
    'title' => 'Payments Management',
    'pageTitle' => 'Payments',
    'pageSubtitle' => 'Manage student payments and transactions',
];
```

#### Analytics Page
```php
$data = [
    'title' => 'Payment Analytics',
    'pageTitle' => 'Analytics & Reports',
    'pageSubtitle' => 'View payment statistics and generate reports',
];
```

#### Student Management
```php
$data = [
    'title' => 'Student Management',
    'pageTitle' => 'Students',
    'pageSubtitle' => 'Manage student information and enrollment',
];
```

#### Settings Page
```php
$data = [
    'title' => 'System Settings',
    'pageTitle' => 'Settings',
    'pageSubtitle' => 'Configure system preferences and options',
];
```

### 3. Default Values

If you don't provide `pageTitle` or `pageSubtitle`, the system will use defaults:
- **Default pageTitle**: "Dashboard"
- **Default pageSubtitle**: "Welcome back to your ClearPay dashboard"

### 4. Dynamic Content in Titles

You can also make titles dynamic based on data:

```php
// For editing a specific student
$data = [
    'pageTitle' => 'Edit Student',
    'pageSubtitle' => 'Editing information for ' . $student['name'],
];

// For viewing payment details
$data = [
    'pageTitle' => 'Payment Details',
    'pageSubtitle' => 'Payment #' . $payment['id'] . ' - ' . $payment['student_name'],
];

// For monthly reports
$data = [
    'pageTitle' => 'Monthly Report',
    'pageSubtitle' => 'Payment report for ' . date('F Y'),
];
```

### 5. Current Available Routes

- `/dashboard` - Dashboard page
- `/payments` - Payments management
- `/payments/history` - Payment history
- `/analytics` - Analytics and reports

### 6. File Structure

The title system works through these files:
- `layouts/main.php` - Main layout that passes variables to header
- `partials/header.php` - Header partial that displays the titles
- `Controllers/Admin/*` - Controllers that set the title data
- `Views/admin/*` - View files that extend the main layout

### 7. Best Practices

1. **Be Descriptive**: Use clear, descriptive titles that help users understand where they are
2. **Consistent Naming**: Follow a consistent pattern for similar pages
3. **Context Awareness**: Include relevant context in subtitles (dates, names, statuses)
4. **Character Limits**: Keep titles concise but informative
5. **User-Friendly**: Use language that your users will understand

### 8. Testing Your Titles

To test different page titles:
1. Create a new controller method
2. Set custom `pageTitle` and `pageSubtitle` in the data
3. Create a corresponding view
4. Add a route in `Routes.php`
5. Access the page to see your custom header

This system makes it easy to provide context-aware navigation and improve user experience throughout your application.