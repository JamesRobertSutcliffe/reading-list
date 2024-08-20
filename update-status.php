<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE reading_lists SET status = ? WHERE book_id = ?");
    $stmt->bind_param("ss", $status, $book_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
