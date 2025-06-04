<?php
session_start();
include(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch total number of clients
$stmt = $pdo->query("SELECT COUNT(*) FROM clients");
$total_clients = (int) $stmt->fetchColumn();

// Fetch total number of invoices
$stmt = $pdo->query("SELECT COUNT(*) FROM invoices");
$total_invoices = (int) $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- Sidebar -->
<div class="w-64 bg-gray-900 text-white p-6 space-y-4 min-h-screen">
    <h2 class="text-xl font-bold mb-6">Invoice Menu</h2>
    <a href="../clients/add_client.php" class="block w-full px-4 py-2 rounded hover:bg-white hover:text-black transition">➕ Add Client</a>
    <a href="edit_invoice.php" class="block w-full px-4 py-2 rounded hover:bg-white hover:text-black transition">📝 New Invoice</a>
    <a href="invoices.php" class="block w-full px-4 py-2 rounded hover:bg-white hover:text-black transition">📄 View Invoices</a>
    <a href="../includes/options.php" class="block w-full px-4 py-2 rounded hover:bg-white hover:text-black transition">⚙️ Business Settings</a>
    <a href="logout.php" class="block w-full px-4 py-2 rounded hover:bg-white hover:text-black transition">🔒 Logout</a>
    <p class="text-xs text-gray-400 mt-6">&copy; <?= date('Y') ?> Mehrad's Invoice Manager</p>
</div>

<!-- Main Content -->
<div class="flex-1 p-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Dashboard Overview</h1>
    <p class="text-sm text-gray-500 mb-8">Welcome back!</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Chart 1 -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">System Overview</h2>
            <canvas id="summaryChart"></canvas>
        </div>

        <!-- Chart 2 -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Earnings Breakdown</h2>
            <canvas id="earningsChart"></canvas>
        </div>
    </div>
</div>

<script>
    const summaryChartCtx = document.getElementById('summaryChart').getContext('2d');
    new Chart(summaryChartCtx, {
        type: 'bar',
        data: {
            labels: ['Clients', 'Invoices'],
            datasets: [{
                label: 'Total Count',
                data: [<?= $total_clients ?>, <?= $total_invoices ?>],
                backgroundColor: ['#3B82F6', '#10B981']
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const earningsChartCtx = document.getElementById('earningsChart').getContext('2d');
    new Chart(earningsChartCtx, {
        type: 'pie',
        data: {
            labels: ['Design', 'Consulting', 'Hosting'],
            datasets: [{
                label: 'Earnings',
                data: [500, 300, 200],
                backgroundColor: ['#F59E0B', '#6366F1', '#EF4444']
            }]
        }
    });
</script>
</body>
</html>