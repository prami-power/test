<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Assuming login.php is your login page
    exit;
}

$date = date('Y-m-d');

// Basic CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Retrieve any errors or old form data from session after redirect from submit_vendor_payment.php
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying

$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

// Populate form fields with old data or defaults
$date_value = $old_form_data['date'] ?? $date;
$vendor_name_value = $old_form_data['vendor_name'] ?? '';
$selected_1_value = $old_form_data['selected_1'] ?? '';
$selected_2_value = $old_form_data['selected_2'] ?? '';
$collected_from_value = $old_form_data['collected_from'] ?? '';
$selected_company_value = $old_form_data['selected_company'] ?? '';

$checked_payment_modes = $old_form_data['payment_mode'] ?? [];
$terms_accepted_checkbox = isset($old_form_data['terms_accepted']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vendor Payment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="src/img/favicon.png" type="src/img/favicon.png">
    <link rel="shortcut icon" href="src/img/favicon.png" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Add padding to the body or a wrapper div to prevent content from being hidden by the fixed footer */
    body {
      padding-bottom: 70px; /* Adjust based on footer height */
    }

    /* Original footer styles (as provided) - modified for full width on desktop */
    .desktop-footer {
        background-color: #000; /* Use black as per your footer code */
        color: #fff;
        padding: 8px 16px; /* px-4 py-2 */
        position: fixed;
        bottom: 0;
        width: 100%;
        z-index: 50;
    }
    .desktop-footer .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px; /* Keep this for content centering on large screens */
        margin: 0 auto; /* Center the content within the full-width footer */
    }
    .mobile-footer {
        background-color: transparent; /* As per your code */
        color: #fff;
        padding: 8px 16px; /* px-4 py-2 */
        position: fixed;
        bottom: 0;
        width: 100%;
        z-index: 50;
    }
    .mobile-footer .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen px-4 py-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <h2 class="text-2xl font-bold">Vendor Payment</h2>
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

    <form action="submit_vendor_payment.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      <div>
        <label for="date" class="block font-semibold text-gray-700">Date*</label>
        <input type="date" id="date" name="date" value="<?= htmlspecialchars($date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="vendor_name" class="block font-semibold text-gray-700">Vendor Name*</label>
        <input type="text" id="vendor_name" name="vendor_name" value="<?= htmlspecialchars($vendor_name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="selected_1" class="block font-semibold text-gray-700">Payment Type*</label> <select id="selected_1" name="selected_1" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
          <option value="">-- Select Option --</option>
          <?php
          $options1 = ['Payment', 'Invoice from'];
          foreach ($options1 as $opt) {
              $selected = ($selected_1_value === $opt) ? 'selected' : '';
              echo "<option value=\"$opt\" $selected>$opt</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label for="selected_2" class="block font-semibold text-gray-700">Status*</label> <select id="selected_2" name="selected_2" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
          <option value="">-- Select Option --</option>
          <?php
          $options2 = ['Paid', 'Received'];
          foreach ($options2 as $opt) {
              $selected = ($selected_2_value === $opt) ? 'selected' : '';
              echo "<option value=\"$opt\" $selected>$opt</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Mode of Payment*</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <?php
          $modes = ['Cash', 'Cheque', 'UPI', 'Bank transfer', 'Other'];
          foreach ($modes as $mode) {
            $checked = in_array($mode, $checked_payment_modes) ? 'checked' : '';
            echo "<label class='flex items-center text-gray-700'><input type='checkbox' name='payment_mode[]' value='$mode' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$mode</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label for="collected_from" class="block font-semibold text-gray-700">Collected From / Spend For</label>
        <input type="text" id="collected_from" name="collected_from" value="<?= htmlspecialchars($collected_from_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="selected_company" class="block font-semibold text-gray-700">Company*</label> <select id="selected_company" name="selected_company" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
          <option value="">-- Select Company --</option>
          <?php
          $companies = ['PRAMIE power', 'PRAMI enterprises', 'PRAMIE solution'];
          foreach ($companies as $comp) {
              $selected = ($selected_company_value === $comp) ? 'selected' : '';
              echo "<option value=\"$comp\" $selected>$comp</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label class="flex items-center text-gray-700">
          <input type="checkbox" name="terms_accepted" class="form-checkbox h-4 w-4 text-blue-600 mr-2" <?= $terms_accepted_checkbox ? 'checked' : ''; ?> required> I accept the&nbsp; <a href="#" class="text-blue-600 hover:underline">terms and conditions</a>*
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