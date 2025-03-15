<?php
session_start();
require 'config.php'; // Koneksi ke database

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Ambil daftar teman
$query = "SELECT users.id, users.username FROM friends 
          JOIN users ON friends.friend_id = users.id 
          WHERE friends.user_id = ? AND friends.status = 'accepted'
          UNION
          SELECT users.id, users.username FROM friends 
          JOIN users ON friends.user_id = users.id 
          WHERE friends.friend_id = ? AND friends.status = 'accepted'";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $user_id]);
$friends = $stmt->fetchAll();

// Ambil permintaan pertemanan yang masuk
$query = "SELECT users.id, users.username FROM friends 
          JOIN users ON friends.user_id = users.id 
          WHERE friends.friend_id = ? AND friends.status = 'pending'";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$friend_requests = $stmt->fetchAll();

// Tambah teman via AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_friend'])) {
    $friend_username = $_POST['friend_username'];

    $query = "SELECT id FROM users WHERE username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$friend_username]);
    $friend = $stmt->fetch();

    if ($friend) {
        $friend_id = $friend['id'];

        // Cek apakah sudah ada hubungan atau permintaan pending
        $checkQuery = "SELECT * FROM friends WHERE 
                      (user_id = ? AND friend_id = ?) 
                      OR (user_id = ? AND friend_id = ?)";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);

        if ($stmt->fetch()) {
            exit("exists"); // Sudah ada hubungan
        }

        // Tambahkan permintaan pertemanan
        $insertQuery = "INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([$user_id, $friend_id]);

        exit("success");
    } else {
        exit("error"); // Username tidak ditemukan
    }
}


// Aksi AJAX untuk menerima atau menghapus teman
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $friend_id = $_POST['friend_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        $updateQuery = "UPDATE friends SET status = 'accepted' 
                        WHERE (user_id = ? AND friend_id = ?) 
                        OR (user_id = ? AND friend_id = ?)";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([$friend_id, $user_id, $user_id, $friend_id]);

        exit("accepted");
    } elseif ($action === 'remove') {
        $deleteQuery = "DELETE FROM friends 
                        WHERE (user_id = ? AND friend_id = ?) 
                        OR (user_id = ? AND friend_id = ?)";
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);

        exit("removed");
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Teman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Konfigurasi Tailwind agar dark mode pakai class
        tailwind.config = {
            darkMode: 'class'
        };

        document.addEventListener("DOMContentLoaded", () => {
            console.log("Halaman dimuat.");
            
            // Cek preferensi mode gelap dari localStorage
            const theme = localStorage.getItem("theme");
            console.log("Tema dari localStorage:", theme);
            
            if (theme === "dark") {
                document.documentElement.classList.add("dark");
                document.getElementById("darkModeIcon").innerHTML = "☀️";
                console.log("Dark mode diaktifkan.");
            } else {
                document.documentElement.classList.remove("dark");
                document.getElementById("darkModeIcon").innerHTML = "🌙";
                console.log("Light mode diaktifkan.");
            }
        });

        function toggleDarkMode() {
            console.log("Tombol diklik.");
            
            if (document.documentElement.classList.contains("dark")) {
                document.documentElement.classList.remove("dark");
                localStorage.setItem("theme", "light");
                document.getElementById("darkModeIcon").innerHTML = "🌙";
                console.log("Dark mode dimatikan, tema disimpan: light.");
            } else {
                document.documentElement.classList.add("dark");
                localStorage.setItem("theme", "dark");
                document.getElementById("darkModeIcon").innerHTML = "☀️";
                console.log("Dark mode diaktifkan, tema disimpan: dark.");
            }
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-200">
    <div class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-center">📌 Daftar Teman</h1>
        <button onclick="toggleDarkMode()" class="text-2xl focus:outline-none transition">
            <span id="darkModeIcon">🌙</span>
        </button>
    </div>

    <a href="index.php" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md mb-6 inline-block">⬅ Kembali</a>

        <!-- Form Tambah Teman -->
        <form id="addFriendForm" class="mb-6">
    <input type="text" id="friend_username" required 
           class="border p-2 w-full rounded-lg mb-2 dark:bg-gray-800 dark:border-gray-600" 
           placeholder="Masukkan username teman...">
    <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md">
        Kirim Permintaan
    </button>
</form>


        <!-- Permintaan Pertemanan -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-3">Permintaan Pertemanan</h2>
            <ul>
                <?php foreach ($friend_requests as $request): ?>
                    <li class="flex justify-between items-center p-4 mb-2 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
                        <span class="font-semibold"><?= htmlspecialchars($request['username']); ?></span>
                        <div>
                            <button onclick="confirmAction('accept', <?= $request['id']; ?>)"
                                    class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg mr-2">
                               ✔ Terima
                            </button>
                            <button onclick="confirmAction('remove', <?= $request['id']; ?>)"
                                    class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                               ✖ Tolak
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Daftar Teman -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-3">Temanmu</h2>
            <ul>
                <?php foreach ($friends as $friend): ?>
                    <li class="flex justify-between items-center p-4 mb-2 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
                        <span class="font-semibold"><?= htmlspecialchars($friend['username']); ?></span>
                        <button onclick="confirmAction('remove', <?= $friend['id']; ?>)" 
                                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                           🚫 Hapus
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
    document.getElementById("addFriendForm").addEventListener("submit", function(event) {
        event.preventDefault(); // Mencegah reload halaman

        let username = document.getElementById("friend_username").value;

        fetch("friends.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `add_friend=1&friend_username=${encodeURIComponent(username)}`
        })
        .then(response => response.text())
        .then(result => {
            console.log("Server Response:", result); // Debugging di Console
            let isDarkMode = document.documentElement.classList.contains('dark'); // Cek mode gelap
            Swal.fire({
                title: result === "success" ? "Sukses!" : result === "exists" ? "Info" : "Gagal!",
                text: result === "success" ? "Permintaan pertemanan terkirim!" 
                     : result === "exists" ? "Kamu sudah berteman atau ada permintaan yang tertunda." 
                     : "Username tidak ditemukan!",
                icon: result === "success" ? "success" : result === "exists" ? "info" : "error",
                background: isDarkMode ? '#1E293B' : '#ffffff', // Warna dark/light mode
                color: isDarkMode ? '#ffffff' : '#000000' // Warna teks dark/light mode
            }).then(() => {
                if (result === "success") location.reload(); // Reload jika sukses
            });
        });
    });

    function confirmAction(action, friend_id) {
        let message = action === 'accept' ? "Terima permintaan pertemanan?" : "Hapus teman?";
        let successMessage = action === 'accept' ? "Permintaan diterima!" : "Teman dihapus!";

        let isDarkMode = document.documentElement.classList.contains('dark'); // Cek mode gelap

        Swal.fire({
            title: "Konfirmasi",
            text: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Batal",
            background: isDarkMode ? '#1E293B' : '#ffffff', // Warna dark/light mode
            color: isDarkMode ? '#ffffff' : '#000000' // Warna teks dark/light mode
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("friends.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=${action}&friend_id=${friend_id}`
                }).then(() => {
                    Swal.fire({
                        title: "Berhasil!",
                        text: successMessage,
                        icon: "success",
                        background: isDarkMode ? '#1E293B' : '#ffffff', // Warna dark/light mode
                        color: isDarkMode ? '#ffffff' : '#000000' // Warna teks dark/light mode
                    }).then(() => location.reload());
                });
            }
        });
    }
</script>
</body>
</html>
