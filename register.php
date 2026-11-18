
<?php
session_start();

require_once "config/db.php";

// Generate a CSRF token for form security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = "";
$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form submission. Please try again.";
    }

    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    // Validate full name
    if ($full_name === "") {
        $errors[] = "Full name is required.";
    } elseif (strlen($full_name) > 100) {
        $errors[] = "Full name must not exceed 100 characters.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } elseif (strlen($email) > 150) {
        $errors[] = "Email must not exceed 150 characters.";
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Insert user if validation succeeds
    if (empty($errors)) {

        $check = $conn->prepare(
            "SELECT user_id FROM Users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "This email address is already registered.";
        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO Users (full_name, email, password_hash)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $full_name,
                $email,
                $password_hash
            );

            if ($stmt->execute()) {
                $success = "Registration successful! Your account has been created.";

                $full_name = "";
                $email = "";

                // Generate a fresh token after successful submission
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $errors[] = "Unable to create your account. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card">

        <h1>Create Account</h1>

        <p class="subtitle">
            Resume & Job Application Tracker
        </p>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <label for="full_name">Full Name</label>

            <input
                type="text"
                id="full_name"
                name="full_name"
                maxlength="100"
                placeholder="Enter your full name"
                value="<?= htmlspecialchars($full_name) ?>"
                required
            >

            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                placeholder="Enter your email"
                value="<?= htmlspecialchars($email) ?>"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                placeholder="At least 8 characters"
                required
            >

            <label for="confirm_password">Confirm Password</label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                placeholder="Re-enter your password"
                required
            >

            <button type="submit">Create Account</button>

        </form>

        <p class="login-link">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</div>

</body>
</html>
