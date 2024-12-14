<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Insert feedback into the database
    $stmt = $pdo->prepare("INSERT INTO feedback_suggestions (user_id, title, description) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $description]);

    // Get the inserted feedback's suggestion_id
    $suggestion_id = $pdo->lastInsertId();

    // Fetch all government officials and moderators to notify
    $officials_stmt = $pdo->query("SELECT user_id FROM users WHERE role IN ('official', 'moderator')");
    $officials = $officials_stmt->fetchAll(PDO::FETCH_ASSOC);


    // Create notifications for each official and moderator
    $notification_message = "New feedback submitted: '{$title}'.";
    $notification_link = "comments.php?suggestion_id={$suggestion_id}";
    $notification_type = "suggestion";

    foreach ($officials as $official) {
        $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message, link_url, `type`) VALUES (?, ?, ?, ?)");
        $notification_stmt->execute([$official['user_id'], $notification_message, $notification_link, $notification_type]);
    };


    echo "Feedback submitted successfully!";
    // Redirect to view feedback
    header('Location: view_feedback.php');
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
                <div class="panel-section send-feedback">
                    <h1> Submit Feedback/Suggestion </h1>
                    <p> Share your thoughts and ideas by submitting feedback or suggestions here. Your input is invaluable in helping us improve our services and initiatives. We welcome your insights on how we can better meet the needs of our community!</p>
                    <form action="feedback.php" method="post">
                        <input type="text" name="title" placeholder="Feedback Title" required>
                        <textarea name="description" placeholder="Feedback Description" rows="8" required></textarea>
                        <button type="submit" class="btn-submit short">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
    
