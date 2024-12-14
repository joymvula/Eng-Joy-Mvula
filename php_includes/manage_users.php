<?php
session_start();
require 'db.php';

// Ensure the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'official' && $_SESSION['role'] != 'moderator')) {
    echo "Access denied!";
    exit;
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Delete the user from the database
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Redirect to avoid form resubmission
    header('Location: manage_users.php');
    exit;
}

// Handle new user creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    // Insert new user into the database
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$email, $password, $role, $first_name, $last_name]);

    echo "User created successfully!";
    // Redirect to avoid form resubmission
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
                <h1>User Management</h1>
                <p>
                Manage user accounts and access within this section. Here, you can add, edit, or remove users, assign roles, and monitor activity to ensure a secure and organized platform. Effective user management is key to fostering a collaborative and productive environment.
                </p>
                <br>
                <!-- Create New User Form -->
                <h2>Create New User</h2>
                <form action="manage_users.php" method="post">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="role" required>
                        <option value="citizen">Citizen</option>
                        <option value="official">Government Official</option>
                        <option value="moderator">Moderator</option>
                    </select>
                    <button type="submit" name="create_user" class="btn-submit short">Create User</button>
                </form>
            </div>

            <div class="panel-section">
                <h2>Existing Users</h2>
                <?php if (empty($users)): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <div class="user-list">
                        <?php foreach ($users as $user): ?>
                            <div class="user-container">
                                <div>
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> 
                                    (<?php echo htmlspecialchars($user['email']); ?>) - Role: <?php echo htmlspecialchars($user['role']); ?>
                                </div>
                                <a href="edit_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn-submit short">Edit</a>
                                <form action="manage_users.php" method="post" style="display:inline;">                                    
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?');" class="btn-submit-outline short"><i class="fa fa-trash"></i> Delete User</button>
                                </form>                                
                        </div>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>


            </div>
        </div>
    </div>
</body>
</html>
