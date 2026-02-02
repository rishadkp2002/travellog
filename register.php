<?php
session_start();
$error = '';
$success = '';
$csvFile = __DIR__ . '/employees.csv';

// Create CSV if missing
if (!file_exists($csvFile)) {
    $file = fopen($csvFile, 'w');
    fclose($file);
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sap_id = trim($_POST['sap_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Allow SAP IDs 4 to 9 digits
    if (!preg_match('/^\d{4,9}$/', $sap_id)) {
        $error = "SAP ID must be between 4 and 9 digits!";
    } else {
        $employees = [];
        if (($file = fopen($csvFile, 'r')) !== false) {
            while (($row = fgetcsv($file)) !== false) {
                $employees[$row[0]] = $row;
            }
            fclose($file);
        }

        if (isset($employees[$sap_id])) {
            $error = "SAP ID already registered!";
        } else {
            $file = fopen($csvFile, 'a');
            fputcsv($file, [$sap_id, $name, password_hash($password, PASSWORD_DEFAULT)]);
            fclose($file);
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Registration</title>
<link rel="stylesheet" href="register.css">
</head>
<body>
<div class="container">
    <h2>Employee Registration</h2>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="sap_id" placeholder="SAP ID (4-9 digits)" maxlength="9" required>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <p class="login-link">
    Already registered? <a href="login.php">Login here</a>
</p>

</div>
</body>
</html>
