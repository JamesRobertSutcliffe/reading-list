<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM reading_lists WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
    $api_key = 'YOUR_GOOGLE_BOOKS_API_KEY';
    $api_url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($search_query) . "&key=" . $api_key;

    $response = file_get_contents($api_url);
    $books = json_decode($response, true)['items'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Your Reading List</h2>
    <a href="logout.php">Logout</a>

    <h3>Search for Books</h3>
    <form action="dashboard.php" method="POST">
        <input type="text" name="search_query" placeholder="Search for books...">
        <button type="submit">Search</button>
    </form>
    
    <?php if (isset($books)): ?>
        <h3>Search Results</h3>
        <div id="search-results">
            <?php foreach ($books as $book): ?>
                <div class="book">
                    <img src="<?= $book['volumeInfo']['imageLinks']['thumbnail'] ?? 'placeholder.jpg' ?>" alt="Book cover">
                    <h3><?= $book['volumeInfo']['title'] ?></h3>
                    <p><?= implode(', ', $book['volumeInfo']['authors'] ?? ['Unknown author']) ?></p>
                    <p><?= $book['volumeInfo']['description'] ?? 'No description available' ?></p>
                    <form action="add-book.php" method="POST">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <input type="hidden" name="title" value="<?= $book['volumeInfo']['title'] ?>">
                        <input type="hidden" name="author" value="<?= implode(', ', $book['volumeInfo']['authors'] ?? ['Unknown author']) ?>">
                        <input type="hidden" name="cover" value="<?= $book['volumeInfo']['imageLinks']['thumbnail'] ?? 'placeholder.jpg' ?>">
                        <input type="hidden" name="description" value="<?= $book['volumeInfo']['description'] ?? 'No description available' ?>">
                        <button type="submit">Add to Reading List</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3>Your Books</h3>
    <div id="reading-list">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="book">
                <img src="<?= $row['cover'] ?>" alt="Book cover">
                <h3><?= $row['title'] ?></h3>
                <p><?= $row['author'] ?></p>
                <p><?= $row['description'] ?></p>
                <form action="update-status.php" method="POST">
                    <select name="status" onchange="this.form.submit()">
                        <option value="to-read" <?= $row['status'] == 'to-read' ? 'selected' : '' ?>>To Read</option>
                        <option value="in-progress" <?= $row['status'] == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="read" <?= $row['status'] == 'read' ? 'selected' : '' ?>>Read</option>
                    </select>
                    <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
