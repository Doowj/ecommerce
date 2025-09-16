<?php
/*
require 'components/_base.php';  // Include the database connection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$fromUserId = isset($_GET['fromUserId']) ? intval($_GET['fromUserId']) : 0; // Admin
$toUserId = isset($_GET['toUserId']) ? intval($_GET['toUserId']) : 0;       // Member

if ($fromUserId > 0 && $toUserId > 0) {
    try {
        $query = "SELECT * FROM chat 
                  WHERE (from_user_id = :fromUserId AND to_user_id = :toUserId)
                  OR (from_user_id = :toUserId AND to_user_id = :fromUserId)
                  ORDER BY timestamp ASC";
        $stmt = $_db->prepare($query);
        $stmt->bindParam(':fromUserId', $fromUserId);
        $stmt->bindParam(':toUserId', $toUserId);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $senderId = htmlspecialchars($row['from_user_id']);
            $message = htmlspecialchars($row['message']);
            $timestamp = htmlspecialchars($row['timestamp']);

            echo "<p><strong>User {$senderId}:</strong> {$message} <small>{$timestamp}</small></p>";
        }
    } catch (PDOException $e) {
        error_log("Error fetching chat messages: " . $e->getMessage());
        echo "Error: Could not fetch messages.";
    }
}

?>*/

require 'components/_base.php'; // Ensure DB connection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$fromUserId = isset($_GET['fromUserId']) ? intval($_GET['fromUserId']) : 0;
$toUserId = isset($_GET['toUserId']) ? intval($_GET['toUserId']) : 0;

if ($fromUserId > 0 && $toUserId > 0) {
    // Fetch chat messages and join with the user table to get user names
    $query = "
        SELECT c.*, u_from.name AS from_user_name, u_to.name AS to_user_name
        FROM chat c
        JOIN user u_from ON c.from_user_id = u_from.id
        JOIN user u_to ON c.to_user_id = u_to.id
        WHERE (c.from_user_id = :fromUserId AND c.to_user_id = :toUserId)
           OR (c.from_user_id = :toUserId AND c.to_user_id = :fromUserId)
        ORDER BY c.timestamp ASC
    ";

    $stmt = $_db->prepare($query);
    $stmt->bindParam(':fromUserId', $fromUserId, PDO::PARAM_INT);
    $stmt->bindParam(':toUserId', $toUserId, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as $message) {
        // Differentiate between admin and member messages
        $isFromAdmin = ($message['from_user_id'] == $_SESSION['user']->id);
        $messageClass = $isFromAdmin ? 'from-admin' : 'from-member';

        // Display the message with the sender's name
        echo '<div class="chat-message ' . $messageClass . '">';
        echo '<strong>' . htmlspecialchars($message['from_user_name']) . ':</strong> '; // Display name
        echo htmlspecialchars($message['message']);
        echo '</div>';
    }
}
?>
