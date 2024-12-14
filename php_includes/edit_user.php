<?php
session_start();
require 'db.php';

// Ensure the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'official' && $_SESSION['role'] != 'moderator')) {
    echo "Access denied!";
    exit;
}

if(!isset($_GET['user_id'])) {
    $error_msg = "No User Selected";
    $redirect_link = "manage_users.php";
    header("Location: error.php?msg=$error_msg&redirect=$redirect_link");
    exit();
}

// Get user ID from the URL
$user_id = $_GET['user_id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle user update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $role = $_POST['role'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    // Update user details
    $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ?, first_name = ?, last_name = ? WHERE user_id = ?");
    $stmt->execute([$email, $role, $first_name, $last_name, $user_id]);

    echo "User updated successfully!";
    header('Location: manage_users.php');
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
                <div class="panel-section">
                    <h1>Edit User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                    <br>
                    <form action="edit_user.php?user_id=<?php echo $user_id; ?>" method="post">
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <select name="role" required>
                            <option value="citizen" <?php if ($user['role'] == 'citizen') echo 'selected'; ?>>Citizen</option>
                            <option value="official" <?php if ($user['role'] == 'official') echo 'selected'; ?>>Government Official</option>
                            <option value="moderator" <?php if ($user['role'] == 'moderator') echo 'selected'; ?>>Moderator</option>
                        </select>
                        <button type="submit" class="btn-submit short">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

