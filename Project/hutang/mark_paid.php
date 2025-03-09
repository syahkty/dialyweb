<?php
include "config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "UPDATE debts SET status = 'Lunas' WHERE id = $id";
    $conn->query($sql);
}

header("Location: debts.php");
exit();
?>
