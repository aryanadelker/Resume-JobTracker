
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

$interview_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$interview_id || $interview_id < 1) {
    http_response_code(400);
    die("Invalid interview ID.");
}

// Fetch interview only if it belongs to the logged-in user
$stmt = $conn->prepare(
    "SELECT
        i.interview_id,
        i.interview_round,
        i.interview_type,
        i.interview_date,
        i.interview_location,
        i.interview_result,
        i.feedback,
        j.job_title,
        c.company_name,
        r.recruiter_name,
        r.email AS recruiter_email,
        r.phone AS recruiter_phone
     FROM Interviews i
     INNER JOIN Applications a
        ON i.application_id = a.application_id
     INNER JOIN JobPostings j
        ON a.job_id = j.job_id
     INNER JOIN Companies c
        ON j.company_id = c.company_id
     LEFT JOIN Recruiters r
        ON i.recruiter_id = r.recruiter_id
     WHERE i.interview_id = ?
       AND a.user_id = ?"
);

$stmt->bind_param("ii", $interview_id, $user_id);
$stmt->execute();

$interview = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$interview) {
    http_response_code(404);
    die("Interview not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Interview Details | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:700px;">

        <h1>Interview Details</h1>

        <p class="subtitle">
            Complete information about your interview
        </p>

        <div style="padding:18px; background:#f2f5fc;
                    border-radius:8px; margin-bottom:20px;">

            <h2 style="color:#243b6b;">
                <?= htmlspecialchars($interview['job_title']) ?>
            </h2>

            <p>
                <strong>Company:</strong>
                <?= htmlspecialchars($interview['company_name']) ?>
            </p>

        </div>

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Interview Information
        </h2>

        <p>
            <strong>Interview ID:</strong>
            <?= (int)$interview['interview_id'] ?>
        </p>

        <p>
            <strong>Interview Round:</strong>
            <?= (int)$interview['interview_round'] ?>
        </p>

        <p>
            <strong>Interview Type:</strong>
            <?= htmlspecialchars($interview['interview_type'] ?? 'Not specified') ?>
        </p>

        <p>
            <strong>Date and Time:</strong>
            <?= htmlspecialchars($interview['interview_date']) ?>
        </p>

        <p>
            <strong>Location:</strong>
            <?= htmlspecialchars($interview['interview_location'] ?? 'Not specified') ?>
        </p>

        <p>
            <strong>Interview Result:</strong>
            <?= htmlspecialchars($interview['interview_result']) ?>
        </p>

        <hr style="margin:20px 0; border:0;
                   border-top:1px solid #ddd;">

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Recruiter Information
        </h2>

        <p>
            <strong>Name:</strong>
            <?= htmlspecialchars($interview['recruiter_name'] ?? 'Not assigned') ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?= htmlspecialchars($interview['recruiter_email'] ?? 'Not available') ?>
        </p>

        <p>
            <strong>Phone:</strong>
            <?= htmlspecialchars($interview['recruiter_phone'] ?? 'Not available') ?>
        </p>

        <hr style="margin:20px 0; border:0;
                   border-top:1px solid #ddd;">

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Interview Feedback
        </h2>

        <p>
            <?= nl2br(htmlspecialchars(
                $interview['feedback'] ?? 'No feedback available yet.'
            )) ?>
        </p>

        <p class="login-link">
            <a href="interviews.php">Back to My Interviews</a>
            &nbsp; | &nbsp;
            <a href="dashboard.php">Back to Dashboard</a>
        </p>

    </div>

</div>

</body>
</html>
