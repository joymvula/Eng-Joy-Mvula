<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user role
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Get poll_id from the URL
if (!isset($_GET['poll_id'])) {
    $error_msg = "No Poll Selected";
    $redirect_link = "view_polls.php";
    header("Location: error.php?msg=$error_msg&redirect=$redirect_link");
    exit();
}
$poll_id = $_GET['poll_id'];

// Fetch poll details
$stmt = $pdo->prepare("SELECT * FROM polls WHERE poll_id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch voting results
$votes_stmt = $pdo->prepare("
    SELECT vote_option, COUNT(*) as vote_count
    FROM votes
    WHERE poll_id = ?
    GROUP BY vote_option
");
$votes_stmt->execute([$poll_id]);
$results = $votes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total votes
$total_votes = array_sum(array_column($results, 'vote_count'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll Results</title>
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
                <div class="panel-section polls">
                    <h1>Poll Results: <?php echo htmlspecialchars($poll['title']); ?></h1>
                    <p><?php echo htmlspecialchars($poll['description']); ?></p>
                    <p>Poll closed on: <span class="highlight"><?php echo date('d F Y', strtotime($poll['closing_date'])); ?></span></p>
                    <hr>
                    <div class="results-section">
                        <h2>Results:</h2>
                        <?php if ($total_votes > 0): ?>
                            <?php foreach ($results as $result): ?>
                                <div class="result-item">
                                    <p>
                                        <strong><?php echo htmlspecialchars(ucfirst($result['vote_option'])); ?>:</strong> 
                                        <?php echo htmlspecialchars($result['vote_count']); ?> votes 
                                        (<?php echo round(($result['vote_count'] / $total_votes) * 100, 2); ?>%)
                                    </p>
                                </div>
                            <?php endforeach; ?>
                            <p><strong>Total Votes:</strong> <?php echo $total_votes; ?></p>
                        <?php else: ?>
                            <p>No votes were cast for this poll.</p>
                        <?php endif; ?>
                        <a href="finished_polls.php" class="btn-submit">Back to Completed Polls</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
