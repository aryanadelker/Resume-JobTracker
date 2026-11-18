
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT
        a.application_id,
        a.job_id,
        a.date_applied,
        a.application_status,
        a.expected_salary,
        a.application_notes,
        j.job_title,
        j.job_location,
        j.job_type,
        c.company_name
     FROM Applications a
     INNER JOIN JobPostings j
        ON a.job_id = j.job_id
     INNER JOIN Companies c
        ON j.company_id = c.company_id
     WHERE a.user_id = ?
     ORDER BY a.date_applied DESC, a.application_id DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result();

function statusColor($status) {
    switch ($status) {
        case 'ACCEPTED':
        case 'OFFER':
            return '#16803c';

        case 'REJECTED':
        case 'WITHDRAWN':
            return '#b42318';

        case 'INTERVIEW':
        case 'ASSESSMENT':
            return '#a15c00';

        default:
            return '#3156a3';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Applications | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:850px;">

        <h1>My Applications</h1>

        <p class="subtitle">
            Track and manage your job applications
        </p>

        <h2 style="color:#243b6b; margin-bottom:20px;">
            Application Overview
        </h2>

        <div style="display:flex; gap:12px; flex-wrap:wrap;
                    margin-bottom:25px;">

            <?php
            $counts = [];
            $count_stmt = $conn->prepare(
                "SELECT application_status, COUNT(*) AS total
                 FROM Applications
                 WHERE user_id = ?
                 GROUP BY application_status"
            );

            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();

            while ($count_row = $count_result->fetch_assoc()) {
                $counts[$count_row['application_status']] =
                    (int)$count_row['total'];
            }

            $count_stmt->close();

            $total = array_sum($counts);
            ?>

            <div style="flex:1; min-width:120px; padding:15px;
                        background:#f2f5fc; border-radius:8px;">
                <strong style="font-size:25px;">
                    <?= $total ?>
                </strong>
                <p>Total Applications</p>
            </div>

            <div style="flex:1; min-width:120px; padding:15px;
                        background:#f2f5fc; border-radius:8px;">
                <strong style="font-size:25px;">
                    <?= (int)($counts['INTERVIEW'] ?? 0) ?>
                </strong>
                <p>Interviews</p>
            </div>

            <div style="flex:1; min-width:120px; padding:15px;
                        background:#f2f5fc; border-radius:8px;">
                <strong style="font-size:25px;">
                    <?= (int)($counts['OFFER'] ?? 0) ?>
                </strong>
                <p>Offers</p>
            </div>

            <div style="flex:1; min-width:120px; padding:15px;
                        background:#f2f5fc; border-radius:8px;">
                <strong style="font-size:25px;">
                    <?= (int)($counts['ACCEPTED'] ?? 0) ?>
                </strong>
                <p>Accepted</p>
            </div>

        </div>

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Your Applications
        </h2>

        <?php if ($applications->num_rows === 0): ?>

            <p>You haven't submitted any applications yet.</p>

        <?php else: ?>

            <?php while ($app = $applications->fetch_assoc()): ?>

                <div style="padding:18px 0;
                            border-bottom:1px solid #ddd;">

                    <h3 style="color:#243b6b;">
                        <?= htmlspecialchars($app['job_title']) ?>
                    </h3>

                    <p>
                        <strong>Company:</strong>
                        <?= htmlspecialchars($app['company_name']) ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?= htmlspecialchars($app['job_location'] ?? 'Not specified') ?>
                    </p>

                    <p>
                        <strong>Job Type:</strong>
                        <?= htmlspecialchars($app['job_type'] ?? 'Not specified') ?>
                    </p>

                    <p>
                        <strong>Date Applied:</strong>
                        <?= htmlspecialchars($app['date_applied']) ?>
                    </p>

                    <p>
                        <strong>Expected Salary:</strong>
                        <?php if ($app['expected_salary'] !== null): ?>
                            $<?= number_format((float)$app['expected_salary'], 2) ?>
                            per year
                        <?php else: ?>
                            Not specified
                        <?php endif; ?>
                    </p>

                    <p style="margin-top:8px;">
                        <strong>Status:</strong>

                        <span style="
                            display:inline-block;
                            padding:5px 10px;
                            border-radius:5px;
                            color:white;
                            background:<?= statusColor($app['application_status']) ?>;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            <?= htmlspecialchars($app['application_status']) ?>
                        </span>
                    </p>

                    <?php if (!empty($app['application_notes'])): ?>
                        <p style="margin-top:8px;">
                            <strong>Notes:</strong>
                            <?= nl2br(htmlspecialchars($app['application_notes'])) ?>
                        </p>
                    <?php endif; ?>

                    <p style="margin-top:12px;">
                        <a href="application_history.php?id=<?= (int)$app['application_id'] ?>"
                           style="color:#3156a3; font-weight:bold;
                           text-decoration:none;">
                            View Application History
                        </a>
                    </p>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

        <p class="login-link">
            <a href="jobs.php">Browse More Jobs</a>
            &nbsp; | &nbsp;
            <a href="dashboard.php">Back to Dashboard</a>
        </p>

    </div>

</div>

</body>
</html>

<?php
$stmt->close();
?>
