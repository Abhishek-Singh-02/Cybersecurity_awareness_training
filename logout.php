<?php
// Title: User Logout Script
session_start();
session_destroy();
header('Location: login.php');
exit();
?>