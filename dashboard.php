
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
    <link rel="stylesheet" href="assets/css/careeraccess-theme.css">
</head>

<body>

<div class="ca-layout">

    <!-- Sidebar -->
    <aside class="ca-sidebar">

        <h2>CareerAccess</h2>

        <nav class="ca-nav">
            <a href="dashboard.php">Dashboard</a>
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
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="ca-main">

        <header class="ca-topbar">
            <h2>Dashboard</h2>
            <span>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
        </header>

        <section class="ca-content">

            <div class="ca-card">

                <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>

                <p>Your Resume & Job Application Tracker</p>

                <p>
                    <strong>Email:</strong>
                    <?= htmlspecialchars($_SESSION['email']) ?>
                </p>

                <p>
                    <strong>Role:</strong>
                    <?= htmlspecialchars($_SESSION['role']) ?>
                </p>

                <p>You have successfully logged in.</p>

            </div>

            <div class="ca-card">

                <h2>Quick Access</h2>

                <div class="dashboard-links">
                    <a class="ca-btn" href="profile.php">My Profile</a>
                    <a class="ca-btn" href="resume.php">My Resumes</a>
                    <a class="ca-btn" href="skills.php">My Skills</a>
                    <a class="ca-btn" href="jobs.php">Browse Jobs</a>
                    <a class="ca-btn" href="my_applications.php">My Applications</a>
                    <a class="ca-btn" href="application_history.php">Application History</a>
                    <a class="ca-btn" href="interviews.php">My Interviews</a>
                    <a class="ca-btn" href="offers.php">My Job Offers</a>
                    <a class="ca-btn" href="reminders.php">My Reminders</a>
                    <a class="ca-btn" href="notifications.php">My Notifications</a>
                    <a class="ca-btn" href="activity_logs.php">Activity Logs</a>
                </div>

            </div>

        </section>

    </main>

</div>

<?php include __DIR__ . '/includes/accessibility_widget.php'; ?>

<script src="assets/js/careeraccess-ui.js"></script>

</body>
</html>
