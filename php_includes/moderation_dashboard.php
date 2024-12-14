<?php
include 'db.php';
session_start();

// Ensure the user is a moderator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'moderator') {
    header('Location: login.php');
    exit;
}

// Get user info from session
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$role = $_SESSION['role'];

// Count unread notifications
$unread_stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$unread_stmt->execute([$_SESSION['user_id']]);
$unread_notifications = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

// Fetch metrics
$feedback_count = $pdo->query("SELECT COUNT(*) FROM feedback_suggestions")->fetchColumn();
$poll_count = $pdo->query("SELECT COUNT(*) FROM polls")->fetchColumn();
$issue_count = $pdo->query("SELECT COUNT(*) FROM public_issues")->fetchColumn();
$completed_polls = $pdo->query("SELECT COUNT(*) FROM polls WHERE closing_date < NOW()")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/fontawesome.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/regular.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/solid.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/brands.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/svg-with-js.css">
</head>
<body>
    <div class="main-container">
        <?php include 'user.php'; ?>
        <div class="display-section">
            <?php include 'side_panel.php'; ?>
            <div class="main-panel">
                <div class="panel-section welcome-user showing">
                    <h1>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>!</h1>
                    <p>Role: <strong><?php echo htmlspecialchars($role); ?></strong></p>
                    <p>Welcome to your Moderator Dashboard! Here, you can efficiently manage reported issues and citizen feedback. Your role is essential in ensuring every voice is heard and addressing community concerns. Let’s work together to foster a responsive and engaged community!</p>
                    <hr>
                    <br>
                    <?php include 'reporting_dashboard.php' ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
