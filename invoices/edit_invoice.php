<?php
session_start();
include(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch clients
$clients = $pdo->query("SELECT * FROM clients ORDER BY name")->fetchAll();

// Fetch unassigned tracked tasks
$stmt = $pdo->prepare("SELECT * FROM tracked_tasks WHERE user_id = ? AND invoice_id IS NULL ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$unassigned_tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-4">
<div class="max-w-5xl mx-auto bg-white shadow-md p-6 rounded-xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2 sm:mb-0">Create Invoice</h2>
        <a href="dashboard.php" class="text-sm text-blue-600 hover:underline">&larr; Back to Dashboard</a>
    </div>

    <form method="post" action="generate_invoice.php" autocomplete="off">
        <div class="mb-6">
            <label for="client_id" class="block mb-2 font-medium text-gray-700">Select Client</label>
            <select name="client_id" id="client_id" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">-- Select Client --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($unassigned_tasks)): ?>
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Saved Tracked Tasks</h3>
                <div class="space-y-2">
                    <?php foreach ($unassigned_tasks as $task): ?>
                        <div class="p-4 bg-gray-100 rounded-md flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <div>
                                <p class="font-medium"><?= htmlspecialchars($task['task_description']) ?></p>
                                <p class="text-sm text-gray-600">
                                    <?= date("g:i A", strtotime($task['start_time'])) ?> &ndash; <?= date("g:i A", strtotime($task['end_time'])) ?> (<?= $task['hours'] ?> hrs)
                                </p>
                            </div>
                            <button type="button" class="import-task px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                                    data-id="<?= $task['id'] ?>"
                                    data-desc="<?= htmlspecialchars($task['task_description'], ENT_QUOTES) ?>"
                                    data-hours="<?= $task['hours'] ?>">
                                + Import to Invoice
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Time Tracker</h3>
            <div class="flex flex-col md:flex-row md:items-center md:space-x-4 gap-2">
                <input type="text" id="tracked_task" class="flex-1 border p-2 rounded" placeholder="Task Description" />
                <button type="button" onclick="startTimer()" id="startBtn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">▶ Start</button>
                <button type="button" onclick="stopTimer()" id="stopBtn" disabled class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">■ Stop</button>
            </div>
            <p class="text-sm text-gray-600 mt-2" id="timerStatus">Timer not started.</p>
        </div>

        <div>
            <h3 class="text-lg font-medium mb-4 text-gray-800">Tasks</h3>
            <div id="items" class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center md:space-x-4 gap-2">
                    <input name="task[]" class="flex-1 border p-2 rounded" placeholder="Task Description" required>
                    <input name="hours[]" type="number" min="0.1" step="0.1" value="1" class="w-full md:w-24 border p-2 rounded" placeholder="Hours" required>
                    <input name="rate[]" type="number" step="0.01" class="w-full md:w-28 border p-2 rounded" placeholder="Rate/hr" required>
                    <button type="button" onclick="removeItem(this)" class="text-red-500 text-sm">Remove</button>
                </div>
            </div>
            <div class="mt-4">
                <button type="button" onclick="addItem()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    + Add Task
                </button>
            </div>
        </div>

        <div class="mt-6">
            <label for="additional_notes" class="block mb-2 font-medium text-gray-700">Additional Notes</label>
            <textarea name="additional_notes" id="additional_notes" rows="4"
                      class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Write any extra details or notes for this invoice..."><?php if (isset($invoice['additional_notes'])) echo htmlspecialchars($invoice['additional_notes']); ?></textarea>
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full py-3 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">
                Generate Invoice
            </button>
        </div>
    </form>
</div>

<script>
    let startTime, endTime;

    function startTimer() {
        const taskInput = document.getElementById('tracked_task');
        if (!taskInput.value.trim()) {
            alert('Please enter a task description before starting the timer.');
            return;
        }
        startTime = new Date();
        document.getElementById('startBtn').disabled = true;
        document.getElementById('stopBtn').disabled = false;
        document.getElementById('timerStatus').textContent = `Started at: ${startTime.toLocaleTimeString()}`;
    }

    async function stopTimer() {
        endTime = new Date();
        const elapsedMs = endTime - startTime;
        const hours = (elapsedMs / 1000 / 60 / 60).toFixed(2);
        const taskDesc = document.getElementById('tracked_task').value;

        const response = await fetch('../tasks/track_task.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                description: taskDesc,
                start_time: startTime.toISOString(),
                end_time: endTime.toISOString(),
                hours: hours
            })
        });

        const result = await response.json();
        if (result.status === 'success') {
            const container = document.getElementById('items');
            const div = document.createElement('div');
            div.className = "flex flex-col md:flex-row md:items-center md:space-x-4 gap-2 mt-4";
            div.innerHTML = `
                <input name="task[]" value="${taskDesc}" class="flex-1 border p-2 rounded" required>
                <input name="hours[]" value="${hours}" class="w-full md:w-24 border p-2 rounded" required>
                <input name="rate[]" type="number" step="0.01" class="w-full md:w-28 border p-2 rounded" placeholder="Rate/hr" required>
                <button type="button" onclick="removeItem(this)" class="text-red-500 text-sm">Remove</button>
            `;
            container.appendChild(div);

            document.getElementById('timerStatus').textContent = `Stopped at: ${endTime.toLocaleTimeString()} (Saved, Duration: ${hours} hrs)`;
        } else {
            alert('Failed to save task.');
        }

        document.getElementById('startBtn').disabled = false;
        document.getElementById('stopBtn').disabled = true;
        document.getElementById('tracked_task').value = '';
    }

    function addItem() {
        const container = document.getElementById('items');
        const div = document.createElement('div');
        div.className = "flex flex-col md:flex-row md:items-center md:space-x-4 gap-2 mt-4";
        div.innerHTML = `
            <input name="task[]" class="flex-1 border p-2 rounded" placeholder="Task Description" required>
            <input name="hours[]" type="number" min="0.1" step="0.1" value="1" class="w-full md:w-24 border p-2 rounded" placeholder="Hours" required>
            <input name="rate[]" type="number" step="0.01" class="w-full md:w-28 border p-2 rounded" placeholder="Rate/hr" required>
            <button type="button" onclick="removeItem(this)" class="text-red-500 text-sm">Remove</button>
        `;
        container.appendChild(div);
    }

    function removeItem(btn) {
        btn.parentElement.remove();
    }

    document.querySelectorAll('.import-task').forEach(button => {
        button.addEventListener('click', async () => {
            const taskId = button.dataset.id;
            const desc = button.dataset.desc;
            const hours = button.dataset.hours;

            const container = document.getElementById('items');
            const div = document.createElement('div');
            div.className = "flex flex-col md:flex-row md:items-center md:space-x-4 gap-2 mt-4";
            div.innerHTML = `
                <input name="task[]" value="${desc}" class="flex-1 border p-2 rounded" required>
                <input name="hours[]" value="${hours}" class="w-full md:w-24 border p-2 rounded" required>
                <input name="rate[]" type="number" step="0.01" class="w-full md:w-28 border p-2 rounded" placeholder="Rate/hr" required>
                <button type="button" onclick="removeItem(this)" class="text-red-500 text-sm">Remove</button>
            `;
            container.appendChild(div);

            await fetch(`mark_task_assigned.php?id=${taskId}`, { method: 'POST' });

            button.disabled = true;
            button.textContent = "✓ Imported";
            button.classList.remove("bg-blue-600");
            button.classList.add("bg-green-500");
        });
    });
</script>
</body>
</html>