<?php
session_start();
$error = '';
$success = '';
$csvFile = __DIR__ . '/employees.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sap_id = trim($_POST['sap_id'] ?? '');

    if (!file_exists($csvFile)) {
        $error = "No users found!";
    } else {
        $file = fopen($csvFile, 'r');
        $rows = [];
        $found = false;

        // Read all users into memory
        while (($row = fgetcsv($file)) !== false) {
            if ($row[0] === $sap_id) {
                $found = true;
                $rowToUpdate = $row;
            }
            $rows[] = $row;
        }
        fclose($file);

        if ($found) {
            // Generate a temporary password
            $tempPassword = bin2hex(random_bytes(4)); // 8 characters
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

            // Update CSV with new password
            foreach ($rows as &$row) {
                if ($row[0] === $sap_id) {
                    $row[2] = $hashedPassword;
                    break;
                }
            }

            $file = fopen($csvFile, 'w');
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);

            $success = "Your temporary password is: <strong>$tempPassword</strong><br>Please log in and change it immediately.";
        } else {
            $error = "SAP ID not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<link rel="stylesheet" href="login.css">
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="sap_id" placeholder="Enter your SAP ID" required>
        <button type="submit">Reset Password</button>
    </form>

    <div class="links">
        <p><a href="login.php">Back to Login</a></p>
    </div>
</div>
</body>
</html>
