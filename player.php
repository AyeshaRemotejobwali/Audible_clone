<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if audiobook ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: library.php");
    exit;
}

$book_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Fetch audiobook details
    $stmt = $pdo->prepare("SELECT audiobook_id, title, author, description, cover_image_path, audio_file_path, duration_seconds FROM audiobooks WHERE audiobook_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        error_log("Audiobook not found for ID: " . $book_id);
        header("Location: library.php");
        exit;
    }

    // Fetch user progress
    $stmt = $pdo->prepare("SELECT current_position FROM listening_progress WHERE user_id = ? AND audiobook_id = ?");
    $stmt->execute([$user_id, $book_id]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    $start_time = $progress ? (int)$progress['current_position'] : 0;

    // Handle add to library
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_library'])) {
        $stmt = $pdo->prepare("INSERT INTO user_library (user_id, audiobook_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE added_at = NOW()");
        $stmt->execute([$user_id, $book_id]);
        error_log("Added audiobook ID $book_id to user ID $user_id library");
    }

} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log("Database error in player.php: " . $e->getMessage());
    $error = "Unable to load audiobook. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player - Audible Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f4f4f9; color: #333; }
        header { background: #131921; color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 24px; }
        nav a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 16px; }
        nav a:hover { color: #f0c14b; }
        .player { padding: 40px; display: flex; gap: 40px; max-width: 1200px; margin: 0 auto; }
        .book-info { flex: 1; text-align: center; }
        .book-info img { width: 100%; max-width: 300px; border-radius: 8px; }
        .book-info h2 { font-size: 28px; margin: 20px 0; }
        .book-info p { font-size: 16px; color: #666; }
        .audio-player { flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        audio { width: 100%; margin-bottom: 20px; }
        .controls { display: flex; justify-content: space-between; align-items: center; }
        .controls button { background: #f0c14b; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; border-radius: 4px; }
        .controls button:hover { background: #e5b109; }
        .speed-control select { padding: 10px; font-size: 16px; border-radius: 4px; }
        .error { color: red; text-align: center; margin-bottom: 20px; }
        footer { background: #131921; color: #fff; text-align: center; padding: 20px; margin-top: 40px; }
        @media (max-width: 768px) {
            .player { flex-direction: column; }
            .book-info img { max-width: 200px; }
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
    <section class="player">
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>
            <div class="book-info">
                <img src="<?php echo htmlspecialchars($book['cover_image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                <p><?php echo htmlspecialchars($book['author']); ?></p>
                <p><?php echo htmlspecialchars($book['description']); ?></p>
                <form method="POST" action="">
                    <input type="hidden" name="add_to_library" value="1">
                    <button type="submit">Add to Library</button>
                </form>
            </div>
            <div class="audio-player">
                <audio id="audio" src="<?php echo htmlspecialchars($book['audio_file_path']); ?>" preload="auto"></audio>
                <div class="controls">
                    <button onclick="rewind()">-10s</button>
                    <button id="playPause">Play</button>
                    <button onclick="forward()">+10s</button>
                    <div class="speed-control">
                        <select id="speed" onchange="changeSpeed()">
                            <option value="1">1x</option>
                            <option value="1.5">1.5x</option>
                            <option value="2">2x</option>
                        </select>
                    </div>
                </div>
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
        <?php if (!isset($error)): ?>
        const audio = document.getElementById('audio');
        const playPauseBtn = document.getElementById('playPause');
        const speedControl = document.getElementById('speed');
        audio.currentTime = <?php echo $start_time; ?>;
        playPauseBtn.addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
                playPauseBtn.textContent = 'Pause';
            } else {
                audio.pause();
                playPauseBtn.textContent = 'Play';
            }
        });
        function rewind() {
            audio.currentTime -= 10;
        }
        function forward() {
            audio.currentTime += 10;
        }
        function changeSpeed() {
            audio.playbackRate = parseFloat(speedControl.value);
        }
        audio.addEventListener('timeupdate', () => {
            fetch('save_progress.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `save_progress=1&current_time=${Math.floor(audio.currentTime)}&id=<?php echo $book_id; ?>`
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
