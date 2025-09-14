<?php
session_start();
require_once 'config.php';

if (isset($_POST['register'])) {
    // Collecting Form Data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = trim($_POST['role']);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);

        if ($stmt->execute()) {
            $_SESSION['register_success'] = 'Registration successful!';
        } else {
            $_SESSION['register_error'] = 'Database error: ' . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();

    // Redirect back to signup page
    header("Location: signup.php");
    exit();
}

if (isset($_POST['login'])) {  // submit button
    $email = trim($_POST['email']);       // input field name="email"
    $password = $_POST['password'];       // input field name="password"

    // Finding user by email
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password is correct
        if (password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: dashboard.php"); // redirect to a logged in user only page
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid password!";
        }
    } else {
        $_SESSION['login_error'] = "Email not found!";
    }

    $stmt->close();
    $conn->close();
    header("Location: signinpage.php"); // back to login page
    exit();
}
?>
