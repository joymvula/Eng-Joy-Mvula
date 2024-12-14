<?php
session_start();
require 'db.php';

// Ensure the user is an official
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'official') {
    echo "Access denied!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $meeting_id = $_POST['meeting_id'];
    $minutes = $_POST['minutes']; // Text-based minutes
    $recording_url = $_POST['recording_url']; // URL to a video recording

    // Update the meeting with minutes and recording URL
    $stmt = $pdo->prepare("UPDATE town_hall_meetings SET minutes = ?, recording_url = ? WHERE meeting_id = ?");
    $stmt->execute([$minutes, $recording_url, $meeting_id]);

    echo "Minutes and recording uploaded successfully!";
    // Redirect to avoid form resubmission
    header('Location: upload_minutes.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Minutes/Recording</title>
</head>
<body>
    <h1>Upload Minutes or Recording for a Town Hall</h1>
    <form action="upload_minutes.php" method="post">
        <select name="meeting_id" required>
            <option value="">Select Meeting</option>
            <?php
            // Fetch meetings that the official created
            $stmt = $pdo->prepare("SELECT * FROM town_hall_meetings WHERE created_by = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($meetings as $meeting) {
                echo '<option value="' . $meeting['meeting_id'] . '">' . htmlspecialchars($meeting['title']) . '</option>';
            }
            ?>
        </select><br>
        <textarea name="minutes" placeholder="Minutes of the Meeting"></textarea><br>
        <input type="url" name="recording_url" placeholder="Recording URL (optional)"><br>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
