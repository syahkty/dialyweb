<?php
session_start();
require 'config.php'; // Sesuaikan dengan koneksi databasenya

$user_id = $_SESSION['user_id']; // Ambil user_id dari sesi login

// Ambil data pengguna
$stmt = $conn->prepare("SELECT username, email, bio, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Jika tidak ada foto, gunakan avatar DiceBear berdasarkan username
$avatar = (!empty($user['profile_picture']) && file_exists("uploads/" . $user['profile_picture'])) 
    ? "uploads/" . $user['profile_picture'] 
    : "https://api.dicebear.com/7.x/initials/png?seed=" . urlencode($user['username']);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="max-w-full mx-20 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-center">📅 Profil</h1>
        <button onclick="toggleDarkMode()" class="text-2xl focus:outline-none transition">
            <span id="darkModeIcon">🌙</span>
        </button>
    </div>
    <a href="index.php" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md mb-6 inline-block">⬅ Kembali</a>
        <!-- Foto Profil -->
        <div class="flex flex-col items-center space-y-4">
            <img src="<?= $avatar ?>" alt="Foto Profil" class="w-32 h-32 rounded-full shadow-lg border-4 border-gray-300 dark:border-gray-600">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($user['username']) ?></h2>
            <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
        </div>
        <!-- Form Upload Foto Profil -->
        <form action="upload_profile.php" method="POST" enctype="multipart/form-data" class="mt-2">
            <input type="file" name="profile_picture" accept="image/png, image/jpeg" class="block w-full text-sm text-gray-600 dark:text-gray-300">
            <button type="submit" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">⬆ Upload Foto</button>
        </form>


        <!-- Biografi -->
        <div class="mt-6 p-4 bg-white dark:bg-gray-800 shadow rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Biografi</h3>
            <p id="bio-text" class="text-gray-700 dark:text-gray-300"><?= htmlspecialchars($user['bio'] ?: 'Belum ada biografi.') ?></p>
            <button onclick="toggleEditBio()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">✏ Edit</button>

            <form id="bio-form" action="update_bio.php" method="POST" class="hidden mt-2">
                <textarea name="bio" class="w-full p-2 text-black border rounded"><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <button type="submit" class="mt-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">✔ Simpan</button>
            </form>
        </div>

        <!-- Pengaturan Akun -->
        <div class="mt-6 p-4 bg-white dark:bg-gray-800 shadow rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Pengaturan Akun</h3>
            <button onclick="toggleModal('reset-modal')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded mr-2">🔑 Reset Kata Sandi</button>
            <button onclick="toggleModal('delete-modal')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">❌ Hapus Akun</button>
        </div>
    </div>

    <!-- Modal Reset Kata Sandi -->
    <div id="reset-modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg w-96">
            <h2 class="text-lg font-bold mb-4">Reset Kata Sandi</h2>
            <form action="reset_password.php" method="POST">
                <input type="password" name="new_password" placeholder="Kata Sandi Baru" class="w-full p-2 text-black border rounded mb-2">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">✔ Simpan</button>
            </form>
            <button onclick="toggleModal('reset-modal')" class="mt-2 text-red-500">Tutup</button>
        </div>
    </div>

    <!-- Modal Hapus Akun -->
    <div id="delete-modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg w-96">
            <h2 class="text-lg font-bold mb-4">Konfirmasi Hapus Akun</h2>
            <p>Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak bisa dibatalkan.</p>
            <form action="delete_account.php" method="POST">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">❌ Hapus</button>
            </form>
            <button onclick="toggleModal('delete-modal')" class="mt-2 text-gray-500">Batal</button>
        </div>
    </div>

    <script>
        function toggleEditBio() {
            document.getElementById('bio-text').classList.toggle('hidden');
            document.getElementById('bio-form').classList.toggle('hidden');
        }

        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }
    </script>
</body>
</html>

