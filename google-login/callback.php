<?php
require '../config.php';
session_start();

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Ambil informasi pengguna dari Google
    $oauth = new Google\Service\Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    // Ambil koneksi database dari config.php
    global $conn;

    // Periksa apakah email Google sudah ada di database
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $userInfo->email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Jika email sudah terdaftar, login langsung
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
    } else {
        // Jika email belum ada, buat akun baru
        $username = explode('@', $userInfo->email)[0]; // Gunakan bagian awal email sebagai username default
        $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // Buat password acak

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $userInfo->email, $password);
        $stmt->execute();

        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
    }

    // Redirect ke halaman utama
    header("Location: ../index.php");
    exit();
}
?>
