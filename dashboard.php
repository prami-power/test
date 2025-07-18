<?php
session_start();

// Check if user is logged in. If not, redirect to login page.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle logout POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    // Invalidate session
    $_SESSION = array(); // Unset all of the session variables.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy(); // Destroy the session.
    header('Location: login.php'); // Redirect to login page after logout
    exit;
}

// This is where you might set a success message from a previous action (e.g., daily report submission)
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']); // Clear it after display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
        <link rel="icon" href="src/img/favicon.png" type="src/img/favicon.png">
    <link rel="shortcut icon" href="fsrc/img/favicon.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom CSS can be moved to a separate styles.css file for cleaner code */
        .dashboard-card {
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                z-index: 50;
                height: 100vh;
            }
            .sidebar.active {
                left: 0;
            }
        }
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
        .content-wrapper {
            padding-bottom: 70px; /* Adjust based on footer height to prevent content overlap */
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <div class="flex flex-grow">
        <div class="sidebar bg-white w-64 shadow-md p-4 flex-shrink-0 md:static">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center">
                    <img src="https://placehold.co/40x40/FF5733/FFFFFF/png?text=User" alt="User profile picture" class="rounded-full mr-3">
                    <div>
                        <p class="font-semibold"><?= htmlspecialchars($_SESSION['username'] ?? 'Employee'); ?></p>
                        <p class="text-xs text-gray-500">Employee</p>
                    </div>
                </div>
                <button id="mobile-menu-close" class="md:hidden text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <nav>
                <ul>
                    <li class="mb-2">
                        <a href="#" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md font-medium active-nav">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="daily.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-clipboard-list mr-2"></i> Daily Report
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="enquiry.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-question-circle mr-2"></i> Enquiry Registration
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="gst.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-file-invoice-dollar mr-2"></i> GST Report
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="quotation.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-file-signature mr-2"></i> Quotation Report
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="service.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-tools mr-2"></i> Service Report
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="summary.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-chart-pie mr-2"></i> Summary Report
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="task.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-tasks mr-2"></i> Task Report
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="vendors.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-money-bill-wave mr-2"></i> Vendor's Payment
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="customer_copy.php" class="nav-link block px-4 py-2 text-gray-700 hover:bg-blue-50 rounded-md">
                            <i class="fas fa-users mr-2"></i> Customer Copy
                        </a>
                    </li>
                    <li class="mt-8">
                        <form action="" method="POST">
                            <input type="hidden" name="logout" value="1">
                            <button type="submit" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-md font-medium">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
        
        <div class="flex-1 overflow-y-auto content-wrapper">
            <div class="bg-white shadow-sm p-4 flex items-center justify-between md:hidden">
                <button id="mobile-menu-button" class="text-gray-500">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                <div class="w-6"></div> </div>
            
            <div class="p-6">
                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline"><?= htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Employee'); ?>!</h1>
                    <p class="text-gray-600">Manage your daily tasks and reports from this dashboard.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div id="daily-report" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <i class="fas fa-clipboard-list text-blue-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Daily Report</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Submit your daily activities and progress reports.</p>
                        <a href="daily.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Enter Report
                        </a>
                    </div>
                    
                    <div id="enquiry-registration" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-question-circle text-green-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Enquiry Registration</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Register new customer enquiries and track their status.</p>
                        <a href="enquiry.php" class="inline-block bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Register Enquiry
                        </a>
                    </div>
                    
                    <div id="gst-report" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-purple-100 p-3 rounded-full mr-4">
                                <i class="fas fa-file-invoice-dollar text-purple-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">GST Report</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Generate and submit GST reports for tax purposes.</p>
                        <a href="gst.php" class="inline-block bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Generate Report
                        </a>
                    </div>
                    
                    <div id="quotation-report" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-yellow-100 p-3 rounded-full mr-4">
                                <i class="fas fa-file-signature text-yellow-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Quotation Report</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Create and manage customer quotations.</p>
                        <a href="quotation.php" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Create Quotation
                        </a>
                    </div>
                    
                    <div id="service-report" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-red-100 p-3 rounded-full mr-4">
                                <i class="fas fa-tools text-red-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Service Report</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Document service activities and customer interactions.</p>
                        <a href="service.php" class="inline-block bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Log Service
                        </a>
                    </div>
                    
                    <div id="summary-report" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-indigo-100 p-3 rounded-full mr-4">
                                <i class="fas fa-chart-pie text-indigo-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Summary Report</h2>
                        </div>
                        <p class="text-gray-600 mb-4">View summary reports of your activities and performance.</p>
                        <a href="summary.php" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md transition duration-150">
                            View Summary
                        </a>
                    </div>
                    
                    <div id="task-report" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-teal-100 p-3 rounded-full mr-4">
                                <i class="fas fa-tasks text-teal-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Task Report</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Track and report on assigned tasks and their completion.</p>
                        <a href="task.php" class="inline-block bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Manage Tasks
                        </a>
                    </div>
                    
                    <div id="vendors-payment" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-orange-100 p-3 rounded-full mr-4">
                                <i class="fas fa-money-bill-wave text-orange-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Vendor's Payment</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Record and track payments to vendors and suppliers.</p>
                        <a href="vendors.php" class="inline-block bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Record Payment
                        </a>
                    </div>
                    
                    <div id="customer-copy" class="dashboard-card bg-white rounded-lg shadow-md p-6 border-l-4 border-pink-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-pink-100 p-3 rounded-full mr-4">
                                <i class="fas fa-users text-pink-500"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800">Customer Copy</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Generate and manage customer copies of documents.</p>
                        <a href="customer_copy.php" class="inline-block bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-md transition duration-150">
                            Generate Copy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="app-footer">
        <div class="app-footer-container">
            <p class="text-xs">&copy; <?= date("Y"); ?> PRAMI Power</p>
            <p class="text-xs">v2.1</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.add('active');
        });
        
        document.getElementById('mobile-menu-close').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
        });
        
        // Smooth scrolling for navigation links (removed for dashboard links, as they are now pages)
        // This part is modified:
        // Now, sidebar links point to actual PHP pages (e.g., daily_report.php)
        // If you still want smooth scrolling to sections *within* the dashboard page
        // (e.g., if you had hidden sections that appeared), you'd re-implement this
        // for specific hash links, but for navigating to different PHP pages, a direct
        // link is appropriate.

        // Example for showing active nav link (basic, can be improved with URL matching)
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname.split('/').pop(); // Get current page filename
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                // If it's the dashboard itself (index.php or dashboard.php) and the link is # (dashboard link)
                if (currentPath === 'dashboard.php' || currentPath === '' || currentPath === 'index.php') {
                    if (link.getAttribute('href') === '#') {
                        link.classList.add('bg-blue-50', 'font-bold'); // Highlight dashboard link
                    }
                } else if (link.getAttribute('href') === currentPath) {
                    link.classList.add('bg-blue-50', 'font-bold'); // Highlight the active page link
                }
            });
        });
    </script>
</body>
</html>