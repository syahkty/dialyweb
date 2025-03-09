<?php
$host = "localhost";
$user = "root"; // Sesuaikan dengan username MySQL Anda
$pass = ""; // Jika ada password MySQL, masukkan di sini
$dbname = "todo_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
