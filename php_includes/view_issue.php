<?php
session_start();
require 'db.php';
include 'functions.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user role
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Check if issue_id is provided
if (!isset($_GET['issue_id'])) {
    $error_msg = "No Issue Selected";
    $redirect_link = "view_issues.php";
    header("Location: error.php?msg={$error_msg}&redirect={$redirect_link}");
    exit();
}

$issue_id = $_GET['issue_id'];

// Fetch the specific issue
$stmt = $pdo->prepare("SELECT public_issues.*, users.first_name, users.last_name FROM public_issues JOIN users ON public_issues.user_id = users.user_id WHERE public_issues.issue_id = ?");
$stmt->execute([$issue_id]);
$issue = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$issue) {
    // Redirect if no issue is found
    header('Location: view_issues.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($issue['issue_title']); ?></title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <div class="main-container">
        <?php include 'user.php';?>
        <div class="display-section">
            <?php include 'side_panel.php';?>
            <div class="main-panel">
                <div class="panel-section issue-details">
                    <div class="post">
                        <div class="post-head">
                            <i class="fa fa-user-circle circle"></i>
                            <h4><?php echo htmlspecialchars($issue['first_name']) . ' ' . htmlspecialchars($issue['last_name']); ?> | <small class="highlight"><?php echo  timeAgo($issue['report_date'])?></small></h4>
                        </div>
                    </div> 
                    <h1><?php echo htmlspecialchars($issue['issue_title']); ?></h1>
                    <p><?php echo nl2br(htmlspecialchars($issue['issue_description'])); ?></p>
                    <br>
                    <p>Status: <span class="highlight"><?php echo htmlspecialchars($issue['status']); ?></span></p>
                    <hr>
                    
                    <?php if ($user_role == 'official'): ?>
                        <!-- Link for officials to update the status -->
                        <a href="update_issue.php?issue_id=<?php echo $issue['issue_id']; ?>" class="btn-submit"><i class="fa fa-pencil"></i> Update Status</a>
                    <?php endif; ?>

                    <?php if ($user_role == 'moderator'): ?>
                        <!-- Moderator actions: delete or mark as inappropriate -->
                        <form action="view_issues.php" method="post" style="display:inline;">
                            <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                            <button type="submit" name="delete_issue" class="btn-submit-outline short" onclick="return confirm('Are you sure you want to delete this issue?');">
                                <i class="fa fa-trash"></i> Delete Issue
                            </button>
                            <button type="submit" name="mark_inappropriate" class="btn-submit short">
                                <i class="fa fa-warning"></i> Mark as Inappropriate
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="view_issues.php" class="btn-submit-outline">Back to Issues</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
