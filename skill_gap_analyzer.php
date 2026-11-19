<?php
require_once "includes/auth.php";
require_once "config/db.php";

$user_id = (int)$_SESSION['user_id'];
$resume_id = filter_var($_GET['resume_id'] ?? '', FILTER_VALIDATE_INT);
$job_id = filter_var($_GET['job_id'] ?? '', FILTER_VALIDATE_INT);
$error = '';
$result = null;

// Only show resumes owned by the signed-in user.
$resume_stmt = $conn->prepare(
    "SELECT resume_id, resume_name, version_number, is_active
     FROM Resumes WHERE user_id = ?
     ORDER BY is_active DESC, resume_name, version_number DESC"
);
$resume_stmt->bind_param("i", $user_id);
$resume_stmt->execute();
$resumes = $resume_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$resume_stmt->close();

$jobs = $conn->query(
    "SELECT job_id, job_title, department, job_description
     FROM JobPostings
     WHERE job_status = 'OPEN'
       AND (application_deadline IS NULL OR application_deadline >= CURDATE())
     ORDER BY posting_date DESC"
);

if ($resume_id && $job_id) {
    // Validate that the selected resume belongs to this user.
    $owner_check = $conn->prepare(
        "SELECT resume_id FROM Resumes WHERE resume_id = ? AND user_id = ?"
    );
    $owner_check->bind_param("ii", $resume_id, $user_id);
    $owner_check->execute();
    $owner_check->store_result();
    $owns_resume = $owner_check->num_rows === 1;
    $owner_check->close();

    $job_stmt = $conn->prepare(
        "SELECT job_id, job_title, department, job_description
         FROM JobPostings
         WHERE job_id = ? AND job_status = 'OPEN'
           AND (application_deadline IS NULL OR application_deadline >= CURDATE())"
    );
    $job_stmt->bind_param("i", $job_id);
    $job_stmt->execute();
    $job = $job_stmt->get_result()->fetch_assoc();
    $job_stmt->close();

    if (!$owns_resume) {
        $error = "Please select one of your own resumes.";
    } elseif (!$job) {
        $error = "The selected job is unavailable or no longer open.";
    } else {
        $skill_rows = $conn->query(
            "SELECT skill_id, skill_name, skill_category FROM Skills ORDER BY skill_name"
        )->fetch_all(MYSQLI_ASSOC);

        $user_skills_stmt = $conn->prepare(
            "SELECT s.skill_id, s.skill_name, s.skill_category, rs.proficiency
             FROM ResumeSkills rs
             INNER JOIN Skills s ON s.skill_id = rs.skill_id
             WHERE rs.resume_id = ?"
        );
        $user_skills_stmt->bind_param("i", $resume_id);
        $user_skills_stmt->execute();
        $user_skills = $user_skills_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $user_skills_stmt->close();

        $user_skill_ids = array_fill_keys(array_map(
            static fn($item) => (int)$item['skill_id'], $user_skills
        ), true);

        // Detect catalogued skills mentioned in the job title/department/description.
        $job_text = strtolower(
            ($job['job_title'] ?? '') . ' ' . ($job['department'] ?? '') . ' ' .
            ($job['job_description'] ?? '')
        );
        $required = [];
        foreach ($skill_rows as $skill) {
            $needle = strtolower($skill['skill_name']);
            $pattern = '/(?<![a-z0-9+#])' . preg_quote($needle, '/') . '(?![a-z0-9+#])/i';
            if (preg_match($pattern, $job_text)) {
                $required[] = $skill;
            }
        }

        $matched = [];
        $missing = [];
        foreach ($required as $skill) {
            if (isset($user_skill_ids[(int)$skill['skill_id']])) {
                $matched[] = $skill;
            } else {
                $missing[] = $skill;
            }
        }
        $total = count($required);
        $score = $total > 0 ? (int)round(count($matched) / $total * 100) : null;
        $result = [
            'job' => $job,
            'required' => $required,
            'matched' => $matched,
            'missing' => $missing,
            'score' => $score,
            'user_skills' => $user_skills
        ];
    }
}

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Gap Analyzer | Resume &amp; Job Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .analyzer-wrap{max-width:900px!important}
        .analyzer-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
        .analyzer-panel{padding:18px;margin:18px 0;background:#f7f8fc;border:1px solid #e1e5ef;border-radius:10px}
        .analyzer-panel h2{color:#243b6b;margin:0 0 12px}
        .analyzer-panel select{width:100%;padding:12px;border:1px solid #ccc;border-radius:6px;background:#fff}
        .skill-chip{display:inline-block;padding:7px 11px;margin:5px 5px 0 0;border-radius:20px;font-size:13px;background:#e8f5ed;color:#17643a}
        .skill-chip.missing{background:#fff0ed;color:#a33125}
        .score{font-size:36px;font-weight:700;color:#243b6b;margin:5px 0}
        .muted{color:#667085;font-size:14px}
        .analyzer-link{display:inline-block;margin:12px 0;padding:10px 16px;background:#3156a3;color:#fff!important;text-decoration:none;border-radius:6px}
        @media(max-width:600px){.analyzer-wrap{padding:22px!important}}
    </style>
</head>
<body>
<div class="register-container">
  <div class="register-card analyzer-wrap">
    <h1>Skill Gap Analyzer</h1>
    <p class="subtitle">Compare the skills on one of your resumes with the skills mentioned in a job listing.</p>

    <?php if ($error !== ''): ?><div class="error-message"><?= h($error) ?></div><?php endif; ?>

    <?php if (count($resumes) === 0): ?>
      <p>You need to upload a resume and add skills to it before running an analysis.</p>
      <a class="analyzer-link" href="resume.php">Manage Resumes</a>
      <a class="analyzer-link" href="skills.php">Add Resume Skills</a>
    <?php elseif ($jobs->num_rows === 0): ?>
      <p>There are currently no open jobs available to analyze.</p>
    <?php else: ?>
      <form method="GET" action="skill_gap_analyzer.php" class="analyzer-panel">
        <div class="analyzer-grid">
          <div>
            <label for="resume_id">Choose Your Resume</label>
            <select id="resume_id" name="resume_id" required>
              <option value="">Select a resume</option>
              <?php foreach ($resumes as $resume): ?>
                <option value="<?= (int)$resume['resume_id'] ?>" <?= $resume_id === (int)$resume['resume_id'] ? 'selected' : '' ?>>
                  <?= h($resume['resume_name']) ?> — v<?= (int)$resume['version_number'] ?><?= $resume['is_active'] ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="job_id">Choose a Job</label>
            <select id="job_id" name="job_id" required>
              <option value="">Select a job</option>
              <?php while ($job_option = $jobs->fetch_assoc()): ?>
                <option value="<?= (int)$job_option['job_id'] ?>" <?= $job_id === (int)$job_option['job_id'] ? 'selected' : '' ?>>
                  <?= h($job_option['job_title']) ?><?= $job_option['department'] ? ' — ' . h($job_option['department']) : '' ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <button type="submit" style="margin-top:18px">Analyze Skill Gap</button>
      </form>
    <?php endif; ?>

    <?php if ($result !== null): ?>
      <section class="analyzer-panel">
        <h2>Analysis for <?= h($result['job']['job_title']) ?></h2>
        <?php if ($result['score'] === null): ?>
          <p>No skills from the current Skills catalogue were detected in this job description. Add relevant skills to the catalogue or update the job description for a meaningful comparison.</p>
        <?php else: ?>
          <div class="score"><?= $result['score'] ?>%</div>
          <p class="muted">Skill match: <?= count($result['matched']) ?> of <?= count($result['required']) ?> detected job skills</p>
          <div style="height:12px;background:#e5e7eb;border-radius:20px;overflow:hidden;margin:12px 0 20px">
            <div style="height:100%;width:<?= $result['score'] ?>%;background:#3156a3;border-radius:20px"></div>
          </div>
        <?php endif; ?>

        <h3 style="color:#243b6b">Matched Skills (<?= count($result['matched']) ?>)</h3>
        <?php if (!$result['matched']): ?><p class="muted">No matched skills yet.</p><?php endif; ?>
        <?php foreach ($result['matched'] as $skill): ?><span class="skill-chip"><?= h($skill['skill_name']) ?></span><?php endforeach; ?>

        <h3 style="color:#a33125;margin-top:22px">Skills to Develop (<?= count($result['missing']) ?>)</h3>
        <?php if (!$result['missing']): ?><p class="muted">Great! No gaps were detected from the available skill catalogue.</p><?php endif; ?>
        <?php foreach ($result['missing'] as $skill): ?><span class="skill-chip missing"><?= h($skill['skill_name']) ?><?= $skill['skill_category'] ? ' · ' . h($skill['skill_category']) : '' ?></span><?php endforeach; ?>

        <h3 style="color:#243b6b;margin-top:22px">Your Resume Skills</h3>
        <?php if (!$result['user_skills']): ?><p class="muted">No skills are assigned to this resume. Add them on the My Skills page.</p><?php endif; ?>
        <?php foreach ($result['user_skills'] as $skill): ?><span class="skill-chip"><?= h($skill['skill_name']) ?><?= $skill['proficiency'] ? ' · ' . h(ucfirst(strtolower($skill['proficiency']))) : '' ?></span><?php endforeach; ?>
      </section>
      <p class="muted">This is a keyword-based estimate using the skills currently stored in your database. It does not read or interpret the PDF itself, and it may not identify synonyms or skills missing from the catalogue.</p>
    <?php endif; ?>

    <p class="login-link"><a href="skills.php">Back to My Skills</a> &nbsp;|&nbsp; <a href="dashboard.php">Back to Dashboard</a></p>
  </div>
</div>
<?php include __DIR__ . '/includes/accessibility_widget.php'; ?>
</body>
</html>
