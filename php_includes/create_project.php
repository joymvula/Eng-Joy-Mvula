<?php
session_start();
require 'db.php';

// Ensure the user is an official
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'official') {
    echo "Access denied!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get project data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $created_by = $_SESSION['user_id'];

    // Insert project into the database
    $stmt = $pdo->prepare("INSERT INTO projects (title, description, start_date, end_date, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $start_date, $end_date, $status, $created_by]);

    // Get the project ID of the newly created project
    $project_id = $pdo->lastInsertId();

    // Notify all citizens about the new project
    $citizens_stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'citizen'");
    $citizens = $citizens_stmt->fetchAll(PDO::FETCH_ASSOC);

    $notification_message = "A new project titled '{$title}' has been added.";
    $notification_link = "project_feedback.php?project_id={$project_id}";
    $notification_type = "project";

    foreach ($citizens as $citizen) {
        $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message, link_url, `type`) VALUES (?, ?, ?, ?)");
        $notification_stmt->execute([$citizen['user_id'], $notification_message, $notification_link, $notification_type]);
    }

    echo "Project created successfully!";
    // Redirect to view projects
    header('Location: government_dashboard.php');
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
                <div class="panel-section create-project">
                    <h1>Create a New Project</h1>
                    <p>Ready to bring your ideas to life? Start a new project and make an impact in your community! Our platform makes it easy to set goals, collaborate with others, and track your progress every step of the way.</p>
                    <br>
                    <form action="create_project.php" method="post">
                        <input type="text" name="title" placeholder="Project Title" required>
                        <textarea name="description" placeholder="Project Description" required rows="8"></textarea>
                        <br>
                        <div class="input-box">
                            <label for="">Starts On:</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="input-box">
                            <label for="">Ends On:</label>
                            <input type="date" name="end_date" required>
                        </div>
                        <br>
                        <div class="input-box">
                            <label for="">Status:</label>
                            <select name="status" required>
                                <option value="in progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="delayed">Delayed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit short">Create Project</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
