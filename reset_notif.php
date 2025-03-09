<?php
session_start();
unset($_SESSION['notified']);
header("Location: index.php");
exit();
?>
