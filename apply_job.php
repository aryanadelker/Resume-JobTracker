
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

$error = "";
$message = "";
$job = null;
$resumes = null;

if (empty($_SESSION['apply_csrf'])) {
    $_SESSION['apply_csrf'] = bin2hex(random_bytes(32));
}

// Get the selected job ID
$job_id = filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
}

// Load the selected job
if ($job_id && $job_id > 0) {
    $job_stmt = $conn->prepare(
        "SELECT j.job_id, j.job_title, j.department,
                j.job_location, j.job_type, j.min_salary,
                j.max_salary, j.application_deadline,
                j.job_status, c.company_name
         FROM JobPostings j
         INNER JOIN Companies c
             ON j.company_id = c.company_id
         WHERE j.job_id = ?"
    );

    $job_stmt->bind_param("i", $job_id);
    $job_stmt->execute();
    $job = $job_stmt->get_result()->fetch_assoc();
    $job_stmt->close();
}

// Handle application submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['apply_csrf'], $_POST['csrf_token'])
    ) {
        $error = "Invalid form submission. Please refresh and try again.";
    }

    $resume_id = filter_input(INPUT_POST, 'resume_id', FILTER_VALIDATE_INT);
    $salary_input = trim($_POST['expected_salary'] ?? "");
    $notes = trim($_POST['application_notes'] ?? "");

    if (!$error && !$job) {
        $error = "The selected job could not be found.";
    }

    if (!$error && (
        $job['job_status'] !== 'OPEN' ||
        ($job['application_deadline'] !== null &&
         $job['application_deadline'] < date('Y-m-d'))
    )) {
        $error = "This job is no longer accepting applications.";
    }

    if (!$error && (!$resume_id || $resume_id < 1)) {
        $error = "Please select a resume.";
    }

    if (!$error && (
        $salary_input === "" ||
        !is_numeric($salary_input) ||
        (float)$salary_input < 0 ||
        (float)$salary_input > 9999999999
    )) {
        $error = "Please enter a valid expected annual salary in USD.";
    }

    if (!$error && strlen($notes) > 5000) {
        $error = "Application notes must not exceed 5,000 characters.";
    }

    // Confirm the selected resume belongs to this user
    if (!$error) {
        $resume_check = $conn->prepare(
            "SELECT resume_id
             FROM Resumes
             WHERE resume_id = ? AND user_id = ?"
        );

        $resume_check->bind_param("ii", $resume_id, $user_id);
        $resume_check->execute();
        $resume_check->store_result();

        if ($resume_check->num_rows !== 1) {
            $error = "Please select one of your own resumes.";
        }

        $resume_check->close();
    }

    // Prevent duplicate applications
    if (!$error) {
        $duplicate_check = $conn->prepare(
            "SELECT application_id
             FROM Applications
             WHERE user_id = ? AND job_id = ?"
        );

        $duplicate_check->bind_param("ii", $user_id, $job_id);
        $duplicate_check->execute();
        $duplicate_check->store_result();

        if ($duplicate_check->num_rows > 0) {
            $error = "You have already applied for this job.";
        }

        $duplicate_check->close();
    }

    // Save application and initial history record
    if (!$error) {
        $salary = (float)$salary_input;
        $date_applied = date('Y-m-d');
        $status = 'APPLIED';

        $notes = $notes === "" ? null : $notes;

        // Find a recruiter for this company's job, if available
        $recruiter_stmt = $conn->prepare(
            "SELECT recruiter_id
             FROM Recruiters
             WHERE company_id = (
                 SELECT company_id
                 FROM JobPostings
                 WHERE job_id = ?
             )
             ORDER BY recruiter_id
             LIMIT 1"
        );

        $recruiter_stmt->bind_param("i", $job_id);
        $recruiter_stmt->execute();
        $recruiter_row = $recruiter_stmt->get_result()->fetch_assoc();
        $recruiter_stmt->close();

        $recruiter_id = $recruiter_row
            ? (int)$recruiter_row['recruiter_id']
            : null;

        try {
            $conn->begin_transaction();

            $insert = $conn->prepare(
                "INSERT INTO Applications
                 (user_id, job_id, resume_id, recruiter_id,
                  date_applied, application_status,
                  expected_salary, application_notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $insert->bind_param(
                "iiiissds",
                $user_id,
                $job_id,
                $resume_id,
                $recruiter_id,
                $date_applied,
                $status,
                $salary,
                $notes
            );

            $insert->execute();
            $application_id = $conn->insert_id;
            $insert->close();

            $history = $conn->prepare(
                "INSERT INTO ApplicationHistory
                 (application_id, changed_by, old_status,
                  new_status, remarks)
                 VALUES (?, ?, NULL, 'APPLIED', ?)"
            );

            $remarks = "Application submitted by the user.";

            $history->bind_param(
                "iis",
                $application_id,
                $user_id,
                $remarks
            );

            $history->execute();
            $history->close();

            $conn->commit();

            $message = "Your application has been submitted successfully. Application ID: "
                     . $application_id;

            $_SESSION['apply_csrf'] = bin2hex(random_bytes(32));

        } catch (Throwable $e) {
            $conn->rollback();
            $error = "Unable to submit your application. Please try again.";
        }
    }
}

// Load only this user's resumes
$resume_stmt = $conn->prepare(
    "SELECT resume_id, resume_name, version_number, is_active
     FROM Resumes
     WHERE user_id = ?
     ORDER BY is_active DESC, resume_name, version_number DESC"
);

$resume_stmt->bind_param("i", $user_id);
$resume_stmt->execute();
$resumes = $resume_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job | Resume & Job Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">
    <div class="register-card">

        <h1>Apply for Job</h1>

        <p class="subtitle">
            Submit your job application
        </p>

        <?php if ($message !== ""): ?>
            <div class="success-message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!$job): ?>

            <p>Job not found. Please return to the job listings.</p>

        <?php else: ?>

            <div style="padding:15px; background:#f2f5fc;
                        border-radius:8px; margin-bottom:20px;">

                <h2 style="color:#243b6b;">
                    <?= htmlspecialchars($job['job_title']) ?>
                </h2>

                <p>
                    <strong>Company:</strong>
                    <?= htmlspecialchars($job['company_name']) ?>
                </p>

                <p>
                    <strong>Location:</strong>
                    <?= htmlspecialchars($job['job_location'] ?? 'Not specified') ?>
                </p>

                <p>
                    <strong>Job Type:</strong>
                    <?= htmlspecialchars($job['job_type'] ?? 'Not specified') ?>
                </p>

                <p>
                    <strong>Salary Range:</strong>
                    <?php if ($job['min_salary'] !== null &&
                              $job['max_salary'] !== null): ?>
                        $<?= number_format((float)$job['min_salary']) ?>
                        – $<?= number_format((float)$job['max_salary']) ?> per year
                    <?php else: ?>
                        Not specified
                    <?php endif; ?>
                </p>

            </div>

            <?php if ($message === ""): ?>

                <?php if ($resumes->num_rows === 0): ?>

                    <p>You need to have a resume before applying.</p>
                    <p class="login-link">
                        <a href="resume.php">Go to My Resumes</a>
                    </p>

                <?php elseif ($job['job_status'] !== 'OPEN' ||
                    ($job['application_deadline'] !== null &&
                     $job['application_deadline'] < date('Y-m-d'))): ?>

                    <p>This job is no longer accepting applications.</p>

                <?php else: ?>

                    <form method="POST" action="apply_job.php">

                        <input type="hidden" name="job_id"
                               value="<?= (int)$job['job_id'] ?>">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['apply_csrf']) ?>">

                        <label for="resume_id">Select Resume</label>

                        <select id="resume_id" name="resume_id"
                                required style="width:100%; padding:12px;
                                border:1px solid #ccc; border-radius:6px;">

                            <option value="">Choose your resume</option>

                            <?php while ($resume = $resumes->fetch_assoc()): ?>

                                <option value="<?= (int)$resume['resume_id'] ?>">
                                    <?= htmlspecialchars($resume['resume_name']) ?>
                                    - Version <?= (int)$resume['version_number'] ?>
                                    <?= $resume['is_active'] ? '(Active)' : '' ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                        <label for="expected_salary">
                            Expected Annual Salary (USD)
                        </label>

                        <input type="number"
                               id="expected_salary"
                               name="expected_salary"
                               min="0"
                               max="9999999999"
                               step="0.01"
                               placeholder="e.g. 120000"
                               required>

                        <label for="application_notes">
                            Application Notes
                        </label>

                        <textarea id="application_notes"
                                  name="application_notes"
                                  rows="4"
                                  maxlength="5000"
                                  placeholder="Add a note about your application..."
                                  style="width:100%; padding:12px;
                                  border:1px solid #ccc; border-radius:6px;">
                        </textarea>

                        <button type="submit">
                            Submit Application
                        </button>

                    </form>

                <?php endif; ?>

            <?php endif; ?>

        <?php endif; ?>

        <p class="login-link">
            <a href="jobs.php">Back to Job Listings</a>
        </p>

    </div>
</div>

</body>
</html>

<?php
$resume_stmt->close();
?>
