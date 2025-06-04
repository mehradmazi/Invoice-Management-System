<?php
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/encryption.php';

function getSiteOptions($pdo) {
    $stmt = $pdo->query("SELECT * FROM options LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function initializeOptions($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM options");
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO options (business_name, business_number, bank_name, e_transfer_email) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'Your Business Name',
            encrypt('123456789'),
            'Your Bank Name',
            'your@email.com'
        ]);
    }
}