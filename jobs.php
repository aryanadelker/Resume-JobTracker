
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$search = trim($_GET['search'] ?? "");

$sql = "SELECT
            j.job_id,
            j.job_title,
            j.department,
            j.job_location,
            j.job_type,
            j.min_salary,
            j.max_salary,
            j.posting_date,
            j.application_deadline,
            j.job_description,
            c.company_name
        FROM JobPostings j
        INNER JOIN Companies c
            ON j.company_id = c.company_id
        WHERE j.job_status = 'OPEN'
          AND (j.application_deadline IS NULL
               OR j.application_deadline >= CURDATE())";

if ($search !== "") {
    $sql .= " AND (j.job_title LIKE ? OR c.company_name LIKE ?)";
}

$sql .= " ORDER BY j.posting_date DESC";

$stmt = $conn->prepare($sql);

if ($search !== "") {
    $keyword = "%" . $search . "%";
    $stmt->bind_param("ss", $keyword, $keyword);
}

$stmt->execute();
$jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings | Resume & Job Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">
    <div class="register-card" style="max-width:750px;">

        <h1>Job Listings</h1>

        <p class="subtitle">
            Explore available job opportunities
        </p>

        <form method="GET" action="jobs.php">

            <label for="search">Search Jobs</label>

            <input
                type="text"
                id="search"
                name="search"
                placeholder="Search by job title or company"
                value="<?= htmlspecialchars($search) ?>"
            >

            <button type="submit">Search Jobs</button>

        </form>

        <hr style="margin:25px 0;">

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Available Opportunities
        </h2>

        <?php if ($jobs->num_rows === 0): ?>

            <p>No matching job opportunities found.</p>

        <?php else: ?>

            <?php while ($job = $jobs->fetch_assoc()): ?>

                <div style="padding:18px 0; border-bottom:1px solid #ddd;">

                    <h3 style="color:#243b6b;">
                        <?= htmlspecialchars($job['job_title']) ?>
                    </h3>

                    <p style="margin-top:8px;">
                        <strong>Company:</strong>
                        <?= htmlspecialchars($job['company_name']) ?>
                    </p>

                    <p>
                        <strong>Department:</strong>
                        <?= htmlspecialchars($job['department'] ?? 'Not specified') ?>
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
                        <strong>Salary:</strong>
                        <?php if ($job['min_salary'] !== null && $job['max_salary'] !== null): ?>
                            $<?= number_format((float)$job['min_salary']) ?>
                            – $<?= number_format((float)$job['max_salary']) ?> per year
                        <?php else: ?>
                            Not specified
                        <?php endif; ?>
                    </p>

                    <p>
                        <strong>Application Deadline:</strong>
                        <?= htmlspecialchars($job['application_deadline'] ?? 'Not specified') ?>
                    </p>

                    <p style="margin-top:8px;">
                        <?= nl2br(htmlspecialchars($job['job_description'] ?? '')) ?>
                    </p>
                    <a href="apply_job.php?job_id=<?= (int)$job['job_id'] ?>"
   style="display:inline-block; margin-top:12px;
   padding:10px 18px; background:#3156a3;
   color:white; text-decoration:none; border-radius:6px;">
    Apply Now
</a>

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
