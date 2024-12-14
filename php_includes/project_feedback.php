<?php
session_start();
require 'db.php';
include 'functions.php';

// Check if the project ID is provided in the URL
if (!isset($_GET['project_id'])) {
    $error_msg = "No Project Selected";
    $redirect_link = "view_projects.php";
    header("Location: error.php?msg=$error_msg&redirect=$redirect_link");
    exit();
}

$project_id = $_GET['project_id'];

// Fetch project details
$stmt = $pdo->prepare("SELECT * FROM projects JOIN users ON projects.created_by = users.user_id WHERE project_id = ?");

$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the user role (assuming session stores this information)
$role = $_SESSION['role'];

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feedback'])) {
    $user_id = $_SESSION['user_id'];
    $feedback = $_POST['feedback'];

    // Insert feedback into the database
    $stmt = $pdo->prepare("INSERT INTO project_feedback (project_id, user_id, feedback) VALUES (?, ?, ?)");
    $stmt->execute([$project_id, $user_id, $feedback]);

    echo "Feedback submitted successfully!";
    // Redirect to avoid form resubmission
    header("Location: project_feedback.php?project_id=$project_id");
    exit;
}

// Fetch feedback for this project
$stmt = $pdo->prepare("SELECT feedback, users.first_name, users.last_name, submission_date FROM project_feedback JOIN users ON project_feedback.user_id = users.user_id WHERE project_feedback.project_id = ?");
$stmt->execute([$project_id]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/fontawesome.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/regular.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/solid.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/brands.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/svg-with-js.css">
</head>
<body>
    <div class="main-container">
        <?php include 'user.php';?>
        <div class="display-section">
            <?php include 'side_panel.php';?>
            <div class="main-panel">         
                <div class="panel-section projects">
                <div class="post">
                    <div class="post-head">
                        <i class="fa fa-user-circle circle"></i>
                        <h4><?php echo htmlspecialchars($project['first_name']) . ' ' . htmlspecialchars($project['last_name']); ?></h4>
                    </div>
                </div>    
                <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                <?php
                echo nl2br(htmlspecialchars($project['description']));
                ?>
                <br>
                <br>
                <h3>Details</h3>
                <p>Starts <span class="highlight"><?php echo htmlspecialchars($project['start_date']); ?></span></p>
                <p>Expected to End <span class="highlight"><?php echo htmlspecialchars($project['end_date']); ?></span></p>
                <p>Is Currently <span class="highlight"><?php echo htmlspecialchars($project['status']); ?></span></p>
                <hr>
                <br>
                <br>
                <div class="add-comment">
                    <!-- Feedback form for citizens -->
                    <?php if ($role == 'citizen'): ?>
                        <form id="feedback-form" action="project_feedback.php?project_id=<?php echo $project_id; ?>" method="post">
                            <div class="flex">
                                <textarea name="feedback" id="feedback-textarea" rows="3" placeholder="Add a comment" required></textarea>
                                <p><input type="submit" value="Comment" class="btn-submit" style="width: fit-content;"></p>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="interactions">
                    <h3>Comments</h3>
                    <?php if (!empty($feedbacks)): ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="comment">
                                <div class="post-by">
                                    <i class="fa fa-user-circle circle"></i>
                                    <h4><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?><br><small class="highlight"><?php echo timeAgo($feedback['submission_date'])?></small></h4>
                                </div>
                                <div class="bubble">
                                <?php
                                    $comment_paragraphs = explode("\n", $feedback['feedback']);
                                    foreach ($comment_paragraphs as $cp) {
                                        if (trim($cp) !== '') {  // Ignore empty lines
                                            echo "<p>" . htmlspecialchars($cp) . "</p>";
                                        }
                                    }?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No comments yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>