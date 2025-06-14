<?php
session_start();
include 'db.php';

try {
    // Fetch audiobooks with category names
    $stmt = $pdo->prepare("
        SELECT 
            a.audiobook_id, 
            a.title, 
            a.author, 
            a.cover_image_path, 
            a.description, 
            c.category_name AS category 
        FROM audiobooks a 
        JOIN categories c ON a.category_id = c.category_id 
        LIMIT 4
    ");
    $stmt->execute();
    $audiobooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($audiobooks) . " audiobooks for index.php");
} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log("Database error in index.php: " . $e->getMessage());
    $error = "Unable to load audiobooks. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audible Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f4f4f4f9; color: #333; }
        header { background: #131921f; color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 24px; }
        nav a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 16px; }
        nav a:hover { color: #f0c14b;14b; }
        .hero { { background: url('hero-bg.jpg'); background-size: cover; padding: 100px 40px; text-align: center; color: #fff; }
        .hero h2 { font-size: 48px; margin-bottom: 20px; }
        .hero h2 { font-size: p; font-size: 18px; margin-bottom: 20px; }
        p { font-size: .hero p { font-size: 18px; margin-bottom: 20px; margin-bottom: 20px; }
        .hero button { background: #f0c14b;14b; border: none;none; padding: 15px 30px; cursor: pointer; font-size: 18px; border-radius: 4px; }
        .hero button:hover { background: #e5b109; }
        .audiobooks { padding: 40px; }
        .audiobooks h2 { font-size: 32px; margin-bottom: 20px; }
        .error { color: red;red; text-align:  center; margin-bottom: margin-bottom: 20px; }
        .audiobook-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .audiobook-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .audiobook-card img { width: 100%; height: 200px; height: 200px; object-fit: cover; }
        .audiobook-card h3 { font-size: 18px; padding: 10px; }
        .audiobook-card p { font-size: 14px; color: #666; padding: 0 10px 10px; }
        .audiobook-card button { background: #f0c14b; border: none; padding: 10px; width: 100%; cursor: pointer; font-size: 16px; }
        .audiobook-card button:hover { background: #e5b109; }
        footer { background: #131921; color: #fff; text-align: center; padding: 20px; margin-top: 40px; }
        @media (max-width: 768px) {
            .audiobook-grid { grid-template-columns: 1fr; }
            .hero h2 { font-size: 32px; }
            .hero p { font-size: 16px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Audible Clone</h1>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <a href="#" onclick="redirect('library.php')">Library</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="redirect('dashboard.php')">Dashboard</a>
                <a href="#" onclick="redirect('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="redirect('login.php')">Login</a>
                <a href="#" onclick="redirect('signup.php')">Signup</a>
            <?php endif; ?>
        </nav>
    </header>
    <section class="hero">
        <h2>Discover Your Next Audiobook</h2>
        <p>Explore a vast library of audiobooks across genres.</p>
        <button onclick="redirect('library.php')">Browse Now</button>
    </section>
    <section class="audiobooks">
        <h2>Featured Audiobooks</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (empty($audiobooks)): ?>
            <p>No audiobooks available at the moment.</p>
        <?php else: ?>
            <div class="audiobook-grid">
                <?php foreach ($audiobooks as $book): ?>
                    <div class="audiobook-card">
                        <img src="<?php echo htmlspecialchars($book['cover_image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p><?php echo htmlspecialchars($book['author']); ?> | <?php echo htmlspecialchars($book['category']); ?></p>
                        <button onclick="redirect('player.php?id=<?php echo htmlspecialchars($book['audiobook_id']); ?>')">Listen Now</button>
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
