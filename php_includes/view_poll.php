<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Check if a poll_id is provided
if (!isset($_GET['poll_id'])) {
    $error_msg = "No Poll Selected";
    $redirect_link = "view_polls.php";
    header("Location: error.php?msg={$error_msg}&redirect={$redirect_link}");
    exit();
}

// Get the poll_id from the URL
$poll_id = $_GET['poll_id'];

// Fetch the specific poll based on the poll_id
$stmt = $pdo->prepare("SELECT * FROM polls WHERE poll_id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);

// If the poll is not found, redirect to the polls page
if (!$poll) {
    header('Location: view_polls.php');
    exit;
}

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
        echo "<p style='color: red;'>You have already voted on this poll.</p>";
    } else {
        // Insert the vote into the database
        $vote_stmt = $pdo->prepare("INSERT INTO votes (poll_id, user_id, vote_option) VALUES (?, ?, ?)");
        $vote_stmt->execute([$poll_id, $user_id, $vote_option]);

        echo "<p style='color: green;'>Thank you for voting!</p>";
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
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <div class="main-container">
        <?php include 'user.php';?>
        <div class="display-section">
            <?php include 'side_panel.php';?>
            <div class="main-panel">
                <div class="panel-section">
                    <h2><?php echo htmlspecialchars($poll['title']); ?></h2>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($poll['description']); ?></p>
                    <p><strong>Closing Date:</strong> <?php echo htmlspecialchars($poll['closing_date']); ?></p>

                    <?php if (isset($vote_message)) echo $vote_message; ?>

                    <?php if ($user_role == 'citizen'): ?>
                        <!-- Voting form for citizens -->
                        <form action="view_poll.php?poll_id=<?php echo $poll_id; ?>" method="post">
                            <button type="submit" class="btn-submit short" name="vote_option" value="yes">Vote Yes</button>
                            <button type="submit" class="btn-submit short" name="vote_option" value="no">Vote No</button>
                        </form>
                        <br>
                        <a href="view_polls.php" lass="btn-submit-outline short">Back to All Polls</a>
                    <?php endif; ?>

                    <?php if ($user_role == 'official' || $user_role == 'moderator'): ?>
                        <!-- Link to view poll results for officials and moderators -->
                        <a href="poll_results.php?poll_id=<?php echo $poll_id; ?>" class="btn-submit short">View Results</a>
                    <?php endif; ?>
                </div>
            </div>
</body>
</html>
