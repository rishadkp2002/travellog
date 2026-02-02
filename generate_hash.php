<?php
$username = 'hod1';
$password = 'admin123';   // the password you will type at login

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Username: $username<br>";
echo "Password: $password<br>";
echo "Hashed password: $hash";
?>
