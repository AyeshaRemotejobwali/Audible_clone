<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Updated query to match the latest schema
    $stmt = $pdo->prepare("
        SELECT 
            a.audiobook_id, 
            a.title, 
            a.author, 
            a.cover_image_path, 
            a.duration_seconds, 
            lp.current_position, 
            c.category_name AS category 
        FROM user_library ul 
        JOIN audiobooks a ON ul.audiobook_id = a.audiobook_id 
        LEFT JOIN listening_progress lp ON lp.audiobook_id = a.audiobook_id AND lp.user_id = ? 
        JOIN categories c ON a.category_id = c.category_id 
        WHERE ul.user_id = ?
    ");
    $stmt->execute([$user_id, $user_id]);
    $audiobooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error (in production, log to a file instead of displaying)
    error_log("Database error: " . $e->getMessage());
    $error = "Unable to load your library. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Audible Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f4f4f9; color: #333; }
        header { background: #131921; color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 24px; }
        nav a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 16px; }
        nav a:hover { color: #f0c14b; }
        .dashboard { padding: 40px; }
        .dashboard h2 { font-size: 32px; margin-bottom: 20px; }
        .error { color: red; text-align: center; margin-bottom: 20px; }
        .audiobook-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .audiobook-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .audiobook-card img { width: 100%; height: 200px; object-fit: cover; }
        .audiobook-card h3 { font-size: 18px; padding: 10px; }
        .audiobook-card p { font-size: 14px; color: #666; padding: 0 10px 10px; }
        .audiobook-card button { background: #f0c14b; border: none; padding: 10px; width: 100%; cursor: pointer; font-size: 16px; }
        .audiobook-card button:hover { background: #e5b109; }
        .progress-bar { background: #ddd; height: 5px; margin: 10px 0; }
        .progress-bar div { background: #f0c14b; height: 100%; }
        footer { background: #131921; color: #fff; text-align: center; padding: 20px; margin-top: 40px; }
        @media (max-width: 768px) {
            .audiobook-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Audible Clone</h1>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <a href="#" onclick="redirect('library.php')">Library</a>
            <a href="#" onclick="redirect('dashboard.php')">Dashboard</a>
            <a href="#" onclick="redirect('logout.php')">Logout</a>
        </nav>
    </header>
    <section class="dashboard">
        <h2>Your Library</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (empty($audiobooks)): ?>
            <p>No audiobooks in your library yet.</p>
        <?php else: ?>
            <div class="audiobook-grid">
                <?php foreach ($audiobooks as $book): ?>
                    <div class="audiobook-card">
                        <img src="<?php echo htmlspecialchars($book['cover_image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p><?php echo htmlspecialchars($book['author']); ?> | <?php echo htmlspecialchars($book['category']); ?></p>
                        <div class="progress-bar">
                            <div style="width: <?php echo ($book['current_position'] / $book['duration_seconds']) * 100; ?>%"></div>
                        </div>
                        <button onclick="redirect('player.php?id=<?php echo $book['audiobook_id']; ?>')">Continue Listening</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <footer>
        <p>© 2025 Audible Clone.</p>
    </footer>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
