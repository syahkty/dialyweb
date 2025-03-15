<?php
require '../config.php';
session_start();

if (isset($_GET['code'])) {
    try {
        // Ambil token dengan kode dari Google
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Periksa jika terjadi error saat mengambil token
        if (isset($token['error'])) {
            throw new Exception("Gagal mendapatkan akses token: " . $token['error']);
        }

        $client->setAccessToken($token);

        // Ambil informasi pengguna dari Google
        $oauth = new Google\Service\Oauth2($client);
        $userInfo = $oauth->userinfo->get();

        // Pastikan email tersedia
        if (!isset($userInfo->email) || empty($userInfo->email)) {
            throw new Exception("Gagal mendapatkan email dari akun Google.");
        }

        // Ambil koneksi database dari config.php
        global $pdo;

        // Periksa apakah email Google sudah ada di database
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$userInfo->email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Jika email sudah terdaftar, login langsung
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        } else {
            // Jika email belum ada, buat akun baru
            $username = explode('@', $userInfo->email)[0]; // Gunakan bagian awal email sebagai username default
            $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // Buat password acak

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $userInfo->email, $password]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
        }

        // Redirect ke halaman utama
        header("Location: ../index.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Autentikasi gagal: " . $e->getMessage();
        header("Location: ../login.php");
        exit();
    }
}
?>
