
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int) $_SESSION['user_id'];

$message = "";
$error = "";

// CSRF protection
if (empty($_SESSION['resume_csrf'])) {
    $_SESSION['resume_csrf'] = bin2hex(random_bytes(32));
}

// Handle PDF upload
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['resume_csrf'], $_POST['csrf_token'])
    ) {
        $error = "Invalid form submission. Please refresh and try again.";

    } elseif (
        !isset($_FILES['resume_file']) ||
        $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK
    ) {
        $error = "Please select a PDF file to upload.";

    } else {

        $resume_name = trim($_POST['resume_name'] ?? "");
        $file = $_FILES['resume_file'];
        $make_active = isset($_POST['make_active']);

        if ($resume_name === "" || strlen($resume_name) > 100) {
            $error = "Resume name is required and must be 100 characters or fewer.";

        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = "The PDF file must not exceed 5 MB.";

        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $error = "Invalid uploaded file.";

        } else {

            $extension = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $handle = fopen($file['tmp_name'], "rb");
            $signature = $handle ? fread($handle, 5) : "";
            if ($handle) {
                fclose($handle);
            }

            if (
                $extension !== "pdf" ||
                $mime !== "application/pdf" ||
                $signature !== "%PDF-"
            ) {
                $error = "Only valid PDF files are allowed.";

            } else {

                // Create a private folder for this user
                $user_folder = __DIR__ . "/uploads/user_" . $user_id;

                if (
                    !is_dir($user_folder) &&
                    !mkdir($user_folder, 0750, true)
                ) {
                    $error = "Unable to create the upload folder.";
                } else {

                    // Find the next version for this resume name
                    $version_stmt = $conn->prepare(
                        "SELECT COALESCE(MAX(version_number), 0) AS max_version
                         FROM Resumes
                         WHERE user_id = ? AND resume_name = ?"
                    );

                    $version_stmt->bind_param(
                        "is",
                        $user_id,
                        $resume_name
                    );

                    $version_stmt->execute();
                    $version_result = $version_stmt->get_result();
                    $version_row = $version_result->fetch_assoc();
                    $version = (int)$version_row['max_version'] + 1;
                    $version_stmt->close();

                    // Make the first resume active automatically
                    $active_stmt = $conn->prepare(
                        "SELECT COUNT(*) AS active_count
                         FROM Resumes
                         WHERE user_id = ? AND is_active = 1"
                    );

                    $active_stmt->bind_param("i", $user_id);
                    $active_stmt->execute();
                    $active_result = $active_stmt->get_result();
                    $active_row = $active_result->fetch_assoc();
                    $active_stmt->close();

                    $is_active = $make_active ||
                        ((int)$active_row['active_count'] === 0);

                    // Generate a random filename
                    $stored_filename =
                        bin2hex(random_bytes(16)) . ".pdf";

                    $absolute_path =
                        $user_folder . "/" . $stored_filename;

                    $relative_path =
                        "uploads/user_" . $user_id . "/" .
                        $stored_filename;

                    if (move_uploaded_file(
                        $file['tmp_name'],
                        $absolute_path
                    )) {

                        try {
                            $conn->begin_transaction();

                            // Only one active resume per user
                            if ($is_active) {
                                $deactivate = $conn->prepare(
                                    "UPDATE Resumes
                                     SET is_active = 0
                                     WHERE user_id = ?"
                                );

                                $deactivate->bind_param("i", $user_id);
                                $deactivate->execute();
                                $deactivate->close();
                            }

                            $insert = $conn->prepare(
                                "INSERT INTO Resumes
                                 (user_id, resume_name, version_number,
                                  file_path, is_active)
                                 VALUES (?, ?, ?, ?, ?)"
                            );

                            $insert->bind_param(
                                "isisi",
                                $user_id,
                                $resume_name,
                                $version,
                                $relative_path,
                                $is_active
                            );

                            $insert->execute();
                            $insert->close();

                            $conn->commit();

                            $message =
                                "Resume uploaded successfully as version " .
                                $version . ".";

                            $_SESSION['resume_csrf'] =
                                bin2hex(random_bytes(32));

                        } catch (Throwable $e) {
                            $conn->rollback();
                            if (is_file($absolute_path)) {
                                unlink($absolute_path);
                            }
                            $error = "Unable to save the resume record.";
                        }

                    } else {
                        $error = "Unable to move the uploaded PDF file.";
                    }
                }
            }
        }
    }
}

// Fetch this user's resumes only
$list = $conn->prepare(
    "SELECT resume_id, resume_name, version_number,
            file_path, is_active, created_at
     FROM Resumes
     WHERE user_id = ?
     ORDER BY created_at DESC, resume_id DESC"
);

$list->bind_param("i", $user_id);
$list->execute();
$resumes = $list->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Resumes | Resume & Job Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">
    <div class="register-card">

        <h1>My Resumes</h1>

        <p class="subtitle">
            Upload and manage your PDF resumes
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

        <form method="POST" action="resume.php"
              enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['resume_csrf']) ?>"
            >

            <label for="resume_name">Resume Name</label>

            <input
                type="text"
                id="resume_name"
                name="resume_name"
                maxlength="100"
                placeholder="e.g. Software Engineer Resume"
                required
            >

            <label for="resume_file">Choose PDF Resume</label>

            <input
                type="file"
                id="resume_file"
                name="resume_file"
                accept=".pdf,application/pdf"
                required
            >

            <p style="font-size: 12px; color: #777; margin-top: 7px;">
                PDF only. Maximum file size: 5 MB.
            </p>

            <label style="display:flex; align-items:center; gap:8px;">
                <input
                    type="checkbox"
                    name="make_active"
                    value="1"
                    style="width:auto;"
                >
                Set this as my active resume
            </label>

            <button type="submit">Upload Resume</button>

        </form>

        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #ddd;">

        <h2 style="color:#243b6b; font-size:20px; margin-bottom:15px;">
            My Uploaded Resumes
        </h2>

        <?php if ($resumes->num_rows === 0): ?>

            <p>No resumes found. Upload your first PDF resume.</p>

        <?php else: ?>

            <?php while ($resume = $resumes->fetch_assoc()): ?>

                <div style="padding:15px 0; border-bottom:1px solid #ddd;">

                    <strong>
                        <?= htmlspecialchars($resume['resume_name']) ?>
                    </strong>

                    <p style="margin-top:7px;">
                        Version:
                        <?= (int)$resume['version_number'] ?>
                    </p>

                    <p>
                        Uploaded:
                        <?= htmlspecialchars($resume['created_at']) ?>
                    </p>

                    <p>
                        Status:
                        <?php if ($resume['is_active']): ?>
                            <span style="color:green; font-weight:bold;">
                                Active
                            </span>
                        <?php else: ?>
                            <span style="color:#777;">
                                Inactive
                            </span>
                        <?php endif; ?>
                    </p>

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
$list->close();
?>
