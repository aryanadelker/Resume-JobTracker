
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

// Fetch interviews belonging to the logged-in user
$stmt = $conn->prepare(
    "SELECT
        i.interview_id,
        i.interview_round,
        i.interview_type,
        i.interview_date,
        i.interview_location,
        i.interview_result,
        i.feedback,
        a.application_id,
        j.job_title,
        c.company_name,
        r.recruiter_name
     FROM Interviews i
     INNER JOIN Applications a
        ON i.application_id = a.application_id
     INNER JOIN JobPostings j
        ON a.job_id = j.job_id
     INNER JOIN Companies c
        ON j.company_id = c.company_id
     LEFT JOIN Recruiters r
        ON i.recruiter_id = r.recruiter_id
     WHERE a.user_id = ?
     ORDER BY i.interview_date DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$interviews = $stmt->get_result();

function interviewResultColor($result) {
    switch ($result) {
        case 'PASSED':
            return '#16803c';

        case 'FAILED':
            return '#b42318';

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

    <title>My Interviews | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:850px;">

        <h1>My Interviews</h1>

        <p class="subtitle">
            View and manage your job interview schedule
        </p>

        <h2 style="color:#243b6b; margin-bottom:20px;">
            Interview Overview
        </h2>

        <div style="display:flex; gap:12px; flex-wrap:wrap;
                    margin-bottom:25px;">

            <div style="flex:1; min-width:120px; padding:15px;
                        background:#f2f5fc; border-radius:8px;">

                <strong style="font-size:25px;">
                    <?= $interviews->num_rows ?>
                </strong>

                <p>Total Interviews</p>

            </div>

        </div>

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Interview Schedule
        </h2>

        <?php if ($interviews->num_rows === 0): ?>

            <p>
                You currently have no scheduled interviews.
            </p>

            <p>
                Your interview details will appear here when an
                interview is associated with one of your applications.
            </p>

        <?php else: ?>

            <?php while ($interview = $interviews->fetch_assoc()): ?>

                <div style="padding:18px 0;
                            border-bottom:1px solid #ddd;">

                    <h3 style="color:#243b6b;">
                        <?= htmlspecialchars($interview['job_title']) ?>
                    </h3>

                    <p>
                        <strong>Company:</strong>
                        <?= htmlspecialchars($interview['company_name']) ?>
                    </p>

                    <p>
                        <strong>Interview Round:</strong>
                        <?= (int)$interview['interview_round'] ?>
                    </p>

                    <p>
                        <strong>Interview Type:</strong>
                        <?= htmlspecialchars(
                            $interview['interview_type'] ?? 'Not specified'
                        ) ?>
                    </p>

                    <p>
                        <strong>Date and Time:</strong>
                        <?= htmlspecialchars($interview['interview_date']) ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?= htmlspecialchars(
                            $interview['interview_location'] ?? 'Not specified'
                        ) ?>
                    </p>

                    <p>
                        <strong>Recruiter:</strong>
                        <?= htmlspecialchars(
                            $interview['recruiter_name'] ?? 'Not assigned'
                        ) ?>
                    </p>

                    <p style="margin-top:8px;">
                        <strong>Result:</strong>

                        <span style="
                            display:inline-block;
                            padding:5px 10px;
                            border-radius:5px;
                            color:white;
                            background:<?= interviewResultColor(
                                $interview['interview_result']
                            ) ?>;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            <?= htmlspecialchars($interview['interview_result']) ?>
                        </span>
                    </p>

                    <?php if (!empty($interview['feedback'])): ?>

                        <p style="margin-top:8px;">
                            <strong>Feedback:</strong>
                            <?= nl2br(htmlspecialchars($interview['feedback'])) ?>
                        </p>

                    <?php endif; ?>

                    <p style="margin-top:12px;">
                        <a href="interview_details.php?id=<?= (int)$interview['interview_id'] ?>"
                           style="color:#3156a3; font-weight:bold;
                                  text-decoration:none;">
                            View Interview Details
                        </a>
                    </p>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

        <p class="login-link">
            <a href="my_applications.php">My Applications</a>
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
