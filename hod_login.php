<?php
session_start();
$error = '';
$csvFile = __DIR__ . '/hod.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hodId = trim($_POST['hod_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (file_exists($csvFile)) {
        $file = fopen($csvFile, 'r');
        while (($row = fgetcsv($file)) !== false) {
            if (trim($row[0]) === $hodId && password_verify($password, trim($row[1]))) {
                $_SESSION['hod'] = $hodId;  // store HOD ID in session
                header('Location: hod_dashboard.php');
                exit;
            }
        }
        fclose($file);
    }
    $error = "Invalid HOD ID or password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HOD Login</title>
<link rel="stylesheet" href="hod_login.css">
</head>
<body>
<div class="container">
<h2>HOD Login</h2>

<?php if($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <input type="text" name="hod_id" placeholder="HOD ID" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<p><a href="hod_forgot.php">Forgot Password?</a></p>
<p><a href="hod_register.php">Register as HOD</a></p>
</div>
</body>
</html>
