<?php
session_start();
session_destroy();
header('Location: hod_login.php');
exit;
