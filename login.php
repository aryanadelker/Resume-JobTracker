
<?php
session_start();

require_once "config/db.php";

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password === "") {
        $error = "Please enter your password.";
    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, full_name, email, password_hash,
                    role, account_status
             FROM Users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if ($user['account_status'] !== 'ACTIVE') {
                $error = "Your account is inactive. Please contact the administrator.";

            } elseif (password_verify($password, $user['password_hash'])) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit();

            } else {
                $error = "Incorrect email address or password.";
            }

        } else {
            $error = "Incorrect email address or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card">

        <h1>Welcome Back</h1>

        <p class="subtitle">
            Login to Resume & Job Application Tracker
        </p>

        <?php if ($error !== ""): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="<?= htmlspecialchars($email) ?>"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <button type="submit">Login</button>

        </form>

        <p class="login-link">
            Don't have an account?
            <a href="register.php">Create Account</a>
        </p>

    </div>

</div>

</body>
</html>
