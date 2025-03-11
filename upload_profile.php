<?php
session_start();
require 'config.php'; // Sesuaikan koneksi database

$user_id = $_SESSION['user_id']; // Ambil ID user yang login

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $file = $_FILES["profile_picture"];
    
    // Validasi ukuran maksimal 2MB
    if ($file["size"] > 2 * 1024 * 1024) {
        die("Ukuran file terlalu besar! Maksimal 2MB.");
    }

    // Validasi format file
    $allowed_extensions = ["jpg", "jpeg", "png"];
    $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        die("Format file tidak valid! Hanya menerima JPG, JPEG, dan PNG.");
    }

    // Buat nama file unik
    $new_filename = "profile_" . $user_id . "." . $file_ext;
    $upload_path = "uploads/" . $new_filename;

    // Pindahkan file yang diunggah
    if (move_uploaded_file($file["tmp_name"], $upload_path)) {
        // Update database dengan nama file baru
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $new_filename, $user_id);
        $stmt->execute();

        // Redirect ke halaman profil
        header("Location: profile.php");
        exit;
    } else {
        die("Gagal mengunggah file!");
    }
} else {
    die("Akses tidak valid!");
}
?>
