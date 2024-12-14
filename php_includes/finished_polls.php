<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch polls where the closing date has passed
$stmt = $pdo->query("SELECT * FROM polls WHERE closing_date < NOW()");
$completed_polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle poll deletion (moderator only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_poll']) && $_SESSION['role'] == 'moderator') {
    $poll_id = $_POST['poll_id'];

    // Delete the poll from the database
    $stmt = $pdo->prepare("DELETE FROM polls WHERE poll_id = ?");
    $stmt->execute([$poll_id]);

    // Redirect to avoid form resubmission
    header('Location: finished_polls.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finished Polls</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <div class="main-container">
        <?php include 'user.php';?>
        <div class="display-section">
            <?php include 'side_panel.php';?>
            <div class="main-panel">
                <div class="panel-section polls">
                    <h1>Completed Polls</h1>
                    <p>View the results of completed polls in this section. Here, you can explore community responses and insights on various topics, helping you understand public sentiment and inform future decisions. Engaging with completed polls allows you to see how citizen voices have shaped outcomes.</p>
                    <hr>
                    <div class="feed">
                        <?php if (empty($completed_polls)): ?>
                            <p>No completed polls to display.</p>
                        <?php else: ?>
                            <?php foreach ($completed_polls as $poll): ?>
                                <div class="post-item government-post">
                                    <div class="post">
                                        <div class="post-head">
                                            <small class="highlight">Poll closed on <?php echo date('d F Y', strtotime($poll['closing_date'])); ?></small>
                                        </div>
                                        <div class="post-body government-post-body">
                                            <div class="government-post-content">
                                                <h2><?php echo htmlspecialchars($poll['title']); ?></h2>
                                                <p><?php echo htmlspecialchars($poll['description']); ?></p>
                                                <br>
                                                <a href="poll_results.php?poll_id=<?php echo $poll['poll_id']; ?>" class="btn-submit">View Results</a>
                                                <?php if ($_SESSION['role'] == 'moderator'): ?>
                                                    <form action="finished_polls.php" method="post" style="display:inline;">
                                                        <input type="hidden" name="poll_id" value="<?php echo $poll['poll_id']; ?>">
                                                        <button type="submit" name="delete_poll" class="btn-submit-outline short" onclick="return confirm('Are you sure you want to delete this poll?');">
                                                            <i class="fa fa-trash"></i> Delete Poll
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
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