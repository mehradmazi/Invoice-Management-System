<?php
session_start();
include(__DIR__ . '/../includes/config.php');

$message = '';
$actionLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = $pdo->prepare("SELECT id FROM clients WHERE name = ?");
    $stmt->execute([$name]);
    $existing = $stmt->fetch();

    if ($existing) {
        $message = "Client already exists.";
        $actionLink = "<a href='invoice.php?id=" . htmlspecialchars($existing['id']) . "' class='text-blue-600 underline'>Generate Invoice</a>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO clients (name, address, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $address, $email, $phone]);
        $id = $pdo->lastInsertId();
        $message = "Client added successfully.";
        $actionLink = "<a href='invoice.php?id=$id' class='text-blue-600 underline'>Generate Invoice</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
<div class="w-full max-w-md bg-white p-6 rounded-xl shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Add New Client</h2>
        <a href="../invoices/dashboard.php" class="text-blue-600 text-sm hover:underline">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded text-sm">
            <?= htmlspecialchars($message) ?><br>
            <?= $actionLink ?>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block font-medium text-gray-700">Name</label>
            <input type="text" name="name" required class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block font-medium text-gray-700">Address</label>
            <textarea name="address" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div>
            <label class="block font-medium text-gray-700">Email</label>
            <input type="email" name="email" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block font-medium text-gray-700">Phone</label>
            <input type="text" name="phone" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit" class="w-full py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
            Add Client
        </button>
    </form>
</div>
</body>
</html>