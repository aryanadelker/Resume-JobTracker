
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

// Fetch offers belonging to the logged-in user
$stmt = $conn->prepare(
    "SELECT
        o.offer_id,
        o.offered_salary,
        o.bonus,
        o.joining_date,
        o.offer_date,
        o.response_deadline,
        o.offer_status,
        a.application_id,
        j.job_title,
        c.company_name
     FROM Offers o
     INNER JOIN Applications a
        ON o.application_id = a.application_id
     INNER JOIN JobPostings j
        ON a.job_id = j.job_id
     INNER JOIN Companies c
        ON j.company_id = c.company_id
     WHERE a.user_id = ?
     ORDER BY o.offer_date DESC, o.offer_id DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$offers = $stmt->get_result();

function offerStatusColor($status) {
    switch ($status) {
        case 'ACCEPTED':
            return '#16803c';

        case 'REJECTED':
        case 'EXPIRED':
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

    <title>My Job Offers | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:850px;">

        <h1>My Job Offers</h1>

        <p class="subtitle">
            View and manage your employment offers
        </p>

        <h2 style="color:#243b6b; margin-bottom:20px;">
            Offer Overview
        </h2>

        <?php
        $counts = [
            'PENDING' => 0,
            'ACCEPTED' => 0,
            'REJECTED' => 0,
            'EXPIRED' => 0
        ];

        $count_stmt = $conn->prepare(
            "SELECT o.offer_status, COUNT(*) AS total
             FROM Offers o
             INNER JOIN Applications a
                ON o.application_id = a.application_id
             WHERE a.user_id = ?
             GROUP BY o.offer_status"
        );

        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();

        while ($row = $count_result->fetch_assoc()) {
            $counts[$row['offer_status']] = (int)$row['total'];
        }

        $count_stmt->close();
        ?>

        <div style="display:flex; gap:12px; flex-wrap:wrap;
                    margin-bottom:25px;">

            <?php foreach ($counts as $status => $total): ?>

                <div style="flex:1; min-width:120px; padding:15px;
                            background:#f2f5fc; border-radius:8px;">

                    <strong style="font-size:25px;">
                        <?= $total ?>
                    </strong>

                    <p><?= htmlspecialchars($status) ?></p>

                </div>

            <?php endforeach; ?>

        </div>

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Your Offers
        </h2>

        <?php if ($offers->num_rows === 0): ?>

            <p>You have not received any job offers yet.</p>

        <?php else: ?>

            <?php while ($offer = $offers->fetch_assoc()): ?>

                <div style="padding:18px 0;
                            border-bottom:1px solid #ddd;">

                    <h3 style="color:#243b6b;">
                        <?= htmlspecialchars($offer['job_title']) ?>
                    </h3>

                    <p>
                        <strong>Company:</strong>
                        <?= htmlspecialchars($offer['company_name']) ?>
                    </p>

                    <p>
                        <strong>Offered Annual Salary:</strong>
                        $<?= number_format((float)$offer['offered_salary'], 2) ?>
                    </p>

                    <p>
                        <strong>Bonus:</strong>
                        $<?= number_format((float)$offer['bonus'], 2) ?>
                    </p>

                    <p>
                        <strong>Offer Date:</strong>
                        <?= htmlspecialchars($offer['offer_date'] ?? 'Not specified') ?>
                    </p>

                    <p>
                        <strong>Joining Date:</strong>
                        <?= htmlspecialchars($offer['joining_date'] ?? 'Not specified') ?>
                    </p>

                    <p>
                        <strong>Response Deadline:</strong>
                        <?= htmlspecialchars($offer['response_deadline'] ?? 'Not specified') ?>
                    </p>

                    <p style="margin-top:8px;">
                        <strong>Offer Status:</strong>

                        <span style="
                            display:inline-block;
                            padding:5px 10px;
                            border-radius:5px;
                            color:white;
                            background:<?= offerStatusColor($offer['offer_status']) ?>;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            <?= htmlspecialchars($offer['offer_status']) ?>
                        </span>
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
