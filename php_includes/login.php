<?php
session_start();
require 'db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Store user info in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        switch ($user['role']) {
            case 'citizen':
                header('Location: welcome.php'); // Citizens go to feedback and voting
                break;
            case 'official':
                header('Location: government_dashboard.php'); // Officials go to poll creation
                break;
            case 'moderator':
                header('Location: moderation_dashboard.php'); // Moderators go to a moderation dashboard
                break;
            default:
                header('Location: login.php'); // Fallback in case of an unknown role
        }
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
</head>
<body>
    <div class="main-container bg-light center-items">
        <div class="form-body">
            <div class="form-container">
                <h2>Login To Your <br> <span class="highlight"> Citizen Voice </span>Account </h2>
                <form action="login.php" method="post">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="btn-submit">Login</button>
                </form>
                <div class="form-error">
                    <?php echo $error ?>
                </div>
                <p>
                    Don't have an account? <a href="register.php">Register</a>
                </p>
                <p><a href="../index.html">Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>