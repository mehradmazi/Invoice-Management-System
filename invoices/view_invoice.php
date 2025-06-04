<?php
session_start();
include(__DIR__ . '/../includes/config.php');
include(__DIR__ . '/../includes/encryption.php');
include(__DIR__ . '/../includes/options_functions.php');
initializeOptions($pdo);

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: invoices.php");
    exit;
}

$invoice_id = (int) $_GET['id'];

// Fetch invoice and client info
$stmt = $pdo->prepare("
    SELECT invoices.*, clients.name AS client_name, clients.email AS client_email
    FROM invoices
    JOIN clients ON invoices.client_id = clients.id
    WHERE invoices.id = ?
");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    echo "Invoice not found.";
    exit;
}

// Fetch invoice items
$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt->execute([$invoice_id]);
$items = $stmt->fetchAll();

// Fetch decrypted business and payment info
$options = getSiteOptions($pdo);
$business_number = decrypt($options['business_number'] ?? '');
$my_name = $options['business_name'] ?? '';
$e_transfer_email = $options['e_transfer_email'] ?? '';
$bank_name = $options['bank_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<?php if (isset($_GET['success'])): ?>
    <div class="mb-4 bg-green-100 border border-green-300 text-green-800 p-3 rounded">
        Invoice created successfully.
    </div>
<?php endif; ?>

<!-- Back + Print Buttons -->
<div class="max-w-4xl mx-auto mb-4 flex justify-between no-print">
    <a href="invoices.php" class="text-sm text-blue-600 hover:underline">&larr; Back to Invoices</a>
    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">🖨️ Print Invoice</button>
</div>

<!-- Invoice Container -->
<div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-md">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900">Mehrad Mazaheri – Invoice</h2>
            <p class="text-gray-700 text-sm mt-1">Invoice #: <?= htmlspecialchars($invoice['invoice_number']) ?></p>
        </div>
        <img src="https://mazaheri.ca/wp-content/uploads/2022/09/Mehrad-Mazaheri-Logo.webp" alt="Mehrad Mazaheri Logo" class="w-24 h-24 object-contain">
    </div>

    <!-- Client & Dates -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700 mb-6">
        <div>
            <p><strong>Client:</strong> <?= htmlspecialchars($invoice['client_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($invoice['client_email']) ?></p>
        </div>
        <div>
            <p><strong>Invoice Date:</strong> <?= $invoice['invoice_date'] ?></p>
            <p><strong>Due Date:</strong> <?= $invoice['due_date'] ?></p>
        </div>
    </div>

    <!-- Tasks Table -->
    <table class="w-full text-sm mb-6">
        <thead class="bg-gray-200 text-gray-700">
        <tr>
            <th class="p-2 text-left">Task</th>
            <th class="p-2 text-right">Hours</th>
            <th class="p-2 text-right">Rate</th>
            <th class="p-2 text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr class="border-t">
                <td class="p-2"><?= htmlspecialchars($item['task_description']) ?></td>
                <td class="p-2 text-right"><?= $item['hours'] ?></td>
                <td class="p-2 text-right">$<?= number_format($item['rate'], 2) ?></td>
                <td class="p-2 text-right">$<?= number_format($item['total'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="text-right text-sm text-gray-800 mb-6">
        <p><strong>Subtotal:</strong> $<?= number_format($invoice['subtotal'], 2) ?></p>
        <p><strong>GST/HST (5%):</strong> $<?= number_format($invoice['tax'], 2) ?></p>
        <p class="text-lg font-bold mt-2">Total: $<?= number_format($invoice['total'], 2) ?></p>
    </div>

    <!-- Notes -->
    <?php if (!empty($invoice['additional_notes'])): ?>
        <div class="mb-6 bg-gray-50 p-4 rounded border text-sm">
            <strong>Notes:</strong>
            <p><?= nl2br(htmlspecialchars($invoice['additional_notes'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="mt-8 border-t pt-6 text-sm text-gray-600">
        <p><strong>Issued by:</strong> <?= htmlspecialchars($my_name) ?></p>
        <p><strong>Business Number:</strong> <?= htmlspecialchars($business_number) ?></p>
        <p><strong>E-Transfer:</strong> <?= htmlspecialchars($e_transfer_email) ?></p>
        <p><strong>Bank:</strong> <?= htmlspecialchars($bank_name) ?></p>
    </div>
</div>

</body>
</html>