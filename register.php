<?php
require_once 'db_config.php';

$registration_error = '';
$registration_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = trim($_POST['emp_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agreed = isset($_POST['terms']);

    if (!$emp_id || !$name || !$email || !$password || !$confirm_password || !$agreed) {
        $registration_error = "All fields are required and terms must be accepted.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $registration_error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            $conn->set_charset("utf8mb4");

            $stmt = $conn->prepare("INSERT INTO employee (emp_id, emp_name, email_id, hashed_password, post) VALUES (?, ?, ?, ?, 'Employee')");
            $stmt->bind_param("ssss", $emp_id, $name, $email, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $registration_error = "Employee ID or Email already exists.";
            }

            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $registration_error = "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
  <link rel="icon" href="src/img/favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="login.css"> <!-- uses same styling -->
</head>
<body>
  <div class="wrapper">
    <form action="register.php" method="post">
      <h1>Register</h1>

      <?php if (!empty($registration_error)): ?>
        <div class="error-message"><?= htmlspecialchars($registration_error) ?></div>
      <?php endif; ?>

      <div class="input-box">
        <label for="emp_id" class="sr-only">Employee ID</label>
        <input type="text" name="emp_id" id="emp_id" placeholder="Employee ID" required value="<?= htmlspecialchars($_POST['emp_id'] ?? '') ?>">
      </div>

      <div class="input-box">
        <label for="name" class="sr-only">Name</label>
        <input type="text" name="name" id="name" placeholder="Full Name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>

      <div class="input-box">
        <label for="email" class="sr-only">Email ID</label>
        <input type="email" name="email" id="email" placeholder="Email ID" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="input-box">
        <label for="password" class="sr-only">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>
      </div>

      <div class="input-box">
        <label for="confirm_password" class="sr-only">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
      </div>
     <br>
      <div class="remember-forgot">
        <label><input type="checkbox" name="terms" required> I agree to the Terms & Conditions</label>
      </div>

      <button type="submit" class="btn">Register</button>
      </p>
    </form>
  </div>
  <script src="register.js"></script>
</body>
</html>
