<?php
// Start session at the very beginning
session_start(); // Start session to use $_SESSION variable

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_management"; // The name of your database

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure that the session always contains the correct user info (role, etc.)
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];

    // Fetch the latest user data from the database to ensure the role is up to date
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user exists, update session role
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user']['role'] = $user['role']; // Update the session with the latest role
    }
}

// Session timeout settings (15 minutes)
$session_timeout = 15 * 60; // 15 minutes

// Check if the session has expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    session_unset(); // Unset session variables
    session_destroy(); // Destroy the session completely
    header('Location: index.php'); // Redirect to login page after session timeout
    exit();
}

// Update the last activity time to prevent timeout
$_SESSION['last_activity'] = time();
?>
