
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

$message = "";
$error = "";

if (empty($_SESSION['reminder_csrf'])) {
    $_SESSION['reminder_csrf'] = bin2hex(random_bytes(32));
}

// Handle reminder creation and status updates
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['reminder_csrf'], $_POST['csrf_token'])
    ) {
        $error = "Invalid form submission. Please refresh the page.";

    } elseif (isset($_POST['add_reminder'])) {

        $title = trim($_POST['reminder_title'] ?? "");
        $date = trim($_POST['reminder_date'] ?? "");
        $type = $_POST['reminder_type'] ?? "";
        $application_id = $_POST['application_id'] ?? "";

        $valid_types = ['FOLLOW_UP', 'INTERVIEW', 'DEADLINE', 'OTHER'];

        if ($title === "" || strlen($title) > 150) {
            $error = "Please enter a valid reminder title.";

        } elseif (strlen($date) !== 16 ||
                  !DateTime::createFromFormat('Y-m-d\TH:i', $date)) {
            $error = "Please enter a valid reminder date and time.";

        } elseif (!in_array($type, $valid_types, true)) {
            $error = "Please select a valid reminder type.";

        } else {

            $date = str_replace('T', ' ', $date) . ':00';

            if ($application_id !== "") {

                $application_id = filter_var(
                    $application_id,
                    FILTER_VALIDATE_INT
                );

                $check = $conn->prepare(
                    "SELECT application_id
                     FROM Applications
                     WHERE application_id = ? AND user_id = ?"
                );

                $check->bind_param(
                    "ii",
                    $application_id,
                    $user_id
                );

                $check->execute();
                $check->store_result();

                if ($check->num_rows !== 1) {
                    $error = "Please select one of your own applications.";
                }

                $check->close();

            } else {
                $application_id = null;
            }

            if (!$error) {

                $insert = $conn->prepare(
                    "INSERT INTO Reminders
                     (user_id, application_id, reminder_title,
                      reminder_date, reminder_type)
                     VALUES (?, ?, ?, ?, ?)"
                );

                $insert->bind_param(
                    "iisss",
                    $user_id,
                    $application_id,
                    $title,
                    $date,
                    $type
                );

                if ($insert->execute()) {
                    $message = "Reminder created successfully.";
                    $_SESSION['reminder_csrf'] =
                        bin2hex(random_bytes(32));
                } else {
                    $error = "Unable to create reminder.";
                }

                $insert->close();
            }
        }

    } elseif (isset($_POST['update_status'])) {

        $reminder_id = filter_var(
            $_POST['reminder_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        $new_status = $_POST['new_status'] ?? '';

        if (
            !$reminder_id ||
            !in_array($new_status, ['COMPLETED', 'CANCELLED'], true)
        ) {
            $error = "Invalid reminder update.";

        } else {

            $update = $conn->prepare(
                "UPDATE Reminders
                 SET reminder_status = ?
                 WHERE reminder_id = ? AND user_id = ?"
            );

            $update->bind_param(
                "sii",
                $new_status,
                $reminder_id,
                $user_id
            );

            if ($update->execute() && $update->affected_rows > 0) {
                $message = "Reminder status updated successfully.";
                $_SESSION['reminder_csrf'] =
                    bin2hex(random_bytes(32));
            } else {
                $error = "Reminder could not be updated.";
            }

            $update->close();
        }
    }
}

// Fetch only this user's applications
$app_stmt = $conn->prepare(
    "SELECT a.application_id, j.job_title, c.company_name
     FROM Applications a
     INNER JOIN JobPostings j ON a.job_id = j.job_id
     INNER JOIN Companies c ON j.company_id = c.company_id
     WHERE a.user_id = ?
     ORDER BY a.date_applied DESC"
);

$app_stmt->bind_param("i", $user_id);
$app_stmt->execute();
$user_applications = $app_stmt->get_result();

// Fetch only this user's reminders
$stmt = $conn->prepare(
    "SELECT reminder_id, application_id, reminder_title,
            reminder_date, reminder_type, reminder_status
     FROM Reminders
     WHERE user_id = ?
     ORDER BY reminder_date ASC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$reminders = $stmt->get_result();

function reminderColor($status) {
    switch ($status) {
        case 'COMPLETED':
            return '#16803c';

        case 'CANCELLED':
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

    <title>My Reminders | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:850px;">

        <h1>My Reminders</h1>

        <p class="subtitle">
            Stay organized throughout your job search
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

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Create New Reminder
        </h2>

        <form method="POST" action="reminders.php">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['reminder_csrf']) ?>">

            <label for="reminder_title">Reminder Title</label>

            <input type="text"
                   id="reminder_title"
                   name="reminder_title"
                   maxlength="150"
                   placeholder="e.g. Follow up with Microsoft recruiter"
                   required>

            <label for="reminder_date">Reminder Date and Time</label>

            <input type="datetime-local"
                   id="reminder_date"
                   name="reminder_date"
                   required>

            <label for="reminder_type">Reminder Type</label>

            <select id="reminder_type" name="reminder_type"
                    required style="width:100%; padding:12px;
                    border:1px solid #ccc; border-radius:6px;">

                <option value="">Select reminder type</option>
                <option value="FOLLOW_UP">Follow Up</option>
                <option value="INTERVIEW">Interview</option>
                <option value="DEADLINE">Application Deadline</option>
                <option value="OTHER">Other</option>

            </select>

            <label for="application_id">
                Related Job Application (Optional)
            </label>

            <select id="application_id" name="application_id"
                    style="width:100%; padding:12px;
                    border:1px solid #ccc; border-radius:6px;">

                <option value="">No related application</option>

                <?php while ($app = $user_applications->fetch_assoc()): ?>

                    <option value="<?= (int)$app['application_id'] ?>">
                        <?= htmlspecialchars(
                            $app['job_title'] . ' - ' . $app['company_name']
                        ) ?>
                    </option>

                <?php endwhile; ?>

            </select>

            <button type="submit" name="add_reminder">
                Create Reminder
            </button>

        </form>

        <hr style="margin:25px 0; border:0;
                   border-top:1px solid #ddd;">

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Your Reminders
        </h2>

        <?php if ($reminders->num_rows === 0): ?>

            <p>You have no reminders yet.</p>

        <?php else: ?>

            <?php while ($reminder = $reminders->fetch_assoc()): ?>

                <div style="padding:18px 0;
                            border-bottom:1px solid #ddd;">

                    <h3 style="color:#243b6b;">
                        <?= htmlspecialchars($reminder['reminder_title']) ?>
                    </h3>

                    <p>
                        <strong>Date:</strong>
                        <?= htmlspecialchars($reminder['reminder_date']) ?>
                    </p>

                    <p>
                        <strong>Type:</strong>
                        <?= htmlspecialchars($reminder['reminder_type'] ?? 'Other') ?>
                    </p>

                    <p>
                        <strong>Status:</strong>

                        <span style="
                            display:inline-block;
                            padding:5px 10px;
                            border-radius:5px;
                            color:white;
                            background:<?= reminderColor($reminder['reminder_status']) ?>;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            <?= htmlspecialchars($reminder['reminder_status']) ?>
                        </span>

                    </p>

                    <?php if ($reminder['reminder_status'] === 'PENDING'): ?>

                        <form method="POST" action="reminders.php"
                              style="display:flex; gap:10px; flex-wrap:wrap;">

                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars($_SESSION['reminder_csrf']) ?>">

                            <input type="hidden" name="reminder_id"
                                   value="<?= (int)$reminder['reminder_id'] ?>">

                            <button type="submit"
                                    name="update_status"
                                    value="1"
                                    onclick="this.form.new_status.value='COMPLETED';">
                                Mark Completed
                            </button>

                            <button type="submit"
                                    name="update_status"
                                    value="1"
                                    style="background:#b42318;"
                                    onclick="this.form.new_status.value='CANCELLED';">
                                Cancel Reminder
                            </button>

                            <input type="hidden" name="new_status" value="">

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
$app_stmt->close();
$stmt->close();
?>
