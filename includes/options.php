<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/encryption.php';
include_once __DIR__ . '/options_functions.php';

// Show all errors for debugging (remove on production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

initializeOptions($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name']);
    $business_number = encrypt(trim($_POST['business_number']));
    $bank_name = trim($_POST['bank_name']);
    $e_transfer_email = trim($_POST['e_transfer_email']);

    $stmt = $pdo->prepare("UPDATE options SET business_name = ?, business_number = ?, bank_name = ?, e_transfer_email = ? LIMIT 1");
    $stmt->execute([$business_name, $business_number, $bank_name, $e_transfer_email]);

    header("Location: options.php?success=1");
    exit;
}

$options = getSiteOptions($pdo);
$decrypted_bn = decrypt($options['business_number']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
<div class="max-w-xl mx-auto bg-white shadow p-6 rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Business Settings</h1>
        <a href="../invoices/dashboard.php" class="text-blue-600 hover:underline text-sm">&larr; Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Settings updated successfully.</div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label for="business_name" class="block font-medium text-gray-700 mb-1">Business Name</label>
            <input type="text" id="business_name" name="business_name" class="w-full border p-2 rounded" value="<?= htmlspecialchars($options['business_name']) ?>" required>
        </div>
        <div>
            <label for="business_number" class="block font-medium text-gray-700 mb-1">Business Number</label>
            <input type="text" id="business_number" name="business_number" class="w-full border p-2 rounded" value="<?= htmlspecialchars($decrypted_bn) ?>" required>
        </div>
        <div>
            <label for="bank_name" class="block font-medium text-gray-700 mb-1">Bank Name</label>
            <input type="text" id="bank_name" name="bank_name" class="w-full border p-2 rounded" value="<?= htmlspecialchars($options['bank_name']) ?>" required>
        </div>
        <div>
            <label for="e_transfer_email" class="block font-medium text-gray-700 mb-1">E-Transfer Email</label>
            <input type="email" id="e_transfer_email" name="e_transfer_email" class="w-full border p-2 rounded" value="<?= htmlspecialchars($options['e_transfer_email']) ?>" required>
        </div>
        <div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Save Settings</button>
        </div>
    </form>
</div>
</body>
</html>