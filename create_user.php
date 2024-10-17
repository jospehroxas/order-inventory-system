<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php';

    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Check if the user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$inputUsername]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        $error = "User '$inputUsername' already exists. Please choose a different username.";
    } else {
        // Hash the password and insert a new user
        $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$inputUsername, $hashedPassword]);
        $success = "User created successfully! You can now log in.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Create User</h1>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Create User">
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
    </form>
    <p>Already have an account? <a href="login.php">Log in here</a></p>
</body>
</html>
