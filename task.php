<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Assuming login.php is your login page
    exit;
}

$loggedInUsername = htmlspecialchars($_SESSION['username'] ?? 'Unknown'); // Get logged-in username
$report_date = date('Y-m-d');

// Basic CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Retrieve any errors or old form data from session after redirect from submit_task_report.php
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear them after displaying

$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear them after displaying

// Populate form fields with old data or defaults
$report_date_value = $old_form_data['report_date'] ?? $report_date;
$task_type_value = $old_form_data['task_type'] ?? '';
$assigned_by_value = $old_form_data['assigned_by'] ?? $loggedInUsername; // Default to loggedInUsername
$assigned_to_value = $old_form_data['assigned_to'] ?? '';
$assigned_date_value = $old_form_data['assigned_date'] ?? '';
$task_name_value = $old_form_data['task_name'] ?? '';
$task_description_value = $old_form_data['task_description'] ?? '';
$duration_value = $old_form_data['duration'] ?? '';
$remarks_value = $old_form_data['remarks'] ?? '';
$checked_action = $old_form_data['action'] ?? [];
$terms_accepted_checkbox = isset($old_form_data['terms_accepted']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Task Report</title>
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
      <h2 class="text-2xl font-bold">Task Report</h2>
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

    <form action="submit_task_report.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

      <div>
        <label for="report_date" class="block font-semibold text-gray-700">Report Date*</label>
        <input type="date" id="report_date" name="report_date" value="<?= htmlspecialchars($report_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="task_type" class="block font-semibold text-gray-700">Task Type*</label>
        <select id="task_type" name="task_type" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
          <option value="">-- Select Task Type --</option>
          <?php
          $taskTypes = ['Tele calling', 'Market visit', 'Office work', 'Service call', 'Other'];
          foreach ($taskTypes as $type) {
              $selected = ($task_type_value === $type) ? 'selected' : '';
              echo "<option value=\"$type\" $selected>$type</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label for="assigned_by" class="block font-semibold text-gray-700">Assigned By*</label>
        <input type="text" id="assigned_by" name="assigned_by" value="<?= htmlspecialchars($assigned_by_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" readonly required>
      </div>

      <div>
        <label for="assigned_to" class="block font-semibold text-gray-700">Assigned To*</label>
        <input type="text" id="assigned_to" name="assigned_to" value="<?= htmlspecialchars($assigned_to_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="assigned_date" class="block font-semibold text-gray-700">Assigned Date*</label>
        <input type="date" id="assigned_date" name="assigned_date" value="<?= htmlspecialchars($assigned_date_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="task_name" class="block font-semibold text-gray-700">Task Name*</label>
        <input type="text" id="task_name" name="task_name" value="<?= htmlspecialchars($task_name_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
      </div>

      <div>
        <label for="task_description" class="block font-semibold text-gray-700">Task Description</label>
        <input type="text" id="task_description" name="task_description" value="<?= htmlspecialchars($task_description_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Duration*</label>
        <div class="flex gap-4">
          <?php
          $durations = ['Hours', 'Days'];
          foreach ($durations as $d) {
              $checked = ($duration_value === $d) ? 'checked' : '';
              echo "<label class='flex items-center text-gray-700'><input type='radio' name='duration' value='$d' class='form-radio h-4 w-4 text-blue-600 mr-2' $checked required>$d</label>";
          }
          ?>
        </div>
        </div>

      <div>
        <label for="remarks" class="block font-semibold text-gray-700">Remarks</label>
        <input type="text" id="remarks" name="remarks" value="<?= htmlspecialchars($remarks_value) ?>" class="w-full border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="block font-semibold text-gray-700">Select Action*</label>
        <div class="flex gap-4">
          <?php
          $actions = ['Submit', 'Assign'];
          foreach ($actions as $action) {
              $checked = in_array($action, $checked_action) ? 'checked' : '';
              echo "<label class='flex items-center text-gray-700'><input type='checkbox' name='action[]' value='$action' class='form-checkbox h-4 w-4 text-blue-600 mr-2' $checked required>$action</label>";
          }
          ?>
        </div>
      </div>

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