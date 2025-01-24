<?php

// Include db.php which handles the session start and database connection
include 'db.php';

// If the user is already logged in, redirect them to the dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');  // Redirect to dashboard (both admin and regular user)
    exit();
}

if (isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Check if the username exists in the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables upon successful login
            $_SESSION['user'] = $user;

            // Redirect to the dashboard after successful login
            header('Location: dashboard.php');
            exit();
        } else {
            echo "<script>alert('Invalid credentials!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <form action="index.php" method="POST">
            <h2>Login</h2>
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p>Not registered? <a href="register.php">Create an account</a></p>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
