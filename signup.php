<?php
session_start();
include 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check for duplicate username or email
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username or email already exists.";
            }
        } catch (PDOException $e) {
            error_log("Database error checking duplicates in signup.php: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }

    // Insert user if no errors
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash]);
            $success = "Account created successfully! <a href='login.php'>Login here</a>.";
            error_log("User registered: $username ($email)");
        } catch (PDOException $e) {
            error_log("Database error inserting user in signup.php: " . $e->getMessage());
            $errors[] = "An error occurred while creating your account. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Audible Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f4f4f9; color: #333; }
        header { background: #131921; color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 24px; }
        nav a { color: #fff; margin-left: 20px; text-decoration: none; font-size: 16px; }
        nav a:hover { color: #f0c14b; }
        .signup { padding: 40px; max-width: 500px; margin: 0 auto; }
        .signup h2 { font-size: 32px; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 16px; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; }
        .form-group input:focus { outline: none; border-color: #f0c14b; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; text-align: center; }
        .success { color: green; font-size: 16px; text-align: center; margin-bottom: 10px; }
        button { background: #f0c14b; border: none; padding: 12px; width: 100%; cursor: pointer; font-size: 16px; border-radius: 4px; }
        button:hover { background: #e5b109; }
        footer { background: #131921; color: #fff; text-align: center; padding: 20px; margin-top: 40px; }
        @media (max-width: 768px) {
            .signup { padding: 20px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Audible Clone</h1>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <a href="#" onclick="redirect('library.php')">Library</a>
            <a href="#" onclick="redirect('login.php')">Login</a>
        </nav>
    </header>
    <section class="signup">
        <h2>Create Account</h2>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Sign Up</button>
            </form>
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
