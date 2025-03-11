<?php
session_start();
include "config.php"; // Pastikan sudah ada koneksi database


// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Jika user_id = 2, maka grid 3 kolom, selain itu grid 2 kolom
$gridClass = ($user_id == 1) ? "lg:grid-cols-3" : "lg:grid-cols-2";

// Array nama hari
$hariArray = [
    1 => "Senin",
    2 => "Selasa",
    3 => "Rabu",
    4 => "Kamis",
    5 => "Jumat",
    6 => "Sabtu",
    7 => "Minggu"
];

// Ambil hari sekarang dalam format angka (1 = Senin, ..., 7 = Minggu)
$hariSekarang = date('N');

// Tentukan hari esok
$hariEsok = ($hariSekarang == 7) ? 1 : $hariSekarang + 1;
$namaHariEsok = $hariArray[$hariEsok];

$query = "SELECT tasks.*, schedule.course_name 
          FROM tasks 
          LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
          WHERE tasks.user_id = ? AND tasks.status != 'Selesai' 
          ORDER BY tasks.due_date ASC 
          LIMIT 2";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$taskresult = $stmt->get_result();


// Query untuk mengambil jadwal besok berdasarkan user_id
$stmt = $conn->prepare("SELECT * FROM schedule WHERE day = ? AND user_id = ?");
$stmt->bind_param("si", $namaHariEsok, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Harian</title>
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
    <style>
        /* Efek kaca transparan */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-200 transition-all duration-300">
    
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 w-full bg-white dark:bg-gray-800 bg-opacity-70 dark:bg-opacity-70 backdrop-blur-lg shadow-md py-3 px-6 flex justify-between items-center z-50">
        <h1 class="text-xl font-bold">Dashboard Harian, <a href="profile.php"><?= $_SESSION['username'] ?></a></h1>
        <div>
        <button onclick="toggleDarkMode()" class="text-2xl focus:outline-none transition mr-2">
            <span id="darkModeIcon">🌙</span>
        </button>
        <a href="logout.php" class="bg-red-500 text-white p-2 rounded-md mb-4 inline-block">Logout</a>
        </div>

    </nav>

    <div class="mt-16 flex flex-col mx-8 lg:mx-auto md:flex-row items-start justify-center gap-2">
    <!-- Jadwal Besok -->
    <div class="w-full md:w-5/12 max-w-md">
        <h2 class="text-2xl font-bold pt-14 pb-4 flex items-center gap-2 justify-center text-center">
            📅 Jadwal Besok (<?= $namaHariEsok ?>)
        </h2>

        <?php if ($result->num_rows > 0): ?>
            <ul class="space-y-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="flex items-center gap-6 bg-gray-100 dark:bg-gray-800 shadow p-4 rounded-lg hover:scale-105 transform transition-all duration-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="text-3xl md:flex hidden">📖</span> <!-- Ikon Buku -->
                        <div>
                            <p class="text-lg font-semibold"><?= htmlspecialchars($row['course_name']) ?></p>
                            <div class="text-sm text-gray-600 dark:text-gray-300 flex gap-2">
                                🕒 <span class="bg-yellow-300 dark:bg-yellow-600 text-black dark:text-white px-2 py-1 rounded-md">
                                    <?= date('H:i', strtotime($row['start_time'])) ?> - <?= date('H:i', strtotime($row['end_time'])) ?>
                                </span>
                                📍 <span class="bg-green-300 dark:bg-green-600 text-black dark:text-white px-2 py-1 rounded-md">
                                    <?= htmlspecialchars($row['room']) ?>
                                </span>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 dark:text-gray-400 text-center">❌ Tidak ada jadwal kuliah besok.</p>
        <?php endif; ?>
    </div>

    <!-- Tugas Terdekat -->
    <div class="w-full md:w-5/12 max-w-md">
        <h2 class="text-2xl font-bold pt-4 md:pt-14 pb-4 flex items-center gap-2 justify-center text-center">
            📌 Tugas Terdekat
        </h2>

        <?php if ($taskresult->num_rows > 0): ?>
            <ul class="space-y-6">
                <?php while ($row = $taskresult->fetch_assoc()): ?>
                    <li class="flex items-center gap-6 bg-gray-100 dark:bg-gray-800 shadow p-4 rounded-lg hover:scale-105 transform transition-all duration-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="text-3xl hidden md:flex">📖</span> <!-- Ikon Buku -->
                        <div>
                            <p class="text-lg font-semibold"><?= htmlspecialchars($row['title']) ?></p>
                            <div class="text-sm text-gray-600 dark:text-gray-300 flex gap-2">
                                🏫 <span class="bg-blue-300 dark:bg-blue-600 text-black dark:text-white px-2 py-1 rounded-md">
                                    <?= htmlspecialchars($row['course_name']) ?>
                                </span>
                                ⏳ <span class="bg-red-300 dark:bg-red-600 text-black dark:text-white px-2 py-1 rounded-md">
                                    <?= date('d M Y', strtotime($row['due_date'])) ?>
                                </span>
                                ✅ <span class="bg-green-300 dark:bg-green-600 text-black dark:text-white px-2 py-1 rounded-md">
                                    <?= ($row['status'] == 'done') ? 'Selesai' : 'Belum Selesai' ?>
                                </span>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 dark:text-gray-400 text-center">❌ Tidak ada tugas terdekat.</p>
        <?php endif; ?>
    </div>
</div>




    <!-- Konten -->
    <div class="flex flex-col items-center justify-center p-10">
        <!-- Grid Menu -->
        <div class="grid gap-6 sm:grid-cols-2 <?php echo $gridClass; ?> w-full max-w-4xl">
        <a href="task.php" class="glassmorphism p-6 text-gray-800 dark:text-gray-200 shadow-lg hover:scale-105 transform transition-all">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📚</span>
                <h2 class="text-xl font-bold">Tugas Kuliahan</h2>
            </div>
            <p class="text-sm mt-2">Kelola dan lihat tugas kuliahmu dengan mudah.</p>
            <button class="mt-4 px-4 py-2 bg-blue-500 dark:bg-blue-700 text-white rounded-lg shadow-md hover:bg-blue-600 dark:hover:bg-blue-800 transition">
                Lihat Tugas
            </button>
        </a>
        <a href="schedule.php" class="glassmorphism p-6 text-gray-800 dark:text-gray-200 shadow-lg hover:scale-105 transform transition-all">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📅</span>
                <h2 class="text-xl font-bold">Jadwal Kuliah</h2>
            </div>
            <p class="text-sm mt-2">Lihat jadwal kuliahmu yang sudah terorganisir.</p>
            <button class="mt-4 px-4 py-2 bg-green-500 dark:bg-green-700 text-white rounded-lg shadow-md hover:bg-green-600 dark:hover:bg-green-800 transition">
                Lihat Jadwal
            </button>
        </a>
        
        <!-- Hanya tampil jika user_id = 2 -->
        <?php if ($user_id == 1): ?>
            <a href="project.html" class="glassmorphism p-6 text-gray-800 dark:text-gray-200 shadow-lg hover:scale-105 transform transition-all">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🛠️</span>
                    <h2 class="text-xl font-bold">Proyek & Eksperimen</h2>
                </div>
                <p class="text-sm mt-2">Eksplorasi proyek-proyek dan eksperimen menarik.</p>
                <button class="mt-4 px-4 py-2 bg-purple-500 dark:bg-purple-700 text-white rounded-lg shadow-md hover:bg-purple-600 dark:hover:bg-purple-800 transition">
                    Lihat Proyek
                </button>
            </a>
        <?php endif; ?>
    </div>
    </div>

</body>
</html>
