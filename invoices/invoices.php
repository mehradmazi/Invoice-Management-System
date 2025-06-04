<?php
session_start();
include(__DIR__ . '/../includes/config.php');
include(__DIR__ . '/../includes/encryption.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("
    SELECT invoices.*, clients.name AS client_name
    FROM invoices
    JOIN clients ON invoices.client_id = clients.id
    ORDER BY invoices.invoice_date DESC
");
$invoices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
<div class="max-w-6xl mx-auto bg-white p-6 sm:p-8 rounded-xl shadow-md">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <h2 class="text-2xl font-bold text-gray-800">Invoices</h2>
        <div class="flex gap-2">
            <a href="edit_invoice.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">+ New Invoice</a>
            <a href="dashboard.php" class="text-blue-600 hover:underline text-sm">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            Invoice deleted successfully.
        </div>
    <?php endif; ?>

    <?php if (count($invoices) === 0): ?>
        <p class="text-gray-600">No invoices found.</p>
    <?php else: ?>
        <div class="overflow-auto border border-gray-200 rounded-md">
            <table class="min-w-full text-sm text-left text-gray-800">
                <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-3">Invoice #</th>
                    <th class="p-3">Client</th>
                    <th class="p-3">Date</th>
                    <th class="p-3 text-right">Total</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3"><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($invoice['client_name']) ?></td>
                        <td class="p-3"><?= $invoice['invoice_date'] ?></td>
                        <td class="p-3 text-right">$<?= number_format($invoice['total'], 2) ?></td>
                        <td class="p-3 text-center space-x-3">
                            <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="text-blue-600 hover:underline">View</a>
                            <a href="delete_invoice.php?id=<?= $invoice['id'] ?>" onclick="return confirm('Are you sure you want to delete this invoice?')" class="text-red-600 hover:underline">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>