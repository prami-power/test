<?php
session_start();

// Check if user is logged in. If not, redirect to login page.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Assuming login.php is your login page
    exit;
}

$today = date('Y-m-d');
$loggedInUsername = htmlspecialchars($_SESSION['username'] ?? 'Unknown'); // Get logged-in username

// Basic CSRF token generation (for demonstration)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Retrieve any errors or old form data from session after redirect from submit_quotation.php
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying

$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

// Populate form fields with old data or defaults
$name_value = $old_form_data['name'] ?? $loggedInUsername; // New: Populate name field
$enquiry_date_value = $old_form_data['enquiry_date'] ?? $today;
$send_by_date_value = $old_form_data['send_by_date'] ?? '';
$customer_name_value = $old_form_data['customer_name'] ?? '';
$customer_mobile_value = $old_form_data['customer_mobile'] ?? '';
$customer_email_value = $old_form_data['customer_email'] ?? '';
$address_value = $old_form_data['address'] ?? '';
$enquiry_by_value = $old_form_data['enquiry_by'] ?? $loggedInUsername; // Default to loggedInUsername
$product_details_value = $old_form_data['product_details'] ?? '';
$product_amount_value = $old_form_data['product_amount'] ?? '';
$quotation_terms_value = $old_form_data['quotation_terms'] ?? '';

$checked_products_required = $old_form_data['product_requires'] ?? [];
$checked_enquiry_source = $old_form_data['enquiry_source'] ?? '';
$terms_accepted_checkbox = isset($old_form_data['terms_accepted']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Quotation Report</title>
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
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <h2 class="text-2xl font-bold">Quotation Report</h2>
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

    <form action="submit_quotation.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      

      <div>
        <label for="enquiry_date" class="block font-semibold text-gray-700">Enquiry Date*</label>
        <input type="date" id="enquiry_date" name="enquiry_date" value="<?= htmlspecialchars($enquiry_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="send_by_date" class="block font-semibold text-gray-700">Send By Date*</label>
        <input type="date" id="send_by_date" name="send_by_date" value="<?= htmlspecialchars($send_by_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="customer_name" class="block font-semibold text-gray-700">Customer Name*</label>
        <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customer_name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="customer_mobile" class="block font-semibold text-gray-700">Customer Mobile No*</label>
        <input type="number" id="customer_mobile" name="customer_mobile" value="<?= htmlspecialchars($customer_mobile_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="customer_email" class="block font-semibold text-gray-700">Customer Email</label>
        <input type="email" id="customer_email" name="customer_email" value="<?= htmlspecialchars($customer_email_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="address" class="block font-semibold text-gray-700">Address*</label>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($address_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Product Requires*</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <?php
          $products = [
            'Net-Metering', 'PMSGY', 'Hybrid', 'Off-Grid', 'Luminous', 'Havells', 'Solar Water Pump',
            'Solar Panels', 'Solar Inverter', 'Solar Batteries', 'Regular Batteries', 'Water Heater',
            'Lift Backup', 'Petrol Pump Power Backup', 'Other'
          ];
          foreach ($products as $p) {
            $checked = in_array($p, $checked_products_required) ? 'checked' : '';
            echo "<label class='flex items-center text-gray-700'><input type='checkbox' name='product_requires[]' value='$p' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$p</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label for="enquiry_by" class="block font-semibold text-gray-700">Enquiry By*</label>
        <input type="text" id="enquiry_by" name="enquiry_by" value="<?= htmlspecialchars($enquiry_by_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Enquiry Source*</label>
        <div class="space-y-1">
          <?php
          $sources = ['Reference', 'PMY List', 'India mart', 'Visit', 'Dealer', 'Other'];
          foreach ($sources as $src) {
            $checked = ($checked_enquiry_source === $src) ? 'checked' : '';
            echo "<label class='flex items-center text-gray-700'><input type='radio' name='enquiry_source' value='$src' class='form-radio h-4 w-4 text-blue-600 mr-2' $checked required>$src</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label for="product_details" class="block font-semibold text-gray-700">Product Details*</label>
        <input type="text" id="product_details" name="product_details" value="<?= htmlspecialchars($product_details_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="product_amount" class="block font-semibold text-gray-700">Product Amount</label>
        <input type="number" id="product_amount" name="product_amount" value="<?= htmlspecialchars($product_amount_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="quotation_terms" class="block font-semibold text-gray-700">Quotation Terms</label>
        <textarea id="quotation_terms" name="quotation_terms" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" rows="3"><?= htmlspecialchars($quotation_terms_value) ?></textarea>
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