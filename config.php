<?php
$host = "sql313.infinityfree.com";
$user = "if0_38356456"; // Sesuaikan dengan username MySQL Anda
$pass = "vbA4YaFoPZ"; // Jika ada password MySQL, masukkan di sini
$dbname = "if0_38356456_todo_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
