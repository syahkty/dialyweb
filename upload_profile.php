<?php
session_start();
require 'config.php'; // Sesuaikan koneksi database

$user_id = $_SESSION['user_id']; // Ambil ID user yang login

// Ambil data pengguna
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Jika pengguna ingin menghapus foto profil
if (isset($_POST['delete_profile_picture'])) {
    if ($user['profile_picture']) {
        $file_path = "uploads/" . $user['profile_picture'];
        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file dari server
        }

        // Reset foto profil di database
        $stmt = $conn->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profil berhasil di Hapus";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus Profil!";
        } 
    }

    header("Location: profile.php");
    exit;
}

// Jika pengguna mengunggah foto profil baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $file = $_FILES["profile_picture"];

    // Validasi ukuran maksimal 2MB
    if ($file["size"] > 2 * 1024 * 1024) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar! Maksimal 2MB.";
        die;
    }

    // Validasi format file
    $allowed_extensions = ["jpg", "jpeg", "png"];
    $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        $_SESSION['error_message'] = "Format file tidak valid! Hanya menerima JPG, JPEG, dan PNG.";
    }

    // Buat nama file unik
    $new_filename = "profile_" . $user_id . "." . $file_ext;
    $upload_path = "uploads/" . $new_filename;

    // Hapus foto lama jika ada
    if ($user['profile_picture']) {
        $old_file_path = "uploads/" . $user['profile_picture'];
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }
    }

    // Pindahkan file yang diunggah
    if (move_uploaded_file($file["tmp_name"], $upload_path)) {
        // Update database dengan nama file baru
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $new_filename, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profil berhasil di Unggah";
        } else {
            $_SESSION['error_message'] = "Gagal mengugah Profil!";
        } 

        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Gagal mengunggah file!";
        die;
    }
} else {
    $_SESSION['error_message'] = "Akses tidak valid!";
    die;
}
?>
