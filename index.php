<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['sap_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$csvFile = __DIR__ . '/logs.csv';

// Create CSV file with header if it doesn't exist
if (!file_exists($csvFile)) {
    $file = fopen($csvFile, 'w');
    fputcsv($file, ['LogID','Date','SAP ID','Name','Break OUT','Break IN','Purpose','Status']);
    fclose($file);
}

$today = date('Y-m-d');
$sap_id = $_SESSION['sap_id'];
$name = $_SESSION['name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $break_out = $_POST['break_out'] ?? '';
    $break_in = $_POST['break_in'] ?? '';
    $purpose = trim($_POST['purpose'] ?? '');

    // Clean and limit purpose
    $purpose = str_replace(["\r", "\n"], ' ', $purpose);
    $purpose = preg_replace('/\s+/', ' ', $purpose);
    $purpose = substr($purpose, 0, 250); // limit to 250 chars

    if ($break_out && $break_in && $purpose !== '') {
        $file = fopen($csvFile, 'a');
        fputcsv($file, [
            uniqid('LOG'),
            $today,
            $sap_id,
            $name,
            $break_out,
            $break_in,
            $purpose,
            'Pending'
        ]);
        fclose($file);
        $success = "Travel log submitted successfully!";
        header("Location: index.php");
        exit;
    } else {
        $error = "Please fill Break OUT, Break IN, and Purpose.";
    }
}

// Read all previous logs safely
$logs = [];
if (($file = fopen($csvFile, 'r')) !== false) {
    fgetcsv($file); // skip header
    while (($row = fgetcsv($file)) !== false) {
        if (!$row || count(array_filter($row)) === 0) continue;
        $row = array_pad($row, 8, '');
        if ($row[2] === $sap_id) {
            $logs[] = $row;
        }
    }
    fclose($file);
}

$break_out_value = '';
$break_in_value = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Travel Log</title>
<link rel="stylesheet" href="index.css">
</head>
<body>

<div class="container">

    <div class="user-header">
        <div>
            <p>NAME: <?= htmlspecialchars($name) ?></p>
            <p>SAP ID: <?= htmlspecialchars($sap_id) ?></p>
        </div>
        <div><a href="logout.php">LOG OUT</a></div>
    </div>

    <h2>Employee Travel Log</h2>

    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST">
        <label>Break OUT:</label>
        <input type="time" name="break_out" id="breakOut" value="<?= htmlspecialchars($break_out_value) ?>" readonly>

        <label>Break IN:</label>
        <input type="time" name="break_in" id="breakIn" value="<?= htmlspecialchars($break_in_value) ?>" required>

        <label>Purpose:</label>
        <textarea name="purpose" rows="3" placeholder="Reason for leaving..." required></textarea>

        <button type="submit">Submit Log</button>
    </form>

    <!-- Toggle Previous Logs -->
    <button type="button" id="toggleLogs">View Your Previous Logs</button>

    <div id="previousLogs" style="display:none; margin-top:20px;">
        <?php if ($logs): ?>
        <table>
            <tr>
                <th>Log ID</th>
                <th>Date</th>
                <th>Break OUT</th>
                <th>Break IN</th>
                <th>Purpose</th>
                <th>Status</th>
            </tr>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log[0]) ?></td>
                <td><?= htmlspecialchars($log[1]) ?></td>
                <td><?= htmlspecialchars($log[4]) ?></td>
                <td><?= htmlspecialchars($log[5]) ?></td>
                <td><?= htmlspecialchars($log[6]) ?></td>
                <td><span class="status <?= $log[7] ?>"><?= $log[7] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p>No previous logs found.</p>
        <?php endif; ?>
    </div>

</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const breakOutInput = document.getElementById('breakOut');
    if (breakOutInput && breakOutInput.value === '') {
        const now = new Date();
        breakOutInput.value = now.toTimeString().slice(0,5);
    const toggleBtn = document.getElementById('toggleLogs');
    const logsDiv = document.getElementById('previousLogs');
    toggleBtn.addEventListener('click', function() {
        if (logsDiv.style.display === 'none') {
            logsDiv.style.display = 'block';
            toggleBtn.textContent = "Hide Your Previous Logs";
        } else {
            logsDiv.style.display = 'none';
            toggleBtn.textContent = "View Your Previous Logs";
        }
    });
});
</script>

</body>
</html>
