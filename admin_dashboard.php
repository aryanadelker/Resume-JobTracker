
<?php
require_once "includes/auth.php";
require_once "config/db.php";

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: dashboard.php");
    exit;
}

// Fetch system statistics
$statistics = [];

$tables = [
    'Users' => 'Total Users',
    'Companies' => 'Total Companies',
    'JobPostings' => 'Total Job Postings',
    'Applications' => 'Total Applications',
    'Interviews' => 'Total Interviews',
    'Offers' => 'Total Offers'
];

foreach ($tables as $table => $label) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

    if ($result) {
        $statistics[$label] = (int)$result->fetch_assoc()['total'];
    } else {
        $statistics[$label] = 0;
    }
}

// Fetch registered users
$users = $conn->query(
    "SELECT user_id, full_name, email, role, account_status, created_at
     FROM Users
     ORDER BY created_at DESC"
);

// Fetch companies
$companies = $conn->query(
    "SELECT company_id, company_name, industry, location
     FROM Companies
     ORDER BY company_name"
);

// Fetch job postings
$jobs = $conn->query(
    "SELECT j.job_id, j.job_title, c.company_name,
            j.job_location, j.job_status, j.posting_date
     FROM JobPostings j
     INNER JOIN Companies c ON j.company_id = c.company_id
     ORDER BY j.job_id DESC"
);

// Fetch applications
$applications = $conn->query(
    "SELECT a.application_id, u.full_name,
            j.job_title, c.company_name,
            a.date_applied, a.application_status
     FROM Applications a
     INNER JOIN Users u ON a.user_id = u.user_id
     INNER JOIN JobPostings j ON a.job_id = j.job_id
     INNER JOIN Companies c ON j.company_id = c.company_id
     ORDER BY a.date_applied DESC"
);

// Fetch recent activity logs
$activities = $conn->query(
    "SELECT al.action, al.table_affected,
            al.action_time, u.full_name
     FROM ActivityLogs al
     LEFT JOIN Users u ON al.user_id = u.user_id
     ORDER BY al.action_time DESC
     LIMIT 20"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .admin-card {
            max-width: 1100px !important;
            width: 95%;
            text-align: left;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin: 20px 0;
        }

        .admin-stat {
            background: #f1f4fb;
            padding: 18px;
            border-radius: 8px;
            text-align: center;
        }

        .admin-stat h3 {
            font-size: 28px;
            color: #3156a3;
            margin: 0 0 8px;
        }

        .admin-section {
            margin-top: 30px;
        }

        .admin-table-wrapper {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }

        .admin-table th,
        .admin-table td {
            padding: 11px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .admin-table th {
            background: #3156a3;
            color: white;
        }

        .admin-table tr:nth-child(even) {
            background: #f5f7fc;
        }

        .admin-navigation a {
            display: inline-block;
            background: #3156a3;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px;
            border-radius: 6px;
        }

        .admin-navigation a:hover {
            background: #243b6b;
        }
    </style>
</head>

<body>

<div class="register-container">

    <div class="register-card admin-card">

        <h1>Admin Dashboard</h1>

        <p class="subtitle">
            Resume & Job Application Tracker Administration
        </p>

        <p>
            Welcome,
            <strong>
                <?= htmlspecialchars($_SESSION['full_name']) ?>
            </strong>
        </p>

        <div class="admin-navigation">
            <a href="dashboard.php">User Dashboard</a>
            <a href="activity_logs.php">Activity Logs</a>
            <a href="logout.php">Logout</a>
        </div>

        <div class="admin-section">

            <h2>System Statistics</h2>

            <div class="admin-stats">

                <?php foreach ($statistics as $label => $total): ?>

                    <div class="admin-stat">
                        <h3><?= $total ?></h3>
                        <strong><?= htmlspecialchars($label) ?></strong>
                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <div class="admin-section">

            <h2>Registered Users</h2>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>

                    <?php while ($user = $users->fetch_assoc()): ?>

                        <tr>
                            <td><?= (int)$user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['account_status']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                        </tr>

                    <?php endwhile; ?>

                </table>

            </div>

        </div>

        <div class="admin-section">

            <h2>Companies</h2>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <tr>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Industry</th>
                        <th>Location</th>
                    </tr>

                    <?php while ($company = $companies->fetch_assoc()): ?>

                        <tr>
                            <td><?= (int)$company['company_id'] ?></td>
                            <td><?= htmlspecialchars($company['company_name']) ?></td>
                            <td><?= htmlspecialchars($company['industry'] ?? '') ?></td>
                            <td><?= htmlspecialchars($company['location'] ?? '') ?></td>
                        </tr>

                    <?php endwhile; ?>

                </table>

            </div>

        </div>

        <div class="admin-section">

            <h2>Job Postings</h2>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <tr>
                        <th>ID</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Posting Date</th>
                    </tr>

                    <?php while ($job = $jobs->fetch_assoc()): ?>

                        <tr>
                            <td><?= (int)$job['job_id'] ?></td>
                            <td><?= htmlspecialchars($job['job_title']) ?></td>
                            <td><?= htmlspecialchars($job['company_name']) ?></td>
                            <td><?= htmlspecialchars($job['job_location'] ?? '') ?></td>
                            <td><?= htmlspecialchars($job['job_status']) ?></td>
                            <td><?= htmlspecialchars($job['posting_date'] ?? '') ?></td>
                        </tr>

                    <?php endwhile; ?>

                </table>

            </div>

        </div>

        <div class="admin-section">

            <h2>All Job Applications</h2>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Job</th>
                        <th>Company</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                    </tr>

                    <?php while ($application = $applications->fetch_assoc()): ?>

                        <tr>
                            <td><?= (int)$application['application_id'] ?></td>
                            <td><?= htmlspecialchars($application['full_name']) ?></td>
                            <td><?= htmlspecialchars($application['job_title']) ?></td>
                            <td><?= htmlspecialchars($application['company_name']) ?></td>
                            <td><?= htmlspecialchars($application['date_applied']) ?></td>
                            <td><?= htmlspecialchars($application['application_status']) ?></td>
                        </tr>

                    <?php endwhile; ?>

                </table>

            </div>

        </div>

        <div class="admin-section">

            <h2>Recent System Activity</h2>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Date and Time</th>
                    </tr>

                    <?php while ($activity = $activities->fetch_assoc()): ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                    $activity['full_name'] ?? 'System'
                                ) ?>
                            </td>

                            <td><?= htmlspecialchars($activity['action']) ?></td>

                            <td><?= htmlspecialchars($activity['table_affected'] ?? '') ?></td>

                            <td><?= htmlspecialchars($activity['action_time']) ?></td>
                        </tr>

                    <?php endwhile; ?>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>
