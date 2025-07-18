<?php
session_start();
// Include database configuration
require_once 'db_config.php'; // Make sure this path is correct

class Database {
    private $conn;

    public function __construct() {
        // Suppress default connection error reporting for security, handle it explicitly
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            // Set charset for security
            $this->conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage()); // Log error for debugging
            // Redirect to a generic error page, or display a non-informative error
            header('Location: error.php?code=db_conn_failed'); // Redirect to a generic error page
            exit;
        }
    }

    // Securely verify user using prepared statements and password_verify
    // This method now fetches the 'post' from the database after successful email/password verification
    public function verifyUser($email, $password) {
        // IMPORTANT: 'hashed_password' column should store hashed passwords.
        $stmt = $this->conn->prepare("SELECT emp_id, emp_name, hashed_password, post FROM employee WHERE email_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false; // Indicate failure
        }

        $stmt->bind_param("s", $email); // "s" for one string parameter (email)
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Verify the password against the stored hash
            if (password_verify($password, $row['hashed_password'])) {
                // Password is correct, user authenticated
                $_SESSION['logged_in'] = true;
                $_SESSION['emp_id'] = $row['emp_id'];
                $_SESSION['username'] = $row['emp_name'];
                $_SESSION['post'] = $row['post']; // Store the user's actual post from the database

                // Session fixation prevention: Regenerate session ID
                session_regenerate_id(true);

                // Redirect based on the user's actual post
                switch ($row['post']) {
                    case 'HR':
                        header('Location: hr_dashboard.php');
                        break;
                    case 'Manager':
                        header('Location: manager_dashboard.php');
                        break;
                    case 'Employee':
                        header('Location: dashboard.php'); // Assuming employee_dashboard.php is now dashboard.php
                        break;
                    default:
                        // Handle unexpected post type
                        header('Location: error.php?code=invalid_post_type');
                }
                exit; // Important to exit after header redirect
            } else {
                // Password does not match
                return false;
            }
        } else {
            // No user found with that email
            return false;
        }
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

class UserAuthentication {
    public function handleLogin() {
        // Initialize an empty error message
        $login_error = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Basic input sanitization
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $pwd = $_POST['password'] ?? ''; // Do NOT sanitize password, it needs to be compared as-is (then hashed/verified)

            // Validate inputs
            if (empty($email) || empty($pwd)) {
                $login_error = "Please fill in all fields.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $login_error = "Invalid email format.";
            } else {
                $db = new Database();
                // Verify user and password (no 'post' parameter needed here)
                if (!$db->verifyUser($email, $pwd)) {
                    $login_error = "Invalid email or password."; // More generic error message
                }
                $db->closeConnection();
            }
        }
        return $login_error; // Return error message to display in HTML
    }
}

$auth = new UserAuthentication();
$login_error_message = $auth->handleLogin(); // Get any login error message
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
          <link rel="icon" href="src/img/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="src/img/favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
  <link rel="stylesheet" href="login.css">
  </head>
<body>
  <div class="wrapper">
    <form action="login.php" method="post">
      <h1>Login</h1>

      <?php if (!empty($login_error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($login_error_message) ?></div>
      <?php endif; ?>

      <div class="input-box">
        <label for="email" class="sr-only">Email Id</label> <input type="email" id="email" name="email" placeholder="Email Id" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="input-box password-input-container">
        <label for="password" class="sr-only">Password</label> <input id="password" type="password" name="password" placeholder="Password" required>
        <span class="#" id="togglePassword"></span>
      </div>

      <div class="remember-forgot">
        <label><input type="checkbox" name="remember_me">Remember Me</label>
        <a href="forgot_password.php">Forgot Password?</a> </div>
      <button type="submit" class="btn">Log In</button>
    </form>
  </div>

  <script src="login.js"></script>
</body>
</html>
