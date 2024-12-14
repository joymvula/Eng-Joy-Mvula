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

// Fetch upcoming town hall meetings
$stmt = $pdo->query("
    SELECT th.*, u.first_name, u.last_name 
    FROM town_hall_meetings th 
    JOIN users u ON th.created_by = u.user_id 
    WHERE th.meeting_date >= NOW()
");

$townhalls = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form actions (registration or deletion)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register']) && $user_role == 'citizen') {
        $meeting_id = $_POST['meeting_id'];
        
        // Insert registration into the database
        $stmt = $pdo->prepare("INSERT INTO meeting_registrations (meeting_id, user_id) VALUES (?, ?)");
        $stmt->execute([$meeting_id, $user_id]);

        // Redirect to avoid form re-submission
        header("Location: view_townhalls.php?registered=success");
        exit;
    }

    if (isset($_POST['delete_meeting']) && $user_role == 'moderator') {
        $meeting_id = $_POST['meeting_id'];
        
        // Delete the meeting from the database
        $stmt = $pdo->prepare("DELETE FROM town_hall_meetings WHERE meeting_id = ?");
        $stmt->execute([$meeting_id]);

        // Redirect to avoid form re-submission
        header("Location: view_townhalls.php?deleted=success");
        exit;
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
                <div class="panel-section projects">
                    <h1>Upcoming Town Hall Meetings</h1>
                    <p>Join us for an open dialogue and a chance to have your voice heard! The upcoming town hall meetings are an opportunity to discuss important community issues, address local challenges, and work together towards solutions that benefit everyone.</p>
                    <hr>

                    <div class="feed">
                        <?php if (empty($townhalls)): ?>
                            <p>No town hall meetings have been scheduled yet.</p>
                        <?php else: ?>
                            <?php foreach ($townhalls as $townhall): ?>
                                <div class="post-item government-post">
                                    <div class="post">
                                        <div class="post-head">
                                            <i class="fa fa-user-circle circle"></i>
                                            <h4><?php echo htmlspecialchars($townhall['first_name']) . ' ' . htmlspecialchars($townhall['last_name']); ?></h4>
                                        </div>
                                        <div class="post-body government-post-body">
                                            <div class="government-post-content">
                                                <h2><?php echo htmlspecialchars($townhall['title']); ?></h2>
                                                <p><?php echo htmlspecialchars($townhall['description']); ?></p>
                                            </div>
                                            <div class="government-post-details">
                                                <h3>Meeting Details</h3>
                                                <div class="list">
                                                    <p>Meeting Date: <span class="highlight"><?php echo htmlspecialchars($townhall['meeting_date']); ?></span></p>
                                                    <p>Meeting links: <a href="<?php echo htmlspecialchars($townhall['location_url']); ?>" target="_blank"><?php echo htmlspecialchars($townhall['location_url']); ?></a></p>
                                                </div>

                                                <div class="government-post-actions">
                                                    <?php if ($user_role == 'citizen'): ?>
                                                        <!-- Registration form for citizens -->
                                                        <form action="view_townhalls.php" method="post" style="display:inline;">
                                                            <input type="hidden" name="meeting_id" value="<?php echo $townhall['meeting_id']; ?>">
                                                            <?php if (!isset($_GET['registered'])): ?>
                                                            <button type="submit" name="register" class="btn-submit">Register for Meeting</button>
                                                            <?php else: echo "<p> You have registed for this meetng </p>"; ?>
                                                            <?php endif ?>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($user_role == 'moderator'): ?>
                                                        <!-- Delete button for moderators -->
                                                        <form action="view_townhalls.php" method="post" style="display:inline;">
                                                            <input type="hidden" name="meeting_id" value="<?php echo $townhall['meeting_id']; ?>">
                                                            <button type="submit" name="delete_meeting" class="btn-submit-outline short" onclick="return confirm('Are you sure you want to delete this meeting?');">
                                                                <i class="fa fa-trash"></i> Delete Meeting
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
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
