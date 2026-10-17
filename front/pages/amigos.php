<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

header("Location: chat.php");
exit();
?>
