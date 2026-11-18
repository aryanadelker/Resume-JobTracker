
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

// Count the logged-in user's activities
$count_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM ActivityLogs
     WHERE user_id = ?"
);

$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();

$total_activities = (int)$count_stmt
    ->get_result()
    ->fetch_assoc()['total'];

$count_stmt->close();

// Fetch the logged-in user's activity history
$stmt = $conn->prepare(
    "SELECT log_id, action, table_affected,
            record_id, action_time, details
     FROM ActivityLogs
     WHERE user_id = ?
     ORDER BY action_time DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$activities = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Activity Logs | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:850px;">

        <h1>Activity Logs</h1>

        <p class="subtitle">
            Review your recent activities in the application
        </p>

        <h2 style="color:#243b6b; margin-bottom:20px;">
            Activity Overview
        </h2>

        <div style="padding:18px; background:#f2f5fc;
                    border-radius:8px; margin-bottom:25px;">

            <strong style="font-size:25px;">
                <?= $total_activities ?>
            </strong>

            <p>Total Recorded Activities</p>

        </div>

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Your Activity History
        </h2>

        <?php if ($activities->num_rows === 0): ?>

            <p>No activities have been recorded for your account yet.</p>

        <?php else: ?>

            <?php while ($activity = $activities->fetch_assoc()): ?>

                <div style="padding:18px;
                            margin-bottom:12px;
                            background:#f7f8fc;
                            border:1px solid #ddd;
                            border-radius:8px;">

                    <h3 style="color:#243b6b;">
                        <?= htmlspecialchars($activity['action']) ?>
                    </h3>

                    <p>
                        <strong>Log ID:</strong>
                        <?= (int)$activity['log_id'] ?>
                    </p>

                    <p>
                        <strong>Table Affected:</strong>
                        <?= htmlspecialchars(
                            $activity['table_affected'] ?? 'Not specified'
                        ) ?>
                    </p>

                    <p>
                        <strong>Record ID:</strong>
                        <?= htmlspecialchars(
                            (string)($activity['record_id'] ?? 'Not specified')
                        ) ?>
                    </p>

                    <p>
                        <strong>Date and Time:</strong>
                        <?= htmlspecialchars($activity['action_time']) ?>
                    </p>

                    <?php if (!empty($activity['details'])): ?>

                        <p>
                            <strong>Details:</strong>
                            <?= nl2br(htmlspecialchars($activity['details'])) ?>
                        </p>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

        <p class="login-link">
            <a href="dashboard.php">Back to Dashboard</a>
        </p>

    </div>

</div>

</body>
</html>

<?php
$stmt->close();
?>
