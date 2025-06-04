<?php
session_start();
include(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: invoices.php");
    exit;
}

$invoice_id = (int) $_GET['id'];

// Check that invoice exists
$stmt = $pdo->prepare("SELECT id FROM invoices WHERE id = ?");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    header("Location: invoices.php?error=notfound");
    exit;
}

// Delete invoice items first
$pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$invoice_id]);

// Then delete the invoice
$pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([$invoice_id]);

header("Location: invoices.php?deleted=1");
exit;
?>