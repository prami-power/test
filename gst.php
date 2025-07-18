<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Assuming login.php is your login page
    exit;
}

$loggedInUsername = htmlspecialchars($_SESSION['username'] ?? 'Unknown'); // Get logged-in username
$today = date('Y-m-d');

// Basic CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Retrieve any errors or old form data from session after redirect from submit_gst.php
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying

$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

// Populate form fields with old data or defaults
$date_value = $old_form_data['date'] ?? $today;
$name_value = $old_form_data['name'] ?? $loggedInUsername;
$amount_without_gst_value = $old_form_data['amount_without_gst'] ?? '';
$gst_amount_value = $old_form_data['gst_amount'] ?? '';
$total_amount_value = $old_form_data['total_amount'] ?? '';
$description_value = $old_form_data['description'] ?? '';
$issued_by_value = $old_form_data['issued_by'] ?? ''; // Assuming this is not always the logged-in user
$customer_name_value = $old_form_data['customer_name'] ?? '';
$customer_date_value = $old_form_data['customer_date'] ?? '';
$gst_date_value = $old_form_data['gst_date'] ?? '';

$checked_accounting_types = $old_form_data['accounting_type'] ?? [];
$terms_accepted_checkbox = isset($old_form_data['terms_accepted']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GST Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" href="src/img/favicon.png" type="src/img/favicon.png">
    <link rel="shortcut icon" href="src/img/favicon.png" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Add padding to the body or a wrapper div to prevent content from being hidden by the fixed footer */
    body {
      padding-bottom: 70px; /* Adjust based on footer height */
    }

    /* Universal Fixed Footer (reusing .app-footer styling from other forms) */
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
  </style>
</head>
<body class="bg-gray-100 min-h-screen px-4 py-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <h2 class="text-2xl font-bold">GST Report</h2>
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

    <form action="submit_gst.php" method="POST" enctype="multipart/form-data" class="space-y-4">
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
        <label class="block font-semibold text-gray-700">Accounting Type*</label>
        <div class="flex flex-col sm:flex-row gap-4">
          <?php
          $accountingTypes = ['Purchase', 'Sales'];
          foreach ($accountingTypes as $type) {
            $checked = in_array($type, $checked_accounting_types) ? 'checked' : '';
            echo "<label class='flex items-center text-gray-700'><input type='checkbox' name='accounting_type[]' value='$type' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked>$type</label>";
          }
          ?>
        </div>
      </div>

      <div>
        <label for="amount_without_gst" class="block font-semibold text-gray-700">Amount Without GST</label>
        <input type="number" id="amount_without_gst" name="amount_without_gst" value="<?= htmlspecialchars($amount_without_gst_value) ?>" step="0.01" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="gst_amount" class="block font-semibold text-gray-700">GST Amount</label>
        <input type="number" id="gst_amount" name="gst_amount" value="<?= htmlspecialchars($gst_amount_value) ?>" step="0.01" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="total_amount" class="block font-semibold text-gray-700">Total Amount</label>
        <input type="number" id="total_amount" name="total_amount" value="<?= htmlspecialchars($total_amount_value) ?>" step="0.01" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="bill_attachment" class="block font-semibold text-gray-700">Attach Bill</label>
        <input type="file" id="bill_attachment" name="bill_attachment" class="w-full border border-gray-300 p-2 rounded-md bg-white focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="description" class="block font-semibold text-gray-700">Description</label>
        <input type="text" id="description" name="description" value="<?= htmlspecialchars($description_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="issued_by" class="block font-semibold text-gray-700">Issued By</label>
        <input type="text" id="issued_by" name="issued_by" value="<?= htmlspecialchars($issued_by_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="customer_name" class="block font-semibold text-gray-700">Customer Name</label>
        <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customer_name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="customer_date" class="block font-semibold text-gray-700">Customer Date</label>
        <input type="date" id="customer_date" name="customer_date" value="<?= htmlspecialchars($customer_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label for="gst_date" class="block font-semibold text-gray-700">GST Date</label>
        <input type="date" id="gst_date" name="gst_date" value="<?= htmlspecialchars($gst_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
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