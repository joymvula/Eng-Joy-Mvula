<?php
require 'db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validation
    $errors = [];

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password validation (minimum 8 characters, 1 uppercase, 1 number, 1 special character)
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "User with this email already exists!";
    }

    // If no errors, proceed to register
    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $password_hash]);

        echo "Registration successful!";
        // Redirect to login page
        header('Location: login.php');
        exit;
    } else {
        // Display errors
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
</head>
<body>
    <div class="main-container bg-light center-items">
        <div class="form-body br-medium">
            <div class="form-container">
                <h2>Create A<span class="highlight"> Citizen Voice </span> <br> Account </h2>
                <form action="register.php" method="post">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="btn-submit">Register</button>
                </form>
                    <?php if (!empty($error)): ?>
                        <div class="error-body">
                        <?php
                            foreach ($errors as $error) {
                                echo "<p style='color: red;'>$error</p>";
                            }
                        ?>
                        </div>
                    <?php endif; ?>
                <p>
                    Already have an account? <a href="login.php">Login</a>
                </p>
                <p><a href="../index.html">Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
