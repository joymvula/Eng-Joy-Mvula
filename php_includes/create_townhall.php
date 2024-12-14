<?php
session_start();
require 'db.php';

// Ensure the user is an official
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'official') {
    echo "Access denied!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get town hall data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $meeting_date = $_POST['meeting_date'];
    $location_url = $_POST['location_url']; // URL for virtual meeting
    $created_by = $_SESSION['user_id'];

    // Insert town hall data into the database
    $stmt = $pdo->prepare("INSERT INTO town_hall_meetings (title, description, meeting_date, location_url, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $meeting_date, $location_url, $created_by]);

    $meeting_id = $pdo->lastInsertId();

    // Notify all citizens about the new poll
    $citizens_stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'citizen'");
    $citizens = $citizens_stmt->fetchAll(PDO::FETCH_ASSOC);

    $notification_message = "A new townhall meeting titled '{$title}' has been created. Participate now!";
    $notification_link = "view_townhalls.php?";
    $notification_type = "townhall";

    foreach ($citizens as $citizen) {
        $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message, link_url, `type`) VALUES (?, ?, ?, ?)");
        $notification_stmt->execute([$citizen['user_id'], $notification_message, $notification_link, $notification_type]);
    }


    echo "Town hall created successfully!";
    // Redirect to town hall list
    header('Location: view_townhalls.php');
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
                <div class="panel-section create-townhall">
                    <h1>Create a New Town Hall Meeting</h1>
                    <br>
                    <form action="create_townhall.php" method="post">
                        <input type="text" name="title" placeholder="Meeting Title" required>
                        <textarea name="description" placeholder="Meeting Description" rows="8" required></textarea>
                        <div class="input-box">
                            <label for="">Starts On:</label>
                            <input type="datetime-local" name="meeting_date" required>
                        </div>
                        <input type="url" name="location_url" placeholder="Virtual Meeting URL" required>
                        <button type="submit" class="btn-submit short">Create Town Hall</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
