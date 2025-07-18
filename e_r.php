<?php
session_start();
$username = $_SESSION['username'] ?? 'Unknown'; // fallback
$date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Enquiry Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen px-4 py-6">
  <div class="w-full max-w-full md:max-w-3xl mx-auto bg-white p-6 rounded shadow">
    
    <!-- Header with Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <h2 class="text-2xl font-bold">Enquiry Registration</h2>
      <a href="dashboard.php" class="inline-flex items-center justify-center bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </a>
    </div>

    <!-- Form -->
    <form action="submit_enquiry.php" method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold">Date*</label>
        <input type="date" name="date" value="<?= $date ?>" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Name*</label>
        <input type="text" name="name" value="<?= htmlspecialchars($username) ?>" class="w-full border p-2 rounded" readonly required>
      </div>

      <div>
        <label class="block font-semibold">Verticals</label>
        <select name="vertical" class="w-full border p-2 rounded">
          <option value="">Select</option>
          <?php
          $verticals = ['Builders', 'Architects', 'Residential Apartment', 'Commercial Apartment', 'Villa', 'Petrol Pump', 'Colleges/Schools', 'Dealers', 'Customers', 'Vendors', 'Other'];
          foreach ($verticals as $v) echo "<option>$v</option>";
          ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold">Enquiry Origin*</label>
        <select name="origin" class="w-full border p-2 rounded" required>
          <?php
          $origins = ['Inbound telecall', 'Outbound telecall', 'Visited', 'PMY List', 'India Mart', 'Reference', 'Other'];
          foreach ($origins as $o) echo "<option>$o</option>";
          ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold">Company/Customer Name*</label>
        <input type="text" name="customer_name" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Phone No*</label>
        <input type="number" name="phone" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Customer Address*</label>
        <input type="text" name="address" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Customer Email</label>
        <input type="email" name="email" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-semibold">Remarks/Notes</label>
        <textarea name="remarks" class="w-full border p-2 rounded"></textarea>
      </div>

      <div>
        <label class="block font-semibold">Nature*</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <?php
          $nature = ['Send Intro', 'Send Quotation', 'Need Quotation', 'Call/ Call Received', 'Follow Customer', 'Not Attended', 'Collection', 'Meet', 'Other'];
          foreach ($nature as $n) echo "<label class='flex items-center'><input type='checkbox' name='nature[]' value='$n' class='mr-2'>$n</label>";
          ?>
        </div>
      </div>

      <div>
        <label class="block font-semibold">Product Interested</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <?php
          $products = ['Net-Metering', 'PMSGY', 'Hybrid', 'Off-Grid', 'Luminous', 'Havells', 'Solar Water Pump', 'Solar Panels', 'Solar Inverter', 'Solar Batteries', 'Regular Batteries', 'Water Heater', 'Lift Backup', 'Petrol Pump Power Backup', 'Other'];
          foreach ($products as $p) echo "<label class='flex items-center'><input type='checkbox' name='products[]' value='$p' class='mr-2'>$p</label>";
          ?>
        </div>
      </div>

      <div>
        <label class="block font-semibold">Visited Date*</label>
        <input type="date" name="visited_date" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Need By*</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
          <?php
          $needBy = ['ASAP', 'Urgent', '1 month', '3 month', '6 month', '1 year'];
          foreach ($needBy as $n) echo "<label class='flex items-center'><input type='checkbox' name='need_by[]' value='$n' class='mr-2'>$n</label>";
          ?>
        </div>
      </div>

      <div>
        <label class="flex items-center"><input type="checkbox" name="terms" class="mr-2" required> I accept the terms and conditions*</label>
      </div>

      <div class="text-left">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 w-full sm:w-auto">Submit</button>
      </div>
    </form>
  </div>
  <!-- Desktop Footer -->
<footer class="hidden md:block fixed bottom-0 w-full bg-black z-50">
  <div class="px-4 py-2 flex justify-between text-xs text-gray-300 w-full">
    <p>&copy; <?= date("Y") ?> PRAMI Power</p>
    <p>v1.3</p>
  </div>
</footer>

<!-- Mobile Footer -->
<footer class="md:hidden fixed bottom-0 w-full bg-black z-50">
  <div class="px-4 py-2 flex justify-between text-xs text-gray-300 w-full">
    <p>&copy; <?= date("Y") ?> PRAMI Power</p>
    <p>v1.3</p>
  </div>
</footer>

</body>
</html>
