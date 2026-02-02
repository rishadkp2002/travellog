<?php
session_start();
if (!isset($_SESSION['hod'])) {
    header('Location: hod_login.php');
    exit;
}

$csvFile = __DIR__ . '/logs.csv';
$logs = [];
$message = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    $action = $_POST['action_type'];
    $rowIndex = intval($_POST['row_index'] ?? -1);

    if (($action === 'approve' || $action === 'reject') && $rowIndex >= 0 && file_exists($csvFile)) {
        $allLogs = array_map('str_getcsv', file($csvFile));
        foreach ($allLogs as $i => $row) $allLogs[$i] = array_pad($row, 8, '');
        $csvRowIndex = $rowIndex + 1; // skip header
        if (isset($allLogs[$csvRowIndex])) {
            $allLogs[$csvRowIndex][7] = ($action === 'approve') ? 'Approved' : 'Rejected';
            $file = fopen($csvFile, 'w');
            foreach ($allLogs as $line) fputcsv($file, $line);
            fclose($file);
            $_SESSION['message'] = "Status updated successfully!";
            header("Location: hod_dashboard.php");
            exit;
        }
    }
}

// Display message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Read logs
if (file_exists($csvFile)) {
    $allLogs = array_map('str_getcsv', file($csvFile));
    foreach ($allLogs as $i => $row) $allLogs[$i] = array_pad($row, 8, '');
    $logs = array_slice($allLogs, 1); // skip header
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>HOD Dashboard</title>
<style>
/* Your existing CSS here */
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body {
    background: #f5f6fa;
    color: #333;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    padding: 30px;
}
.container {
    width: 100%;
    max-width: 1300px;
    background: #fff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
.logout {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    gap: 10px;
    font-weight: 600;
}
.logout span { font-size:16px; color:#555; }
.logout a {
    text-decoration: none;
    padding: 8px 16px;
    background: #e74c3c;
    color: #fff;
    border-radius: 6px;
    font-weight: 600;
    transition: 0.3s;
}
.logout a:hover { background: #c0392b; }
h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 28px;
    color: #34495e;
}
.message {
    background: #dff9e3;
    color: #27ae60;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 600;
    border: 1px solid #2ecc71;
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
thead th {
    background: linear-gradient(135deg,#3498db,#2980b9);
    color: #fff;
    padding: 12px;
    font-weight: 600;
    text-align: center;
}
tbody td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
}
tbody tr:hover {
    background: #f1f3f6;
    transition: 0.3s;
}
.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    color: #fff;
    display: inline-block;
}
.status.Pending { background: #f39c12; }
.status.Approved { background: #27ae60; }
.status.Rejected { background: #c0392b; }
button.approve, button.reject {
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    margin: 2px 2px;
    transition: 0.3s;
}
button.approve { background: #27ae60; }
button.approve:hover { background: #1e8449; }
button.reject { background: #e74c3c; }
button.reject:hover { background: #c0392b; }
.download {
    display: inline-block;
    background: #3498db;
    color: #fff;
    padding: 8px 18px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    float: right;
    margin-top: 15px;
    transition: 0.3s;
}
.download:hover { background: #2980b9; }
@media(max-width:900px){
    table, thead, tbody, th, td, tr { display:block; }
    thead tr { display:none; }
    tbody tr { margin-bottom:20px; background:#f9f9f9; padding:15px; border-radius:10px; }
    tbody td { text-align:right; padding:8px 10px; position:relative; }
    tbody td::before { content: attr(data-label); position:absolute; left:10px; width:50%; text-align:left; font-weight:600; color:#555; }
    .logout { flex-direction:column; align-items:flex-start; }
    .download { float:none; display:block; text-align:center; }
}
</style>
</head>
<body>
<div class="container">
    <div class="logout">
        <span>Welcome <?= htmlspecialchars($_SESSION['hod']) ?>!</span>
        <a href="hod_logout.php">Logout</a>
    </div>
      <!-- Excel Download Button -->
     <a href="export_csv.php" class="download">Download Excel</a>



    <h2>Employee Travel Requests</h2>

    <?php if($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Log ID</th>
                <th>Date</th>
                <th>SAP ID</th>
                <th>Name</th>
                <th>Break OUT</th>
                <th>Break IN</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($logs as $index => $log): ?>
            <tr>
                <td data-label="#"><?= $index+1 ?></td>
                <td data-label="Log ID"><?= htmlspecialchars($log[0]) ?></td>
                <td data-label="Date"><?= htmlspecialchars($log[1]) ?></td>
                <td data-label="SAP ID"><?= htmlspecialchars($log[2]) ?></td>
                <td data-label="Name"><?= htmlspecialchars($log[3]) ?></td>
                <td data-label="Break OUT"><?= htmlspecialchars($log[4]) ?></td>
                <td data-label="Break IN"><?= htmlspecialchars($log[5]) ?></td>
                <td data-label="Purpose"><?= htmlspecialchars($log[6]) ?></td>
                <td data-label="Status">
                    <span class="status <?= htmlspecialchars($log[7]) ?>"><?= htmlspecialchars($log[7]) ?></span>
                </td>
                <td data-label="Action">
                    <form method="POST">
                        <input type="hidden" name="row_index" value="<?= $index ?>">
                        <button type="submit" name="action_type" value="approve" class="approve">Approve</button>
                        <button type="submit" name="action_type" value="reject" class="reject">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
