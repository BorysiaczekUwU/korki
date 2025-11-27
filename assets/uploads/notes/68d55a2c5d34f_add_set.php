<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $pdo->prepare("INSERT INTO sets (user_id, title) VALUES (?, ?)");
    $stmt->execute([$user_id, $title]);

    $set_id = $pdo->lastInsertId();
    $lines = explode("\n", $content);

    foreach ($lines as $line) {
        if (strpos($line, '-') !== false) {
            list($term, $definition) = array_map('trim', explode('-', $line, 2));
            $stmt = $pdo->prepare("INSERT INTO cards (set_id, term, definition) VALUES (?, ?, ?)");
            $stmt->execute([$set_id, $term, $definition]);
        }
    }
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj zestaw</title>
    <link rel="stylesheet" href="style/add.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <div class="add-box">
        <h1>Dodaj nowy zestaw</h1>
        <form method="post">
            <input type="text" name="title" placeholder="Tytuł zestawu" required>
            <textarea name="content" placeholder="Podaj fiszki w formacie '[słowo] - [znaczenie]'" rows="10" required></textarea>
            <button type="submit">Dodaj zestaw</button>
        </form>
        <a href="dashboard.php" class="button">Wróć</a>
    </div>
    </div>
</body>
</html>
