<?php
session_start();
$error = '';
$csvFile = __DIR__ . '/employees.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sap_id = trim($_POST['sap_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!file_exists($csvFile)) {
        $error = "No registered users found. Please register first.";
    } else {
        $file = fopen($csvFile, 'r');
        $loginSuccess = false;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 3) continue;

            if ($row[0] === $sap_id) {
                if (password_verify($password, $row[2])) {
                    $_SESSION['sap_id'] = $sap_id;
                    $_SESSION['name'] = $row[1];
                    $loginSuccess = true;
                    break;
                } else {
                    $error = "Incorrect password!";
                    break;
                }
            }
        }

        fclose($file);

        if ($loginSuccess) {
            header('Location: index.php');
            exit;
        } elseif (!$error) {
            $error = "SAP ID not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Login</title>
<link rel="stylesheet" href="login.css">
</head>
<body>

<div class="container">
    <h2>Employee Login</h2>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="sap_id" placeholder="SAP ID (4-8 digits)" maxlength="8" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="links">
        <p class="register-link">Not registered? <a href="register.php">Register here</a></p>
        <p class="forgot-link"><a href="forgot_password.php">Forgot password?</a></p>
    </div>
</div>

</body>
</html>

