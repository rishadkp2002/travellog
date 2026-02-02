<?php
session_start();
$error = '';
$success = '';
$csvFile = __DIR__ . '/hod.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match!";
    } elseif (strlen($newPassword) < 4) {
        $error = "Password must be at least 4 characters!";
    } else {
        $updated = false;
        if (file_exists($csvFile)) {
            $rows = [];
            $file = fopen($csvFile, 'r');
            while (($row = fgetcsv($file)) !== false) {
                if (trim($row[0]) === $username) {
                    $row[1] = password_hash($newPassword, PASSWORD_DEFAULT); // update password
                    $updated = true;
                }
                $rows[] = $row;
            }
            fclose($file);

            if ($updated) {
                $file = fopen($csvFile, 'w');
                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
                $success = "Password reset successful! <a href='hod_login.php'>Login now</a>.";
            } else {
                $error = "Username not found!";
            }
        } else {
            $error = "No HOD records found!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>HOD Password Reset</title>
<link rel="stylesheet" href="hod_login.css">
</head>
<body>
<div class="container">
<h2>Reset HOD Password</h2>

<?php if($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Reset Password</button>
</form>

<p style="margin-top: 15px;">
    <a href="hod_login.php">Back to Login</a>
</p>
</div>
</body>
</html>
