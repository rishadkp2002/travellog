<?php
session_start();
if (!isset($_SESSION['hod'])) {
    header('Location: hod_login.php');
    exit;
}

$error = '';
$success = '';
$csvFile = __DIR__ . '/hod.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = trim($_POST['current_password'] ?? '');
    $new = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($new !== $confirm) {
        $error = "New passwords do not match!";
    } else {
        // Read all HOD users
        $hods = [];
        if (file_exists($csvFile)) {
            $file = fopen($csvFile, 'r');
            while (($row = fgetcsv($file)) !== false) {
                $hods[] = $row;
            }
            fclose($file);
        }

        // Update password if current is correct
        foreach ($hods as &$row) {
            if ($row[0] === $_SESSION['hod']) {
                if (!password_verify($current, $row[1])) {
                    $error = "Current password is incorrect!";
                } else {
                    $row[1] = password_hash($new, PASSWORD_DEFAULT);
                    // Save back to CSV
                    $file = fopen($csvFile, 'w');
                    foreach ($hods as $r) {
                        fputcsv($file, $r);
                    }
                    fclose($file);
                    $success = "Password changed successfully!";
                }
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Change HOD Password</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
<h2>Change Password</h2>

<?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="POST">
    <input type="password" name="current_password" placeholder="Current Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
    <button type="submit">Change Password</button>
</form>

<p style="margin-top:10px;"><a href="hod_dashboard.php">Back to Dashboard</a></p>
</div>
</body>
</html>
