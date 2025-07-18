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

// Retrieve any errors or old form data from session after redirect from submit_summary_report.php
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying

$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

// Populate form fields with old data or defaults
$summary_type_value = $old_form_data['summary_type'] ?? '';
$date_value = $old_form_data['date'] ?? $today;
$name_value = $old_form_data['name'] ?? $loggedInUsername;

// Populate dynamic fields with old data
$field_values = [];
$fields = [
    "Total no of call made" => "total_calls",
    "No of call answered" => "answered_calls",
    "No of positive calls" => "positive_calls",
    "No of negative calls" => "negative_calls",
    "No of unanswered calls" => "unanswered_calls",
    "Total no of visit made" => "total_visits",
    "New visit made" => "new_visits",
    "Follow up calls" => "follow_ups",
    "Quotation submitted" => "quotations",
    "Demo shown" => "demos",
    "Interested customer" => "interested_customers",
    "Amount collected" => "amount_collected",
    "Total value" => "total_value",
    "Pending payments" => "pending_payments",
    "Order collected" => "order_collected",
    "System supply due" => "supply_due",
    "System supply done" => "supply_done",
    "Installation done" => "install_done",
    "Service calls received" => "service_received",
    "Service calls closed" => "service_closed"
];

foreach ($fields as $label => $name) {
    $field_values[$name] = $old_form_data[$name] ?? '';
}

$terms_accepted_checkbox = isset($old_form_data['terms_accepted']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Summary Report</title>
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
  <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <h2 class="text-2xl font-bold">Summary Report</h2>
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

    <form action="submit_summary_report.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      <div>
        <label for="summary_type" class="block font-semibold text-gray-700">Summary Report*</label>
        <select id="summary_type" name="summary_type" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
          <option value="">-- Select --</option>
          <?php
          $summaryTypes = ['Daily report', 'Weekly report', 'Monthly report'];
          foreach ($summaryTypes as $type) {
              $selected = ($summary_type_value === $type) ? 'selected' : '';
              echo "<option value=\"$type\" $selected>$type</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label for="date" class="block font-semibold text-gray-700">Date*</label>
        <input type="date" id="date" name="date" value="<?= htmlspecialchars($date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="name" class="block font-semibold text-gray-700">Name (Employee)*</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" readonly required>
      </div>

      <?php
      foreach ($fields as $label => $name) {
        $required = in_array($name, [
          'total_calls', 'answered_calls', 'positive_calls', 'negative_calls',
          'unanswered_calls', 'total_visits', 'new_visits', 'follow_ups', 'quotations',
          'demos', 'interested_customers', 'amount_collected', 'total_value',
          'pending_payments', 'order_collected', 'supply_due', 'supply_done',
          'install_done', 'service_received', 'service_closed'
        ]) ? 'required' : '';
        echo "
          <div>
            <label for='{$name}' class='block font-semibold text-gray-700'>$label" . ($required ? '*' : '') . "</label>
            <input type='number' id='{$name}' name='$name' value='" . htmlspecialchars($field_values[$name]) . "' class='w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500' $required>
          </div>
        ";
      }
      ?>

      <div>
        <label class="flex items-center text-gray-700">
          <input type="checkbox" name="terms_accepted" class="form-checkbox h-4 w-4 text-blue-600 mr-2" <?= $terms_accepted_checkbox ? 'checked' : ''; ?> required> I accept the &nbsp;<a href="#" class="text-blue-600 hover:underline">terms and conditions</a>*
        </label>
      </div>

      <div class="text-right pt-4">
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