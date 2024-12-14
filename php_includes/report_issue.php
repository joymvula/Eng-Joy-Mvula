<?php
session_start();
require 'db.php';

// Ensure the user is logged in and is a citizen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'citizen') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $issue_title = $_POST['issue_title'];
    $issue_description = $_POST['issue_description'];
    $user_id = $_SESSION['user_id'];

    // Insert issue into the database with the status 'open'
    $stmt = $pdo->prepare("INSERT INTO public_issues (user_id, issue_title, issue_description, status) VALUES (?, ?, ?, 'open')");
    $stmt->execute([$user_id, $issue_title, $issue_description]);

    // Fetch all government officials to notify about the new issue
    $officials_stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'official'");
    $officials = $officials_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the inserted issue's issue_id
    $issue_id = $pdo->lastInsertId();

    // Create notifications for each official
    $notification_message = "New public issue reported: '{$issue_title}'.";
    $notification_link = "view_issue.php?issue_id={$issue_id}";
    $notification_type = "issue";

    foreach ($officials as $official) {
        $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message, link_url, `type`) VALUES (?, ?, ?, ?)");
        $notification_stmt->execute([$official['user_id'], $notification_message, $notification_link, $notification_type]);
    };


    echo "Issue reported successfully!";
    header('Location: view_issues.php');
    exit;
}
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
                <div class="panel-section report-issue">
                    <h1>Report a Public Issue</h1>
                    <p>
                    Help us improve our community by reporting public issues you encounter. Whether it’s a concern about infrastructure, safety, or local services, your observations are invaluable in identifying areas that need attention. Use this platform to share details about the issue, including its location and any relevant information. Together, we can work towards solutions and ensure a better living environment for everyone. Your voice matters—let us know how we can help!
                    </p>
                    <br>
                    <form action="report_issue.php" method="post">
                        <input type="text" name="issue_title" placeholder="Issue Title" required>
                        <textarea name="issue_description" placeholder="Issue Description" rows="8" required></textarea>
                        <button type="submit" class="btn-submit short">Report Issue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
