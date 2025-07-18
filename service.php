<?php
session_start();

// Check if user is logged in. If not, redirect to login page.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Assuming login.php is your login page
    exit;
}

$today = date('Y-m-d');
$loggedInUsername = htmlspecialchars($_SESSION['username'] ?? 'Unknown'); // Get logged-in username

// Basic CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Retrieve any errors or old form data from session after redirect from submit_service_report.php
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying

$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

// Populate form fields with old data or defaults
$date_value = $old_form_data['date'] ?? $today;
$call_type_value = $old_form_data['call_type'] ?? '';
$customer_name_value = $old_form_data['customer_name'] ?? '';
$customer_mobile_value = $old_form_data['customer_mobile'] ?? '';
$customer_address_value = $old_form_data['customer_address'] ?? '';
$customer_email_value = $old_form_data['customer_email'] ?? '';
$system_configuration_value = $old_form_data['system_configuration'] ?? '';
$nature_of_complaint_value = $old_form_data['nature_of_complaint'] ?? '';
$service_resolution_value = $old_form_data['service_resolution'] ?? '';
$report_by_value = $old_form_data['report_by'] ?? $loggedInUsername; // Default to loggedInUsername

$terms_accepted_checkbox = isset($old_form_data['terms_accepted']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Service Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="src/img/favicon.png" type="src/img/favicon.png">
    <link rel="shortcut icon" href="src/img/favicon.png" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Footer styling for both desktop and mobile */
    .app-footer {
        background-color: #1a1a1a;
        color: #fff;
        padding: 15px 0;
        position: fixed; /* Fixed position */
        bottom: 0;
        width: 100%;
        z-index: 50; /* Ensure it's above other content if needed */
    }
    .app-footer-container {
        /* max-width: 1200px; */ /* REMOVED to make it full width */
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1rem; /* Add horizontal padding */
    }
    /* Add padding to the body or a wrapper div to prevent content from being hidden by the fixed footer */
    body {
      padding-bottom: 70px; /* Adjust based on footer height */
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen px-4 py-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <h2 class="text-2xl font-bold">Service Report</h2>
      <a href="dashboard.php" class="inline-flex items-center justify-center bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </a>
    </div>

    <?php if (!empty($form_errors)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Validation Errors:</strong>
        <ul class="mt-2 list-disc list-inside">
          <?php foreach ($form_errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="submit_service_report.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      <div>
        <label for="date" class="block font-semibold text-gray-700">Date*</label>
        <input type="date" id="date" name="date" value="<?= htmlspecialchars($date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="report_by" class="block font-semibold text-gray-700">Report By*</label>
        <input type="text" id="report_by" name="report_by" value="<?= htmlspecialchars($report_by_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" readonly required>
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Call Type*</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <?php
          $callTypes = ['AMC call', 'AMC visit', 'Breakdown call', 'Breakdown visit', 'Installation', 'Delivery', 'Other'];
          foreach ($callTypes as $type) {
            $checked = ($call_type_value === $type) ? 'checked' : '';
            echo "<label class='flex items-center text-gray-700'><input type='radio' name='call_type' value='$type' class='form-radio h-4 w-4 text-blue-600 mr-2' $checked required>$type</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label for="customer_name" class="block font-semibold text-gray-700">Customer Name*</label>
        <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customer_name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="customer_mobile" class="block font-semibold text-gray-700">Customer Mobile Number*</label>
        <input type="number" id="customer_mobile" name="customer_mobile" value="<?= htmlspecialchars($customer_mobile_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="customer_address" class="block font-semibold text-gray-700">Customer Address*</label>
        <input type="text" id="customer_address" name="customer_address" value="<?= htmlspecialchars($customer_address_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="customer_email" class="block font-semibold text-gray-700">Customer Email</label>
        <input type="email" id="customer_email" name="customer_email" value="<?= htmlspecialchars($customer_email_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="system_configuration" class="block font-semibold text-gray-700">System Configuration</label>
        <input type="text" id="system_configuration" name="system_configuration" value="<?= htmlspecialchars($system_configuration_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="nature_of_complaint" class="block font-semibold text-gray-700">Nature of Complaint</label>
        <input type="text" id="nature_of_complaint" name="nature_of_complaint" value="<?= htmlspecialchars($nature_of_complaint_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="service_resolution" class="block font-semibold text-gray-700">Service Resolution</label>
        <input type="text" id="service_resolution" name="service_resolution" value="<?= htmlspecialchars($service_resolution_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="flex items-center text-gray-700">
          <input type="checkbox" name="terms_accepted" class="form-checkbox h-4 w-4 text-blue-600 mr-2" <?= $terms_accepted_checkbox ? 'checked' : ''; ?> required> I accept the &nbsp; <a href="#" class="text-blue-600 hover:underline">terms and conditions</a>*
        </label>
      </div>

      <div class="text-right">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full sm:w-auto">Submit</button>
      </div>

    </form>
  </div>
  
  <footer class="hidden md:flex fixed bottom-0 left-0 w-full bg-black z-50 text-xs text-gray-300">
  <div class="px-4 py-2 flex justify-between w-full max-w-screen-xl mx-auto">
    <p>&copy; <?= date("Y") ?> PRAMI Power</p>
    <p>v2.1</p>
  </div>
</footer>

<!-- Fixed Mobile Footer -->
<footer class="md:hidden fixed bottom-0 left-0 w-full bg-transparent text-xs text-gray-300 z-50">
  <div class="px-4 py-2 flex justify-between w-full">
    <p>&copy; <?php echo date("Y"); ?> PRAMI Power</p>
    <p>v2.1</p>
  </div>
</footer>

</body>
</html>