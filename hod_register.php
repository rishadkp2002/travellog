<?php
session_start();
$error = '';
$success = '';
$csvFile = __DIR__ . '/hod.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hodId = trim($_POST['hod_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');

    if (empty($hodId) || empty($password)) {
        $error = "HOD ID and password are required.";
    } elseif ($password !== $passwordConfirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if hodId already exists
        $exists = false;
        if (file_exists($csvFile)) {
            $file = fopen($csvFile, 'r');
            while (($row = fgetcsv($file)) !== false) {
                if (trim($row[0]) === $hodId) {
                    $exists = true;
                    break;
                }
            }
            fclose($file);
        }

        if ($exists) {
            $error = "HOD ID already registered.";
        } else {
            // Save new HOD ID and hashed password
            $file = fopen($csvFile, 'a');
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            fputcsv($file, [$hodId, $hashedPassword]);
            fclose($file);
            $success = "Registration successful! You can now <a href='hod_login.php'>login</a>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HOD Registration</title>
<link rel="stylesheet" href="hod_register.css">
</head>
<body>
<div class="container">
<h2>HOD Registration</h2>

<?php if($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php elseif($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <input type="text" name="hod_id" placeholder="HOD ID" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="password_confirm" placeholder="Confirm Password" required>
    <button type="submit">Register</button>
</form>

<p><a href="hod_login.php">Back to Login</a></p>
</div>
</body>
</html>
