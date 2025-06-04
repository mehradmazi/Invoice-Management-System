<?php
session_start();
include(__DIR__ . '/../includes/config.php');
include(__DIR__ . '/../includes/encryption.php');
include(__DIR__ . '/../includes/options_functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch business number from site-wide options and decrypt it
$options = getSiteOptions($pdo);
$business_number = !empty($options['business_number']) ? decrypt($options['business_number']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = (int) ($_POST['client_id'] ?? 0);
    $notes = trim($_POST['additional_notes'] ?? '');
    $tasks = $_POST['task'] ?? [];
    $hours = $_POST['hours'] ?? [];
    $rates = $_POST['rate'] ?? [];

    // Validate client exists
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    if (!$client) {
        die("Client not found.");
    }

    $items = [];
    $subtotal = 0;

    for ($i = 0; $i < count($tasks); $i++) {
        $task = trim($tasks[$i] ?? '');
        $hrs = (float) ($hours[$i] ?? 0);
        $rate = (float) ($rates[$i] ?? 0);
        $total = $hrs * $rate;

        if ($task !== '') {
            $subtotal += $total;
            $items[] = [
                'description' => $task,
                'hours' => $hrs,
                'rate' => $rate,
                'total' => $total
            ];
        }
    }

    $tax = $subtotal * 0.05;
    $grand_total = $subtotal + $tax;

    $invoice_number = 'INV-' . date('Ymd-His');
    $date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+30 days'));

    // Insert invoice
    $stmt = $pdo->prepare("
        INSERT INTO invoices (
            client_id, invoice_number, invoice_date, due_date, 
            subtotal, tax, total, additional_notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $client_id,
        $invoice_number,
        $date,
        $due_date,
        $subtotal,
        $tax,
        $grand_total,
        $notes
    ]);

    $invoice_id = $pdo->lastInsertId();

    file_put_contents('notes_debug.log', print_r([
        'notes' => $notes,
        'raw_post' => $_POST['additional_notes'] ?? null
    ], true));
    
    // Insert invoice items
    $stmt = $pdo->prepare("
        INSERT INTO invoice_items (invoice_id, task_description, hours, rate, total) 
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmt->execute([
            $invoice_id,
            $item['description'],
            $item['hours'],
            $item['rate'],
            $item['total']
        ]);
    }

    // Redirect to the invoice view page
    header("Location: view_invoice.php?id=" . $invoice_id . "&success=1");
    exit;
}
?>