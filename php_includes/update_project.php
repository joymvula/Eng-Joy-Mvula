<?php
session_start();
require 'db.php';

// Ensure the user is an official
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'official') {
    echo "Access denied!";
    exit;
}

if(!isset($_GET['project_id'])) {
    $error_msg = "No Project Selected";
    $redirect_link = "view_projects.php";
    header("Location: error.php?msg=$error_msg&redirect=$redirect_link");
    exit();
}

// Fetch project details
$project_id = $_GET['project_id'];
$stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];

    // Update project status
    $stmt = $pdo->prepare("UPDATE projects SET status = ? WHERE project_id = ?");
    $stmt->execute([$status, $project_id]);

    // Fetch the project details
    $project_stmt = $pdo->prepare("SELECT title FROM projects WHERE project_id = ?");
    $project_stmt->execute([$project_id]);
    $project = $project_stmt->fetch(PDO::FETCH_ASSOC);

    // Notify all citizens about the project status update
    $citizens_stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'citizen'");
    $citizens = $citizens_stmt->fetchAll(PDO::FETCH_ASSOC);

    $notification_message = "The status of the project titled '{$project['title']}' has been updated to '{$status}'.";
    $notification_link = "project_feedback.php?project_id={$project_id}";
    $notification_type = 'project';

    foreach ($citizens as $citizen) {
        $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message, link_url, `type`) VALUES (?, ?, ?, ?)");
        $notification_stmt->execute([$citizen['user_id'], $notification_message, $notification_link, $notification_type]);
    }

    echo "Project status updated!";
    header("Location: view_projects.php");
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
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <div class="main-container">
        <?php include 'user.php';?>
        <div class="display-section">
            <?php include 'side_panel.php';?>
            <div class="main-panel">
                <div class="panel-section">
                    <h2>Update Status for: <?php echo htmlspecialchars($project['title']); ?></h2>
                    <br>
                    <form action="update_project.php?project_id=<?php echo $project_id; ?>" method="post">
                        <select name="status" required>
                            <option value="in progress" <?php if ($project['status'] == 'in progress') echo 'selected'; ?>>In Progress</option>
                            <option value="completed" <?php if ($project['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                            <option value="delayed" <?php if ($project['status'] == 'delayed') echo 'selected'; ?>>Delayed</option>
                        </select>
                        <button type="submit" class="btn-submit short">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>