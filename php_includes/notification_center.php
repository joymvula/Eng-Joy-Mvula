<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch notifications for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_date DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Mark notifications as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    $notification_id = $_POST['notification_id'];

    // Update the notification to mark it as read
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
    $stmt->execute([$notification_id]);

    // Redirect to avoid form resubmission
    header('Location: notification_center.php');
    exit;
}

// Function to convert datetime to a "time ago" format
function timeAgo($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [];
    $units = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    foreach ($units as $key => &$val) {
        if ($diff->$key) {
            $string[] = $diff->$key . ' ' . $val . ($diff->$key > 1 ? 's' : '');
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Define icon mapping for notification types
$iconMapping = [
    'suggestion' => 'fa-comment',
    'poll' => 'fa-pie-chart',
    'project' => 'fa-building',
    'townhall' => 'fa-users',
    'issue' => 'fa-comment', // Adjust this as needed
];

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
        <?php include 'user.php'; ?>
        <div class="display-section">
            <?php include 'side_panel.php'; ?>
            <div class="main-panel">
                <div class="panel-section notifications showing">
                    <h1>Your Notifications</h1>
                    <p>Stay up-to-date with important updates and reminders! Here, you’ll find notifications tailored to keep you informed about recent announcements, upcoming events, and community alerts.</p>
                    <hr>
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
                                        <span><?php echo timeAgo($notification['sent_date']); ?></span>
                                    </div>
                                    <div class="notification-actions">
                                        <?php if (!empty($notification['link_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($notification['link_url']); ?>">View Details</a>
                                        <?php endif; ?>
                                        <?php if (!$notification['is_read']): ?>
                                            <form action="notification_center.php" method="post" style="display:inline;">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                <button type="submit" name="mark_read" class="btn-submit">Mark as Read</button>
                                            </form>
                                        <?php endif; ?>
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
