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

// Handle issue filtering by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Fetch issues based on the status filter
if ($status_filter === 'all') {
    $stmt = $pdo->query("SELECT public_issues.*, users.first_name, users.last_name FROM public_issues JOIN users ON public_issues.user_id = users.user_id");
} else {
    $stmt = $pdo->prepare("SELECT public_issues.*, users.first_name, users.last_name FROM public_issues JOIN users ON public_issues.user_id = users.user_id WHERE public_issues.status = ?");
    $stmt->execute([$status_filter]);
}
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle moderator actions (delete issue, mark as inappropriate)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $issue_id = $_POST['issue_id'];

    // Delete the issue
    if (isset($_POST['delete_issue'])) {
        // Get the issue details
        $issue_stmt = $pdo->prepare("SELECT * FROM public_issues WHERE issue_id = ?");
        $issue_stmt->execute([$issue_id]);
        $issue = $issue_stmt->fetch(PDO::FETCH_ASSOC);

        if ($issue) {
            // Delete the issue
            $delete_stmt = $pdo->prepare("DELETE FROM public_issues WHERE issue_id = ?");
            $delete_stmt->execute([$issue_id]);

            // Notify the user who reported the issue
            $notification_message = "Your reported issue titled '{$issue['issue_title']}' was deleted by a moderator.";
            $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, notification_message) VALUES (?, ?)");
            $notification_stmt->execute([$issue['user_id'], $notification_message]);

            // Notify officials
            $officials_stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'official'");
            $officials = $officials_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($officials as $official) {
                $notification_message_official = "A moderator deleted the issue titled '{$issue['issue_title']}'.";;
                $notification_stmt->execute([$official['user_id'], $notification_message_official]);
            }

            // Redirect to avoid form resubmission
            header('Location: view_issues.php');
            exit;
        }
    }

    // Mark issue as inappropriate
    if (isset($_POST['mark_inappropriate'])) {
        // Get the issue details
        $issue_stmt = $pdo->prepare("SELECT * FROM public_issues WHERE issue_id = ?");
        $issue_stmt->execute([$issue_id]);
        $issue = $issue_stmt->fetch(PDO::FETCH_ASSOC);

        if ($issue) {
            // Notify the user whose issue was marked as inappropriate
            $notification_message = "Your issue titled '{$issue['issue_title']}' has been flagged as inappropriate by a moderator.";
            $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notification_stmt->execute([$issue['user_id'], $notification_message]);

            // Notify officials that an issue was marked as inappropriate
            $officials_stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'official'");
            $officials = $officials_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($officials as $official) {
                $notification_message_official = "A moderator flagged the issue titled '{$issue['issue_title']}' as inappropriate.";
                $notification_stmt->execute([$official['user_id'], $notification_message_official]);
            }

            // Redirect to avoid form resubmission
            header('Location: view_issues.php');
            exit;
        }
    }
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
                <div class="panel-section feedback">
                    <h1>Public Issues</h1>
                    <p>Join the conversation on pressing public issues that impact us all. This is a chance to bring community challenges to light, brainstorm solutions, and create meaningful change.</p>
                    <hr>
                    <br>

                    <!-- Issue Filter Form -->
                    <form action="view_issues.php" method="get">
                        <div class="filtering">
                        <label for="status">Filter by Status:</label>
                        <select name="status" id="status" onchange="this.form.submit()">
                            <option value="all" <?php if ($status_filter === 'all') echo 'selected'; ?>>All</option>
                            <option value="open" <?php if ($status_filter === 'open') echo 'selected'; ?>>Open</option>
                            <option value="under investigation" <?php if ($status_filter === 'under investigation') echo 'selected'; ?>>Under Investigation</option>
                            <option value="resolved" <?php if ($status_filter === 'resolved') echo 'selected'; ?>>Resolved</option>
                        </select>
                        </div>
                    </form>

                    <div class="feed">
                        <?php if (empty($issues)): ?>
                            <p>No issues found for the selected status.</p>
                        <?php else: ?>
                            <?php foreach ($issues as $issue): ?>
                                <div class="post-item">
                                    <div class="post">
                                        <div class="post-head">
                                            <i class="fa fa-user-circle circle"></i>
                                            <h4><?php echo htmlspecialchars($issue['first_name'] . ' ' . $issue['last_name']); ?> | <small class="highlight"> <?php echo timeAgo($issue['report_date'])?></small></h4>
                                        </div>
                                        <div class="post-body">
                                            <h3><?php echo htmlspecialchars($issue['issue_title']); ?></h3>
                                            <?php
                                                echo nl2br(htmlspecialchars($issue['issue_description']));                                        
                                            ?>
                                            <p>Status: <?php echo htmlspecialchars($issue['status']); ?></p>
                                        </div>
                                        <div class="post-footer">
                                            <a href="view_issue.php?issue_id=<?php echo $issue['issue_id']; ?>" class="btn-submit short">View Details</a>

                                            <?php if ($user_role == 'official'): ?>
                                                <a href="update_issue.php?issue_id=<?php echo $issue['issue_id']; ?>" class="btn-submit short">Update Status</a>
                                            <?php endif; ?>

                                            <?php if ($user_role == 'moderator'): ?>
                                                <form action="view_issues.php" method="post" style="display:inline;">
                                                    <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                                                    <button type="submit" name="delete_issue" onclick="return confirm('Are you sure you want to delete this issue?');" class="btn-submit-outline short"><i class="fa fa-trash"></i> Delete Issue</button>
                                                    <button type="submit" name="mark_inappropriate" class="btn-submit-outline short"><i class="fa fa-warning"></i> Mark as Inappropriate</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
