<?php
session_start();
require '../config.php'; // Sesuaikan koneksi database

$user_id = $_SESSION['user_id']; // Ambil ID user yang login

// Ambil data pengguna
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika pengguna ingin menghapus foto profil
if (isset($_POST['delete_profile_picture'])) {
    if (!empty($user['profile_picture'])) {
        $file_path = "uploads/" . $user['profile_picture'];
        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file dari server
        }

        // Reset foto profil di database
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $_SESSION['success_message'] = "Profil berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus profil!";
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
        header("Location: profile.php");
        exit;
    }

    // Validasi format file
    $allowed_extensions = ["jpg", "jpeg", "png"];
    $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        $_SESSION['error_message'] = "Format file tidak valid! Hanya menerima JPG, JPEG, dan PNG.";
        header("Location: profile.php");
        exit;
    }

    // Buat nama file unik
    $new_filename = "profile_" . $user_id . "." . $file_ext;
    $upload_path = "uploads/" . $new_filename;

    // Hapus foto lama jika ada
    if (!empty($user['profile_picture'])) {
        $old_file_path = "uploads/" . $user['profile_picture'];
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }
    }

    // Pindahkan file yang diunggah
    if (move_uploaded_file($file["tmp_name"], $upload_path)) {
        // Update database dengan nama file baru
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        if ($stmt->execute([$new_filename, $user_id])) {
            $_SESSION['success_message'] = "Profil berhasil diunggah.";
        } else {
            $_SESSION['error_message'] = "Gagal mengunggah profil!";
        }
    } else {
        $_SESSION['error_message'] = "Gagal mengunggah file!";
    }

    header("Location: profile.php");
    exit;
}

// Jika akses tidak valid
$_SESSION['error_message'] = "Akses tidak valid!";
header("Location: profile.php");
exit;
?>
