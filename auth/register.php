<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/../includes/config.php');

$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    try {
        $stmt->execute([$username, $password]);
        $feedback = "<div class='text-green-600 text-center mt-4'>User registered. <a class='text-blue-600 underline' href='login.php'>Login</a></div>";
    } catch (PDOException $e) {
        $feedback = "<div class='text-red-600 text-center mt-4'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="bg-white shadow-md rounded-xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Register Admin</h2>

    <?php if ($feedback): ?>
        <?= $feedback ?>
    <?php endif; ?>

    <form method="post" class="space-y-4 mt-4">
        <div>
            <label class="block text-gray-700 mb-1">Username</label>
            <input name="username" type="text" placeholder="Username" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-gray-700 mb-1">Password</label>
            <input name="password" type="password" placeholder="Password" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Register</button>
    </form>
</div>
</body>
</html>