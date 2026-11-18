
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = $_SESSION['user_id'];

$message = "";
$error = "";

// Fetch existing profile
$stmt = $conn->prepare(
    "SELECT phone, location, education, experience_years,
            linkedin_url, portfolio_url
     FROM UserProfiles
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$profile = $result->fetch_assoc() ?: [];

$stmt->close();

// Handle profile submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $phone = trim($_POST['phone'] ?? "");
    $location = trim($_POST['location'] ?? "");
    $education = trim($_POST['education'] ?? "");
    $experience = trim($_POST['experience_years'] ?? "0");
    $linkedin = trim($_POST['linkedin_url'] ?? "");
    $portfolio = trim($_POST['portfolio_url'] ?? "");

    if ($experience === "" || !is_numeric($experience)
        || (float)$experience < 0 || (float)$experience > 999.9) {
        $error = "Please enter valid years of experience.";
    } elseif (strlen($phone) > 20 || strlen($location) > 100
        || strlen($education) > 150) {
        $error = "One or more fields exceed their allowed length.";
    } elseif (($linkedin !== "" && !filter_var($linkedin, FILTER_VALIDATE_URL))
        || ($portfolio !== "" && !filter_var($portfolio, FILTER_VALIDATE_URL))) {
        $error = "Please enter valid LinkedIn and portfolio URLs.";
    } elseif (strlen($linkedin) > 255 || strlen($portfolio) > 255) {
        $error = "Website URLs must not exceed 255 characters.";
    } else {

        $experience = (float)$experience;

        // Check whether a profile already exists
        $check = $conn->prepare(
            "SELECT profile_id FROM UserProfiles WHERE user_id = ?"
        );

        $check->bind_param("i", $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            // Update existing profile
            $update = $conn->prepare(
                "UPDATE UserProfiles
                 SET phone = ?, location = ?, education = ?,
                     experience_years = ?, linkedin_url = ?,
                     portfolio_url = ?
                 WHERE user_id = ?"
            );

            $update->bind_param(
                "sssdssi",
                $phone,
                $location,
                $education,
                $experience,
                $linkedin,
                $portfolio,
                $user_id
            );

            if ($update->execute()) {
                $message = "Your profile has been updated successfully.";
            } else {
                $error = "Unable to update your profile.";
            }

            $update->close();

        } else {

            // Insert new profile
            $insert = $conn->prepare(
                "INSERT INTO UserProfiles
                 (user_id, phone, location, education,
                  experience_years, linkedin_url, portfolio_url)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $insert->bind_param(
                "isssdss",
                $user_id,
                $phone,
                $location,
                $education,
                $experience,
                $linkedin,
                $portfolio
            );

            if ($insert->execute()) {
                $message = "Your profile has been saved successfully.";
            } else {
                $error = "Unable to save your profile.";
            }

            $insert->close();
        }

        $check->close();

        // Refresh displayed profile values
        if ($message !== "") {
            $profile = [
                'phone' => $phone,
                'location' => $location,
                'education' => $education,
                'experience_years' => $experience,
                'linkedin_url' => $linkedin,
                'portfolio_url' => $portfolio
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card">

        <h1>My Profile</h1>

        <p class="subtitle">
            Manage your personal and professional information
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

        <form method="POST" action="profile.php">

            <label for="phone">Phone Number</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                maxlength="20"
                placeholder="+1-555-123-4567"
                value="<?= htmlspecialchars($profile['phone'] ?? '') ?>"
            >

            <label for="location">Location</label>
            <input
                type="text"
                id="location"
                name="location"
                maxlength="100"
                placeholder="e.g. New York, NY"
                value="<?= htmlspecialchars($profile['location'] ?? '') ?>"
            >

            <label for="education">Education</label>
            <input
                type="text"
                id="education"
                name="education"
                maxlength="150"
                placeholder="e.g. BS in Computer Science"
                value="<?= htmlspecialchars($profile['education'] ?? '') ?>"
            >

            <label for="experience_years">Years of Experience</label>
            <input
                type="number"
                id="experience_years"
                name="experience_years"
                min="0"
                max="999.9"
                step="0.1"
                value="<?= htmlspecialchars((string)($profile['experience_years'] ?? '0')) ?>"
            >

            <label for="linkedin_url">LinkedIn Profile</label>
            <input
                type="url"
                id="linkedin_url"
                name="linkedin_url"
                maxlength="255"
                placeholder="https://www.linkedin.com/in/yourprofile"
                value="<?= htmlspecialchars($profile['linkedin_url'] ?? '') ?>"
            >

            <label for="portfolio_url">Portfolio Website</label>
            <input
                type="url"
                id="portfolio_url"
                name="portfolio_url"
                maxlength="255"
                placeholder="https://yourportfolio.com"
                value="<?= htmlspecialchars($profile['portfolio_url'] ?? '') ?>"
            >

            <button type="submit">Save Profile</button>

        </form>

        <p class="login-link">
            <a href="dashboard.php">Back to Dashboard</a>
        </p>

    </div>

</div>

</body>
</html>
