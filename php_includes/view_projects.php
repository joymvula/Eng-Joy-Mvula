<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the user role
$user_role = $_SESSION['role'];

// Handle project filtering by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Fetch projects based on the selected status filter along with user details
if ($status_filter === 'all') {
    $stmt = $pdo->query("
        SELECT p.*, u.first_name, u.last_name 
        FROM projects p 
        JOIN users u ON p.created_by = u.user_id
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, u.first_name, u.last_name 
        FROM projects p 
        JOIN users u ON p.created_by = u.user_id 
        WHERE p.status = ?
    ");
    $stmt->execute([$status_filter]);
}

$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <h1>Government Projects</h1>
                    <p>Explore ongoing government projects that impact our community. Stay informed and see how you can contribute or provide feedback.</p>
                    <hr>


                    <!-- Filter Form -->
                    <div>
                        <div class="filtering">
                            <p>Filter By Status:</p>
                            <form action="view_projects.php" method="get">
                                <select name="status" id="status" onchange="this.form.submit()">
                                    <option value="all" <?php if ($status_filter === 'all') echo 'selected'; ?>>All</option>
                                    <option value="open" <?php if ($status_filter === 'open') echo 'selected'; ?>>Open</option>
                                    <option value="in progress" <?php if ($status_filter === 'in progress') echo 'selected'; ?>>In Progress</option>
                                    <option value="completed" <?php if ($status_filter === 'completed') echo 'selected'; ?>>Completed</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="feed">
                        <?php if (empty($projects)): ?>
                            <p>No projects available at the moment.</p>
                        <?php else: ?>
                            <?php foreach ($projects as $project): ?>
                                <div class="post-item government-post">
                                    <div class="post">
                                        <div class="post-head">
                                            <i class="fa fa-user-circle circle"></i>
                                            <h4><?php echo htmlspecialchars($project['first_name']) . ' ' . htmlspecialchars($project['last_name']); ?></h4>
                                        </div>
                                        <div class="post-body government-post-body">
                                            <div class="government-post-content">
                                                <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                                                <?php
                                                $paragraphs = explode("\n", $project['description']);
                                                foreach ($paragraphs as $paragraph) {
                                                    if (trim($paragraph) !== '') {  // Ignore empty lines
                                                        echo "<p>" . htmlspecialchars($paragraph) . "</p>";
                                                    }
                                                }?>
                                            </div>
                                            <div class="government-post-details">
                                                <h3>Details</h3>
                                                <div class="list">
                                                    <p>Starts <span class="highlight"><?php echo htmlspecialchars($project['start_date']); ?></span></p>
                                                    <p>Expected to End <span class="highlight"><?php echo htmlspecialchars($project['end_date']); ?></span></p>
                                                    <p>Is Currently <span class="highlight"><?php echo htmlspecialchars($project['status']); ?></span></p>
                                                </div>
                                                <div class="government-post-actions">
                                                    <!-- Displayed actions according to user role -->
                                                    <?php if ($user_role == 'citizen'): ?>
                                                        <a href="project_feedback.php?project_id=<?php echo $project['project_id']; ?>" class="btn-submit">Give Feedback</a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($user_role == 'official'): ?>
                                                        <a href="project_feedback.php?project_id=<?php echo $project['project_id']; ?>" class="btn-submit">View Feedback</a>
                                                        <a href="update_project.php?project_id=<?php echo $project['project_id']; ?>" class="btn-submit-outline">Update Status</a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($user_role == 'moderator'): ?>
                                                        <a href="project_feedback.php?project_id=<?php echo $project['project_id']; ?>" class="btn-submit">View Feedback</a>
                                                        <form action="delete_project.php" method="post" style="display:inline;">
                                                            <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this project?');" class="btn-submit-outline short"><i class="fa fa-trash"></i> Delete</button>
                                                        </form>
                                                        <br>
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
