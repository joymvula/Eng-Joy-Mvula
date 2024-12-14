<?php
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header('Location: login.php');
    exit;
}

// Get user info from session
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$role = $_SESSION['role'];

// Fetch the last 5 notifications for the logged-in citizen
$notifications_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_date DESC LIMIT 5");
$notifications_stmt->execute([$_SESSION['user_id']]);
$notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    $notification_id = $_POST['notification_id'];

    // Update the notification to mark it as read
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
    $stmt->execute([$notification_id]);

    // Redirect to avoid form resubmission
    header('Location: welcome.php');
    exit;
}

// Count unread notifications
$unread_stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$unread_stmt->execute([$_SESSION['user_id']]);
$unread_notifications = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

include 'functions.php';

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
                <div class="panel-section welcome-user showing">
                    <h1>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>!</h1>
                    <p>Role: <strong><?php echo htmlspecialchars($role); ?></strong></p>
                    <p>Welcome to your dashboard. Here you can manage your account and participate in the community.</p>
                    <hr>                      
                    <br>
                    <h2>For You</h2>
                    <br>
                    <div class="notifications-grid">
                        <?php if (empty($notifications)): ?>
                            <p>No notifications to display.</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item box">
                                    <div class="indicator"></div>
                                    <i class="fa <?php echo $iconMapping[$notification['type']] ?? 'fa-bell'; ?> notification-icon"></i>
                                    <div class="notification-content">
                                        <?php echo htmlspecialchars($notification['notification_message']); ?> <br>
                                        <span><?php echo timeAgo($notification['sent_date']); ?></span> <!-- Custom function for time -->
                                    </div>
                                    <div class="notification-actions">
                                        <?php if (!empty($notification['link_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($notification['link_url']); ?>">View Details</a>
                                        <?php endif; ?>
                                        <form action="welcome.php" method="post" style="display:inline;">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                            <?php if (!$notification['is_read']): ?>
                                                <form action="notification_center.php" method="post" style="display:inline;">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                    <button type="submit" name="mark_read" class="btn-submit">Mark as Read</button>
                                                </form>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <p><a href="notification_center.php" class="btn-submit short">View All </a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
