
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

$application_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$application_id || $application_id < 1) {
    die("Invalid application ID.");
}

// Confirm that this application belongs to the logged-in user
$check = $conn->prepare(
    "SELECT a.application_id, j.job_title, c.company_name
     FROM Applications a
     INNER JOIN JobPostings j ON a.job_id = j.job_id
     INNER JOIN Companies c ON j.company_id = c.company_id
     WHERE a.application_id = ? AND a.user_id = ?"
);

$check->bind_param("ii", $application_id, $user_id);
$check->execute();

$application = $check->get_result()->fetch_assoc();
$check->close();

if (!$application) {
    http_response_code(404);
    die("Application not found.");
}

// Fetch status history
$stmt = $conn->prepare(
    "SELECT old_status, new_status, change_date, remarks
     FROM ApplicationHistory
     WHERE application_id = ?
     ORDER BY change_date ASC, history_id ASC"
);

$stmt->bind_param("i", $application_id);
$stmt->execute();
$history = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Application History | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:700px;">

        <h1>Application History</h1>

        <p class="subtitle">
            <?= htmlspecialchars($application['job_title']) ?>
            at <?= htmlspecialchars($application['company_name']) ?>
        </p>

        <h2 style="color:#243b6b; margin-bottom:20px;">
            Status Timeline
        </h2>

        <?php if ($history->num_rows === 0): ?>

            <p>No status history is available yet.</p>

        <?php else: ?>

            <?php while ($record = $history->fetch_assoc()): ?>

                <div style="padding:16px 0;
                            border-bottom:1px solid #ddd;">

                    <h3 style="color:#3156a3;">
                        <?= htmlspecialchars($record['new_status']) ?>
                    </h3>

                    <p>
                        <strong>Previous Status:</strong>
                        <?= htmlspecialchars($record['old_status'] ?? 'Initial application') ?>
                    </p>

                    <p>
                        <strong>Date:</strong>
                        <?= htmlspecialchars($record['change_date']) ?>
                    </p>

                    <p>
                        <strong>Remarks:</strong>
                        <?= htmlspecialchars($record['remarks'] ?? 'No remarks') ?>
                    </p>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

        <p class="login-link">
            <a href="my_applications.php">Back to My Applications</a>
        </p>

    </div>

</div>

</body>
</html>

<?php
$stmt->close();
?>
