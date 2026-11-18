
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

$message = "";
$error = "";

if (empty($_SESSION['notification_csrf'])) {
    $_SESSION['notification_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['notification_csrf'], $token)) {
        $error = "Invalid request. Please refresh the page.";

    } elseif (isset($_POST['mark_read'])) {

        $notification_id = filter_var(
            $_POST['notification_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        if ($notification_id && $notification_id > 0) {

            $stmt = $conn->prepare(
                "UPDATE Notifications
                 SET is_read = TRUE
                 WHERE notification_id = ? AND user_id = ?"
            );

            $stmt->bind_param("ii", $notification_id, $user_id);

            if ($stmt->execute()) {
                $message = "Notification marked as read.";
            } else {
                $error = "Unable to update notification.";
            }

            $stmt->close();

        } else {
            $error = "Invalid notification.";
        }

    } elseif (isset($_POST['mark_all_read'])) {

        $stmt = $conn->prepare(
            "UPDATE Notifications
             SET is_read = TRUE
             WHERE user_id = ? AND is_read = FALSE"
        );

        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $message = "All notifications marked as read.";
        } else {
            $error = "Unable to update notifications.";
        }

        $stmt->close();
    }
}

// Fetch notification counts
$count_stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN is_read = FALSE THEN 1 ELSE 0 END) AS unread
     FROM Notifications
     WHERE user_id = ?"
);

$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();

$counts = $count_stmt->get_result()->fetch_assoc();
$count_stmt->close();

$total = (int)$counts['total'];
$unread = (int)$counts['unread'];

// Fetch user's notifications
$stmt = $conn->prepare(
    "SELECT notification_id, message, notification_type,
            is_read, created_at
     FROM Notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Notifications | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:850px;">

        <h1>My Notifications</h1>

        <p class="subtitle">
            Stay updated with your job application activities
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

        <h2 style="color:#243b6b;">
            Notification Overview
        </h2>

        <div style="display:flex; gap:12px; flex-wrap:wrap;
                    margin:20px 0;">

            <div style="flex:1; min-width:120px; padding:15px;
                        background:#f2f5fc; border-radius:8px;">

                <strong style="font-size:25px;">
                    <?= $total ?>
                </strong>

                <p>Total Notifications</p>

            </div>

            <div style="flex:1; min-width:120px; padding:15px;
                        background:#f2f5fc; border-radius:8px;">

                <strong style="font-size:25px;">
                    <?= $unread ?>
                </strong>

                <p>Unread Notifications</p>

            </div>

        </div>

        <?php if ($unread > 0): ?>

            <form method="POST" action="notifications.php"
                  style="margin-bottom:20px;">

                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['notification_csrf']) ?>">

                <button type="submit" name="mark_all_read">
                    Mark All as Read
                </button>

            </form>

        <?php endif; ?>

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Notification History
        </h2>

        <?php if ($notifications->num_rows === 0): ?>

            <p>You have no notifications yet.</p>

        <?php else: ?>

            <?php while ($notification = $notifications->fetch_assoc()): ?>

                <div style="
                    padding:18px;
                    margin-bottom:12px;
                    border-radius:8px;
                    background:<?= $notification['is_read']
                        ? '#f7f7f7' : '#edf3ff' ?>;
                    border:1px solid #ddd;
                ">

                    <p style="font-weight:bold;">
                        <?= htmlspecialchars($notification['message']) ?>
                    </p>

                    <p>
                        <strong>Type:</strong>
                        <?= htmlspecialchars(
                            $notification['notification_type'] ?? 'General'
                        ) ?>
                    </p>

                    <p>
                        <strong>Date:</strong>
                        <?= htmlspecialchars($notification['created_at']) ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?= $notification['is_read']
                            ? 'READ' : 'UNREAD' ?>
                    </p>

                    <?php if (!$notification['is_read']): ?>

                        <form method="POST" action="notifications.php">

                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars($_SESSION['notification_csrf']) ?>">

                            <input type="hidden" name="notification_id"
                                   value="<?= (int)$notification['notification_id'] ?>">

                            <button type="submit" name="mark_read">
                                Mark as Read
                            </button>

                        </form>

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
