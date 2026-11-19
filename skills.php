
<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];

$message = "";
$error = "";

if (empty($_SESSION['skills_csrf'])) {
    $_SESSION['skills_csrf'] = bin2hex(random_bytes(32));
}

// Handle skill assignment and removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['skills_csrf'], $token)) {
        $error = "Invalid request. Please refresh the page.";

    } elseif (isset($_POST['add_skill'])) {

        $resume_id = filter_var(
            $_POST['resume_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        $skill_id = filter_var(
            $_POST['skill_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        $proficiency = $_POST['proficiency'] ?? '';

        $valid_levels = [
            'BEGINNER',
            'INTERMEDIATE',
            'ADVANCED',
            'EXPERT'
        ];

        if (
            !$resume_id || !$skill_id ||
            !in_array($proficiency, $valid_levels, true)
        ) {
            $error = "Please complete all fields correctly.";

        } else {

            // Confirm the resume belongs to the logged-in user
            $check = $conn->prepare(
                "SELECT resume_id
                 FROM Resumes
                 WHERE resume_id = ? AND user_id = ?"
            );

            $check->bind_param("ii", $resume_id, $user_id);
            $check->execute();
            $check->store_result();

            $owns_resume = $check->num_rows === 1;
            $check->close();

            if (!$owns_resume) {
                $error = "Invalid resume selection.";

            } else {

                // Confirm the selected skill exists
                $skill_check = $conn->prepare(
                    "SELECT skill_id FROM Skills WHERE skill_id = ?"
                );

                $skill_check->bind_param("i", $skill_id);
                $skill_check->execute();
                $skill_check->store_result();

                $skill_exists = $skill_check->num_rows === 1;
                $skill_check->close();

                if (!$skill_exists) {
                    $error = "Selected skill does not exist.";

                } else {

                    $insert = $conn->prepare(
                        "INSERT INTO ResumeSkills
                         (resume_id, skill_id, proficiency)
                         VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE
                         proficiency = VALUES(proficiency)"
                    );

                    $insert->bind_param(
                        "iis",
                        $resume_id,
                        $skill_id,
                        $proficiency
                    );

                    if ($insert->execute()) {
                        $message = "Skill added or updated successfully.";
                        $_SESSION['skills_csrf'] =
                            bin2hex(random_bytes(32));
                    } else {
                        $error = "Unable to save the selected skill.";
                    }

                    $insert->close();
                }
            }
        }

    } elseif (isset($_POST['remove_skill'])) {

        $resume_id = filter_var(
            $_POST['resume_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        $skill_id = filter_var(
            $_POST['skill_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        if (!$resume_id || !$skill_id) {
            $error = "Invalid skill selection.";

        } else {

            // Remove only from a resume owned by this user
            $delete = $conn->prepare(
                "DELETE rs
                 FROM ResumeSkills rs
                 INNER JOIN Resumes r
                    ON rs.resume_id = r.resume_id
                 WHERE rs.resume_id = ?
                   AND rs.skill_id = ?
                   AND r.user_id = ?"
            );

            $delete->bind_param(
                "iii",
                $resume_id,
                $skill_id,
                $user_id
            );

            if ($delete->execute() && $delete->affected_rows > 0) {
                $message = "Skill removed successfully.";
                $_SESSION['skills_csrf'] =
                    bin2hex(random_bytes(32));
            } else {
                $error = "Skill could not be removed.";
            }

            $delete->close();
        }
    }
}

// Fetch this user's resumes
$resume_stmt = $conn->prepare(
    "SELECT resume_id, resume_name, version_number, is_active
     FROM Resumes
     WHERE user_id = ?
     ORDER BY is_active DESC, resume_name ASC, version_number DESC"
);

$resume_stmt->bind_param("i", $user_id);
$resume_stmt->execute();
$resumes = $resume_stmt->get_result();

$resume_options = [];

while ($resume = $resumes->fetch_assoc()) {
    $resume_options[] = $resume;
}

$resume_stmt->close();

// Fetch available skills
$skills = $conn->query(
    "SELECT skill_id, skill_name, skill_category
     FROM Skills
     ORDER BY skill_category, skill_name"
);

// Fetch skills assigned to this user's resumes
$assigned_stmt = $conn->prepare(
    "SELECT
        rs.resume_id,
        rs.skill_id,
        rs.proficiency,
        s.skill_name,
        s.skill_category,
        r.resume_name,
        r.version_number
     FROM ResumeSkills rs
     INNER JOIN Skills s
        ON rs.skill_id = s.skill_id
     INNER JOIN Resumes r
        ON rs.resume_id = r.resume_id
     WHERE r.user_id = ?
     ORDER BY r.resume_name, s.skill_name"
);

$assigned_stmt->bind_param("i", $user_id);
$assigned_stmt->execute();
$assigned_skills = $assigned_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Skills | Resume & Job Tracker</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-card" style="max-width:850px;">

        <h1>My Skills</h1>

        <p class="subtitle">
            Manage the skills associated with your resumes
        </p>

        <p style="margin:16px 0;">
            <a href="skill_gap_analyzer.php" style="display:inline-block;padding:10px 16px;background:#3156a3;color:white;text-decoration:none;border-radius:6px;">
                Open Skill Gap Analyzer
            </a>
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
            Add a Skill to Your Resume
        </h2>

        <?php if (count($resume_options) === 0): ?>

            <p>
                You have no saved resumes yet. Upload a resume first
                before assigning skills.
            </p>

            <p>
                <a href="resume.php">Go to Resume Management</a>
            </p>

        <?php elseif ($skills->num_rows === 0): ?>

            <p>No skills are currently available in the database.</p>

        <?php else: ?>

            <form method="POST" action="skills.php">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['skills_csrf']) ?>">

                <label for="resume_id">Select Resume</label>

                <select id="resume_id" name="resume_id"
                        required
                        style="width:100%; padding:12px;
                               border:1px solid #ccc;
                               border-radius:6px;">

                    <option value="">Choose a resume</option>

                    <?php foreach ($resume_options as $resume): ?>

                        <option value="<?= (int)$resume['resume_id'] ?>">

                            <?= htmlspecialchars($resume['resume_name']) ?>

                            - Version <?= (int)$resume['version_number'] ?>

                            <?= $resume['is_active'] ? '(Active)' : '' ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <label for="skill_id">Select Skill</label>

                <select id="skill_id" name="skill_id"
                        required
                        style="width:100%; padding:12px;
                               border:1px solid #ccc;
                               border-radius:6px;">

                    <option value="">Choose a skill</option>

                    <?php while ($skill = $skills->fetch_assoc()): ?>

                        <option value="<?= (int)$skill['skill_id'] ?>">

                            <?= htmlspecialchars($skill['skill_name']) ?>

                            <?= $skill['skill_category']
                                ? ' - ' . htmlspecialchars($skill['skill_category'])
                                : '' ?>

                        </option>

                    <?php endwhile; ?>

                </select>

                <label for="proficiency">Proficiency Level</label>

                <select id="proficiency" name="proficiency"
                        required
                        style="width:100%; padding:12px;
                               border:1px solid #ccc;
                               border-radius:6px;">

                    <option value="">Choose proficiency</option>
                    <option value="BEGINNER">Beginner</option>
                    <option value="INTERMEDIATE">Intermediate</option>
                    <option value="ADVANCED">Advanced</option>
                    <option value="EXPERT">Expert</option>

                </select>

                <button type="submit" name="add_skill">
                    Add Skill
                </button>

            </form>

        <?php endif; ?>

        <hr style="margin:25px 0; border:0;
                   border-top:1px solid #ddd;">

        <h2 style="color:#243b6b; margin-bottom:15px;">
            Skills Assigned to Your Resumes
        </h2>

        <?php if ($assigned_skills->num_rows === 0): ?>

            <p>No skills have been assigned to your resumes yet.</p>

        <?php else: ?>

            <?php while ($assigned = $assigned_skills->fetch_assoc()): ?>

                <div style="padding:16px;
                            margin-bottom:12px;
                            background:#f7f8fc;
                            border:1px solid #ddd;
                            border-radius:8px;">

                    <h3 style="color:#243b6b;">
                        <?= htmlspecialchars($assigned['skill_name']) ?>
                    </h3>

                    <p>
                        <strong>Resume:</strong>
                        <?= htmlspecialchars($assigned['resume_name']) ?>
                        (Version <?= (int)$assigned['version_number'] ?>)
                    </p>

                    <p>
                        <strong>Category:</strong>
                        <?= htmlspecialchars(
                            $assigned['skill_category'] ?? 'Not specified'
                        ) ?>
                    </p>

                    <p>
                        <strong>Proficiency:</strong>
                        <?= htmlspecialchars($assigned['proficiency'] ?? 'Not specified') ?>
                    </p>

                    <form method="POST" action="skills.php"
                          onsubmit="return confirm('Remove this skill from the resume?');">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['skills_csrf']) ?>">

                        <input type="hidden"
                               name="resume_id"
                               value="<?= (int)$assigned['resume_id'] ?>">

                        <input type="hidden"
                               name="skill_id"
                               value="<?= (int)$assigned['skill_id'] ?>">

                        <button type="submit"
                                name="remove_skill"
                                style="background:#b42318;">
                            Remove Skill
                        </button>

                    </form>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

        <p class="login-link">
            <a href="resume.php">My Resumes</a>
            &nbsp; | &nbsp;
            <a href="dashboard.php">Back to Dashboard</a>
        </p>

    </div>

</div>

</body>
</html>

<?php
$assigned_stmt->close();
?>
