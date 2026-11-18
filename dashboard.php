
<?php
require_once "includes/auth.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Resume & Job Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">
    <div class="register-card">

        <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>

        <p class="subtitle">
            Resume & Job Application Tracker Dashboard
        </p>

        <p><strong>Email:</strong>
            <?= htmlspecialchars($_SESSION['email']) ?>
        </p>

        <p><strong>Role:</strong>
            <?= htmlspecialchars($_SESSION['role']) ?>
        </p>

        <p style="margin-top: 20px;">
            You have successfully logged in.
        </p>

        
<div class="dashboard-links">

    <h2>Dashboard Menu</h2>

    <a href="profile.php">My Profile</a>
    <a href="resume.php">My Resumes</a>
    <a href="skills.php">My Skills</a>
    <a href="jobs.php">Browse Jobs</a>
    <a href="my_applications.php">My Applications</a>
    <a href="application_history.php">Application History</a>
    <a href="interviews.php">My Interviews</a>
    <a href="offers.php">My Job Offers</a>
    <a href="reminders.php">My Reminders</a>
    <a href="notifications.php">My Notifications</a>
    <a href="activity_logs.php">Activity Logs</a>

    <a href="logout.php" class="logout-link">Logout</a>

</div>


    </div>
</div>
<?php include __DIR__ . '/includes/accessibility_widget.php'; ?>
</body>
</html>
