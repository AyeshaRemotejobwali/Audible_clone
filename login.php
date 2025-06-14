<?php
session_start();
include 'db.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // Attempt login if no validation errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id, password_hash FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                error_log("User logged in: $email (user_id: {$user['user_id']})");
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Invalid email or password.";
                error_log("Login failed for email: $email");
            }
        } catch (PDOException $e) {
            error_log("Database error in login.php: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Audible Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f4f4f9; color: #333; }
        header { background: #131921; color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 24px; }
        nav a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 16px; }
        nav a:hover { color: #f0c14b; }
        .login { padding: 40px; max-width: 500px; margin: 0 auto; }
        .login h2 { font-size: 32px; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 16px; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; }
        .form-group input:focus { outline: none; border-color: #f0c14b; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; text-align: center; }
        button { background: #f0c14b; border: none; padding: 12px; width: 100%; cursor: pointer; font-size: 16px; border-radius: 4px; }
        button:hover { background: #e5b109; }
        footer { background: #131921; color: #fff; text-align: center; padding: 20px; margin-top: 40px; }
        @media (max-width: 768px) {
            .login { padding: 20px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Audible Clone</h1>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <a href="#" onclick="redirect('library.php')">Library</a>
            <a href="#" onclick="redirect('signup.php')">Signup</a>
        </nav>
    </header>
    <section class="login">
        <h2>Login</h2>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
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
