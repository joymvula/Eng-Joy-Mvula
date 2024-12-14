<?php
session_start();
require 'db.php';

// Ensure the user is logged in and is an official
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'official') {
    header('Location: login.php');
    exit;
}

if(!isset($_GET['issue_id'])) {
    $error_msg = "No Issue Selected";
    $redirect_link = "view_issues.php";
    header("Location: error.php?msg=$error_msg&redirect=$redirect_link");
    exit();
}

// Get the issue ID from the URL
$issue_id = $_GET['issue_id'];

// Fetch the current issue details
$stmt = $pdo->prepare("SELECT * FROM public_issues WHERE issue_id = ?");
$stmt->execute([$issue_id]);
$issue = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];

    // Update the status of the issue
    $stmt = $pdo->prepare("UPDATE public_issues SET status = ? WHERE issue_id = ?");
    $stmt->execute([$new_status, $issue_id]);

    // Fetch the issue details and the user who reported it
    $issue_stmt = $pdo->prepare("SELECT issue_title, user_id FROM public_issues WHERE issue_id = ?");
    $issue_stmt->execute([$issue_id]);
    $issue = $issue_stmt->fetch(PDO::FETCH_ASSOC);

    // Notify the citizen who reported the issue
    $notification_message = "The status of your issue titled '{$issue['issue_title']}' has been updated to '{$new_status}'.";
    $notification_type = "issue";
    $notification_link = "view_issue.php?issue_id={$issue_id}";

    $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message, link_url, `type`) VALUES (?, ?, ?, ?)");
    $notification_stmt->execute([$issue['user_id'], $notification_message, $notification_link, $notification_type]);


    echo "Issue status updated!";
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
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <div class="main-container">
        <?php include 'user.php';?>
        <div class="display-section">
            <?php include 'side_panel.php';?>
            <div class="main-panel">
                <div class="panel-section">
                <h2>Update Status for: <?php echo htmlspecialchars($issue['issue_title']); ?></h2>
                <br>
                    <form action="update_issue.php?issue_id=<?php echo $issue_id; ?>" method="post">                    
                        <label for="status">New Status:</label>
                        <select name="status" required>
                            <option value="open" <?php if ($issue['status'] == 'open') echo 'selected'; ?>>Open</option>
                            <option value="under investigation" <?php if ($issue['status'] == 'under investigation') echo 'selected'; ?>>Under Investigation</option>
                            <option value="resolved" <?php if ($issue['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                        </select>
                        <button type="submit" class="btn-submit short">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
