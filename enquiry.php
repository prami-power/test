<?php
session_start();

// Check if user is logged in. If not, redirect to login page.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Assuming login.php is your login page
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Unknown'); // fallback and sanitize
$date = date('Y-m-d');

// Retrieve any errors or old form data from session after redirect from submit_enquiry.php
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying

$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

// Populate form fields with old data or defaults
$date_value = $old_form_data['date'] ?? $date;
$name_value = $old_form_data['name'] ?? $username;
$vertical_value = $old_form_data['vertical'] ?? '';
$origin_value = $old_form_data['origin'] ?? '';
$customer_name_value = $old_form_data['customer_name'] ?? '';
$phone_value = $old_form_data['phone'] ?? '';
$address_value = $old_form_data['address'] ?? '';
$email_value = $old_form_data['email'] ?? '';
$remarks_value = $old_form_data['remarks'] ?? '';
$visited_date_value = $old_form_data['visited_date'] ?? '';

$checked_nature = $old_form_data['nature'] ?? [];
$checked_products = $old_form_data['products'] ?? [];
$checked_need_by = $old_form_data['need_by'] ?? [];
$terms_checked = isset($old_form_data['terms']);

// Basic CSRF token generation (for demonstration)
// In a real application, this token should be generated and stored securely
// in the session when the form is rendered, and validated on submission.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Enquiry Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
         <link rel="icon" href="src/img/favicon.png" type="image/x-icon">
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
        max-width: 1200px;
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
  <div class="w-full max-w-full md:max-w-3xl mx-auto bg-white p-6 rounded shadow">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <h2 class="text-2xl font-bold">Enquiry Registration</h2>
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

    <form action="submit_enquiry.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      <div>
        <label for="date" class="block font-semibold text-gray-700">Date*</label>
        <input type="date" id="date" name="date" value="<?= htmlspecialchars($date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="name" class="block font-semibold text-gray-700">Name*</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" readonly required>
      </div>

      <div>
        <label for="vertical" class="block font-semibold text-gray-700">Verticals</label>
        <select id="vertical" name="vertical" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
          <option value="">Select</option>
          <?php
          $verticals = ['Builders', 'Architects', 'Residential Apartment', 'Commercial Apartment', 'Villa', 'Petrol Pump', 'Colleges/Schools', 'Dealers', 'Customers', 'Vendors', 'Other'];
          foreach ($verticals as $v) {
              $selected = ($vertical_value === $v) ? 'selected' : '';
              echo "<option value=\"$v\" $selected>$v</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label for="origin" class="block font-semibold text-gray-700">Enquiry Origin*</label>
        <select id="origin" name="origin" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
          <option value="">Select an Origin</option> <?php
          $origins = ['Inbound telecall', 'Outbound telecall', 'Visited', 'PMY List', 'India Mart', 'Reference', 'Other'];
          foreach ($origins as $o) {
              $selected = ($origin_value === $o) ? 'selected' : '';
              echo "<option value=\"$o\" $selected>$o</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label for="customer_name" class="block font-semibold text-gray-700">Company/Customer Name*</label>
        <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customer_name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="phone" class="block font-semibold text-gray-700">Phone No*</label>
        <input type="number" id="phone" name="phone" value="<?= htmlspecialchars($phone_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="address" class="block font-semibold text-gray-700">Customer Address*</label>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($address_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="email" class="block font-semibold text-gray-700">Customer Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="remarks" class="block font-semibold text-gray-700">Remarks/Notes</label>
        <textarea id="remarks" name="remarks" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500 rows="4"><?= htmlspecialchars($remarks_value) ?></textarea>
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Nature* <span class="text-sm text-gray-500">(Purpose of interaction/follow-up)</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <?php
          $nature = ['Send Intro', 'Send Quotation', 'Need Quotation', 'Call/ Call Received', 'Follow Customer', 'Not Attended', 'Collection', 'Meet', 'Other'];
          foreach ($nature as $n) {
              $checked = in_array($n, $checked_nature) ? 'checked' : '';
              echo "<label class='flex items-center text-gray-700'><input type='checkbox' name='nature[]' value='$n' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$n</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Product Interested</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <?php
          $products = ['Net-Metering', 'PMSGY', 'Hybrid', 'Off-Grid', 'Luminous', 'Havells', 'Solar Water Pump', 'Solar Panels', 'Solar Inverter', 'Solar Batteries', 'Regular Batteries', 'Water Heater', 'Lift Backup', 'Petrol Pump Power Backup', 'Other'];
          foreach ($products as $p) {
              $checked = in_array($p, $checked_products) ? 'checked' : '';
              echo "<label class='flex items-center text-gray-700'><input type='checkbox' name='products[]' value='$p' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$p</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label for="visited_date" class="block font-semibold text-gray-700">Visited Date*</label>
        <input type="date" id="visited_date" name="visited_date" value="<?= htmlspecialchars($visited_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Need By*</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
          <?php
          $needBy = ['ASAP', 'Urgent', '1 month', '3 month', '6 month', '1 year'];
          foreach ($needBy as $n) {
              $checked = in_array($n, $checked_need_by) ? 'checked' : '';
              echo "<label class='flex items-center text-gray-700'><input type='checkbox' name='need_by[]' value='$n' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$n</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label class="flex items-center text-gray-700"><input type="checkbox" name="terms" class="form-checkbox h-4 w-4 text-blue-600 mr-2" <?= $terms_checked ? 'checked' : ''; ?> required> I accept the &nbsp; <a href="#" class="text-blue-600 hover:underline">terms and conditions</a>*</label>
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