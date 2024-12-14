<?php
session_start();
require 'db.php';
include 'functions.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];  // Get the logged-in user's ID

// Handle voting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['poll_id']) && isset($_POST['vote_option'])) {
    $poll_id = $_POST['poll_id'];
    $vote_option = $_POST['vote_option'];

    // Check if the user has already voted on this poll
    $check_vote_stmt = $pdo->prepare("SELECT * FROM votes WHERE poll_id = ? AND user_id = ?");
    $check_vote_stmt->execute([$poll_id, $user_id]);
    $existing_vote = $check_vote_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_vote) {
        // User has already voted, show an error message
        echo "<script type='text/javascript'>alert('You have already voted on this poll')</script>";
    } else {
        // Insert the vote into the database
        $vote_stmt = $pdo->prepare("INSERT INTO votes (poll_id, user_id, vote_option) VALUES (?, ?, ?)");
        $vote_stmt->execute([$poll_id, $user_id, $vote_option]);

        echo "<script type='text/javascript'>alert('Thank you for voting!')</script>";
    }
}

// Fetch all active polls
$stmt = $pdo->query("SELECT * FROM polls WHERE closing_date >= NOW()");
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <div class="panel-section polls">
                    <h1>Government Polls</h1>
                    <p>Participate in ongoing polls and share your opinions on key issues. Your input plays a vital role in shaping decisions that affect the community.</p>

                    <hr>
                    <div class="feed">
                        <?php if (empty($polls)): ?>
                            <p>No active polls at the moment.</p>
                            <p>
                                <a href="finished_polls.php">View Previous Polls</a>
                            </p>
                        <?php else: ?>
                            <?php foreach ($polls as $poll): ?>
                                <div class="post-item government-post">
                                    <div class="post">
                                        <div class="post-head">
                                            <small class="highlight">Posted <?php echo timeAgo($poll['creation_date']); ?></small></h4>
                                        </div>
                                        <div class="post-body government-post-body">
                                            <div class="government-post-content">
                                                <h2><?php echo htmlspecialchars($poll['title']); ?></h2>
                                                <p><?php echo htmlspecialchars($poll['description']); ?></p>
                                                <p>Closing Date: <span class="highlight"><?php echo date('d F Y', strtotime($poll['closing_date'])); ?></span></p>
                                                <br>

                                                <?php if ($user_role == 'citizen'): ?>
                                                    <!-- Voting form for citizens -->
                                                    <form action="view_polls.php" method="post">
                                                        <div class="input-box">
                                                        <input type="hidden" name="poll_id" value="<?php echo $poll['poll_id']; ?>">
                                                        <button type="submit" name="vote_option" value="yes" class="btn-submit short"> <i class="fa fa-check"></i> Yes</button>
                                                        <button type="submit" name="vote_option" value="no" class="btn-submit-outline short"><i class="fa fa-close"></i> No</button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($user_role == 'official' || $user_role == 'moderator'): ?>
                                                    <!-- View poll results for officials and moderators -->
                                                    <a href="poll_results.php?poll_id=<?php echo $poll['poll_id']; ?>" class="btn-submit">View Results</a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="government-post-details">
                                                <h3> Details </h3>
                                                <div class="list">
                                                    <p>Poll Closes on <span class="highlight"><?php echo date('d F Y', strtotime($poll['closing_date'])); ?></span></p>
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


    