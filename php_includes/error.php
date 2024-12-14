<?php

if(!isset($_GET['msg'])) {
    exit();
}

$msg = $_GET['msg'];
$link = $_GET['redirect'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <div class="main-container">
        <div class="header">
            <div class="header-content"></div>
        </div>
        <div class="error-body">
            <i class="fa fa-warning circle"></i>
            <h2>Error</h2>
            <?php echo htmlspecialchars($msg) ?>
            <br>
            <?php
            echo "<a href=".$link.">Go Back</a>";
            ?>
        </div>
    </div>
</body>
</html>