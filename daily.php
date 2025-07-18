<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php'); // Assuming index.php is your login page
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Guest User'); // Use null coalescing for safety
$datetime = date('Y-m-d\TH:i'); // HTML datetime-local format

// Basic CSRF token generation (for demonstration)
// In a real application, this token should be generated and stored securely
// in the session when the form is rendered, and validated on submission.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Form Submission Handling (Conceptual - you'd likely move this to a separate file or a function) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Log this attempt, redirect, or show an error
        die('CSRF token validation failed.');
    }

    // 2. Server-side Validation and Sanitization (Crucial!)
    $errors = [];

    $submitted_datetime = filter_var($_POST['datetime'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $submitted_name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $visit_date = filter_var($_POST['visit_date'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $verticals = filter_var($_POST['verticals'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_name = filter_var($_POST['customer_name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_phone = filter_var($_POST['customer_phone'] ?? '', FILTER_SANITIZE_NUMBER_INT); // Only numbers
    $customer_address = filter_var($_POST['customer_address'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_email = filter_var($_POST['customer_email'] ?? '', FILTER_SANITIZE_EMAIL);
    $remarks = filter_var($_POST['remarks'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Call Type (array of checkboxes)
    $call_types_raw = $_POST['call_type'] ?? [];
    $call_type = [];
    $validCallTypes = ['Tele Call', 'Visited', 'India Mart', 'Enquiry', 'References', 'Other'];
    foreach ($call_types_raw as $type) {
        if (in_array($type, $validCallTypes)) {
            $call_type[] = filter_var($type, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
    }

    // Service (radio button)
    $service = filter_var($_POST['service'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $validServices = ['Service Call Received', 'Service Call Completed'];
    if (!in_array($service, $validServices)) {
        $errors[] = "Invalid service selected.";
        $service = ''; // Clear invalid selection
    }

    // Nature (array of checkboxes)
    $nature_raw = $_POST['nature'] ?? [];
    $nature = [];
    $validNatureOptions = ['Send intro', 'Send quotation', 'Need quotation', 'Call/ Call received', 'Follow customer', 'Not Attended'];
    foreach ($nature_raw as $option) {
        if (in_array($option, $validNatureOptions)) {
            $nature[] = filter_var($option, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
    }

    // Basic required field validation (add more specific validation as needed)
    if (empty($submitted_datetime)) $errors[] = "Date and Time is required.";
    if (empty($submitted_name)) $errors[] = "Name is required.";
    if (empty($visit_date)) $errors[] = "Visit/Enq/Service Date is required.";
    if (empty($verticals)) $errors[] = "Verticals is required.";
    if (empty($call_type)) $errors[] = "At least one Call Type is required.";
    if (empty($service)) $errors[] = "Service is required.";
    if (empty($customer_name)) $errors[] = "Company/Customer Name is required.";
    if (empty($customer_phone)) $errors[] = "Company/Customer Phone No is required.";
    if (empty($customer_address)) $errors[] = "Customer Address is required.";
    if (empty($nature)) $errors[] = "At least one Nature option is required.";
    if (!isset($_POST['terms'])) $errors[] = "You must accept the terms and conditions.";

    // Validate email format if provided
    if (!empty($customer_email) && !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid customer email format.";
    }

    // If no errors, process data (e.g., save to database)
    if (empty($errors)) {
        // --- Database Insertion Logic Here ---
        // Example:
        /*
        $report_data = [
            'datetime' => $submitted_datetime,
            'name' => $submitted_name,
            'visit_date' => $visit_date,
            'verticals' => $verticals,
            'call_type' => implode(', ', $call_type), // Store as comma-separated string or JSON
            'service' => $service,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_address' => $customer_address,
            'customer_email' => $customer_email,
            'remarks' => $remarks,
            'nature' => implode(', ', $nature) // Store as comma-separated string or JSON
        ];
        // Example: PDO connection and insert
        // try {
        //     $db = new PDO("mysql:host=localhost;dbname=your_db", "user", "pass");
        //     $stmt = $db->prepare("INSERT INTO daily_reports (...) VALUES (...)");
        //     $stmt->execute($report_data);
        //     $_SESSION['success_message'] = "Daily report submitted successfully!";
        //     header('Location: dashboard.php'); // Redirect after successful submission
        //     exit;
        // } catch (PDOException $e) {
        //     $errors[] = "Database error: " . $e->getMessage();
        // }
        */
        $_SESSION['success_message'] = "Daily report (conceptually) submitted successfully!";
        header('Location: dashboard.php'); // Redirect after successful submission
        exit;
    } else {
        $_SESSION['form_errors'] = $errors; // Store errors to display to the user
        $_SESSION['form_data'] = $_POST;    // Store submitted data to repopulate form
    }
}

// Retrieve any errors or old form data from session after redirect
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying
$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

$datetime_value = $old_form_data['datetime'] ?? $datetime;
$username_value = $old_form_data['name'] ?? $username;
$visit_date_value = $old_form_data['visit_date'] ?? '';
$verticals_value = $old_form_data['verticals'] ?? '';
$customer_name_value = $old_form_data['customer_name'] ?? '';
$customer_phone_value = $old_form_data['customer_phone'] ?? '';
$customer_address_value = $old_form_data['customer_address'] ?? '';
$customer_email_value = $old_form_data['customer_email'] ?? '';
$remarks_value = $old_form_data['remarks'] ?? '';
$checked_call_types = $old_form_data['call_type'] ?? [];
$checked_service = $old_form_data['service'] ?? '';
$checked_nature = $old_form_data['nature'] ?? [];
$terms_checked = isset($old_form_data['terms']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daily Report</title>
          <link rel="icon" href="src/img/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="src/img/favicon.png" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .form-container {
      max-width: 700px;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <div class="flex-grow flex justify-center p-6">
    <div class="w-full max-w-[700px]">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h2 class="text-2xl font-bold">Daily Report</h2>
        <a href="dashboard.php" class="inline-flex items-center justify-center bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back
        </a>
      </div>

      <?php if (!empty($form_errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
          <strong class="font-bold">Oops!</strong>
          <span class="block sm:inline">Please correct the following errors:</span>
          <ul class="mt-2 list-disc list-inside">
            <?php foreach ($form_errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form id="dailyForm" class="form-container space-y-4 bg-white p-6 rounded shadow" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div>
          <label for="datetime" class="block font-medium text-gray-700">Date and Time*</label>
          <input type="datetime-local" id="datetime" name="datetime" value="<?= htmlspecialchars($datetime_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" readonly required>
        </div>
        <div>
          <label for="name" class="block font-medium text-gray-700">Name*</label>
          <input type="text" id="name" name="name" value="<?= htmlspecialchars($username_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" readonly required>
        </div>
        <div>
          <label for="visit_date" class="block font-medium text-gray-700">Visit/Enq/Service Date*</label>
          <input type="date" id="visit_date" name="visit_date" value="<?= htmlspecialchars($visit_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <div>
          <label for="verticals" class="block font-medium text-gray-700">Verticals*</label>
          <select id="verticals" name="verticals" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
            <option value="">Select</option>
            <?php
            $verticalOptions = ['Dealers', 'Customers', 'Vendors', 'Builders', 'Architects', 'Residential Apartment', 'Commercial Apartment', 'Villa', 'Petrol Pump', 'Colleges/Schools', 'Others'];
            foreach ($verticalOptions as $option) {
              $selected = ($verticals_value === $option) ? 'selected' : '';
              echo "<option value=\"$option\" $selected>$option</option>";
            }
            ?>
          </select>
        </div>
        <div>
          <label class="block font-medium text-gray-700">Call Type*</label>
          <div class="space-y-1">
            <?php
            $callTypes = ['Tele Call', 'Visited', 'India Mart', 'Enquiry', 'References', 'Other'];
            foreach ($callTypes as $type) {
              $checked = in_array($type, $checked_call_types) ? 'checked' : '';
              echo "<label class='inline-flex items-center text-gray-700'><input type='checkbox' name='call_type[]' value='$type' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$type</label><br>";
            }
            ?>
          </div>
        </div>
        <div>
          <label class="block font-medium text-gray-700">Service*</label>
          <div class="space-x-4">
            <label class="inline-flex items-center text-gray-700">
              <input type="radio" name="service" value="Service Call Received" class="form-radio h-4 w-4 text-blue-600" <?= ($checked_service === 'Service Call Received') ? 'checked' : ''; ?> required> Service Call Received
            </label>
            <label class="inline-flex items-center text-gray-700">
              <input type="radio" name="service" value="Service Call Completed" class="form-radio h-4 w-4 text-blue-600" <?= ($checked_service === 'Service Call Completed') ? 'checked' : ''; ?> required> Service Call Completed
            </label>
          </div>
        </div>
        <div>
          <label for="customer_name" class="block font-medium text-gray-700">Company/Customer Name*</label>
          <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customer_name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <div>
          <label for="customer_phone" class="block font-medium text-gray-700">Company/Customer Phone No*</label>
          <input type="number" id="customer_phone" name="customer_phone" value="<?= htmlspecialchars($customer_phone_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <div>
          <label for="customer_address" class="block font-medium text-gray-700">Customer Address*</label>
          <input type="text" id="customer_address" name="customer_address" value="<?= htmlspecialchars($customer_address_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <div>
          <label for="customer_email" class="block font-medium text-gray-700">Customer Email</label>
          <input type="email" id="customer_email" name="customer_email" value="<?= htmlspecialchars($customer_email_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label for="remarks" class="block font-medium text-gray-700">Remarks/Notes</label>
          <textarea id="remarks" name="remarks" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500 rows="4"><?= htmlspecialchars($remarks_value) ?></textarea>
        </div>
        <div>
          <label class="block font-medium text-gray-700">Nature* <span class="text-sm text-gray-500">(Purpose of interaction/follow-up)</span></label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <?php
            $natureOptions = ['Send intro', 'Send quotation', 'Need quotation', 'Call/ Call received', 'Follow customer', 'Not Attended'];
            foreach ($natureOptions as $nature_option) {
              $checked = in_array($nature_option, $checked_nature) ? 'checked' : '';
              echo "<label class='inline-flex items-center text-gray-700'><input type='checkbox' name='nature[]' value='$nature_option' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$nature_option</label>";
            }
            ?>
          </div>
        </div>
        <div>
          <label class="inline-flex items-center text-gray-700">
            <input type="checkbox" name="terms" class="form-checkbox h-4 w-4 text-blue-600 mr-2" <?= $terms_checked ? 'checked' : ''; ?> required> I accept the &nbsp; <a href="#" class="text-blue-600 hover:underline">terms and conditions</a>*
          </label>
        </div>
        <div class="text-right">
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Submit</button>
        </div>
      </form>
    </div>
  </div>

  <footer class="hidden md:block bg-black z-50 mt-auto">
    <div class="px-4 py-2 flex justify-between text-xs text-gray-300 max-w-screen-xl mx-auto">
      <p>&copy; <?= date("Y") ?> PRAMI Power</p>
      <p>v2.1</p>
    </div>
  </footer>
    
<footer class="md:hidden" style="background-color: transparent;">
  <div class="flex justify-between px-4 py-2 text-xs text-gray-300">
    <p>&copy; <?php echo date("Y"); ?> PRAMI Power</p>
    <p>v2.1</p>
  </div>
</footer>
</body>
</html>