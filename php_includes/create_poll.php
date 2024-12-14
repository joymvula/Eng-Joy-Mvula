<?php
session_start();
require 'db.php';

// Ensure the user is an official
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'official') {
    echo "Access denied!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get poll data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $closing_date = $_POST['closing_date'];
    $created_by = $_SESSION['user_id'];

    // Insert poll into database
    $stmt = $pdo->prepare("INSERT INTO polls (title, description, closing_date, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $closing_date, $created_by]);

    // Get the poll ID of the newly created poll
    $poll_id = $pdo->lastInsertId();

    // Notify all citizens about the new poll
    $citizens_stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'citizen'");
    $citizens = $citizens_stmt->fetchAll(PDO::FETCH_ASSOC);

    $notification_message = "A new poll titled '{$title}' has been created. Participate now!";
    $notification_link = "view_poll.php?poll_id={$poll_id}";
    $notification_type = "poll";

    foreach ($citizens as $citizen) {
        $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message, link_url, `type`) VALUES (?, ?, ?, ?)");
        $notification_stmt->execute([$citizen['user_id'], $notification_message, $notification_link, $notification_type]);
    }

    echo "Poll created successfully!";
    // Redirect to the poll list
    header('Location: view_polls.php');
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
                <div class="panel-section create-poll">
                    <h1>Create a New Poll</h1>
                    <br>
                    <form action="create_poll.php" method="post">
                        <input type="text" name="title" placeholder="Poll Title" required>
                        <textarea name="description" placeholder="Poll Description" rows="6" required></textarea>
                        <div class="input-box">
                            <label for="">Closes On:</label>
                            <input type="datetime-local" name="closing_date" required>
                        </div>
                        <button type="submit" class="btn-submit short">Create Poll</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>