<?php
include "config.php"; // Pastikan sudah ada koneksi database

// Array untuk mencocokkan angka dengan nama hari
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

// Nama hari besok
$namaHariEsok = $hariArray[$hariEsok];

// Ambil jadwal untuk hari esok dari database
$query = "SELECT * FROM schedule WHERE day = '$namaHariEsok'";
$result = $conn->query($query);
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
        <h1 class="text-xl font-bold">Dashboard Harian</h1>
        <button onclick="toggleDarkMode()" class="text-2xl focus:outline-none transition">
            <span id="darkModeIcon">🌙</span>
        </button>
    </nav>

    <div class="mt-10 flex flex-col items-center justify-center mx-10">
    <h2 class="text-2xl font-bold pt-14 pb-4 flex items-center gap-2">
        📅 Jadwal Besok (<?= $namaHariEsok ?>)
    </h2>
    
        <?php if ($result->num_rows > 0): ?>
            <ul class="space-y-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="flex items-center gap-6 bg-gray-100 dark:bg-gray-800 shadow p-4 rounded-lg hover:scale-105 transform transition-all duration-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="text-3xl">📖</span> <!-- Ikon Buku -->
                        <div>
                            <p class="text-lg font-semibold"><?= $row['course_name'] ?></p>
                            <div class="text-sm text-gray-600 dark:text-gray-300 flex gap-2">
                                🕒 <span class="bg-yellow-300 dark:bg-yellow-600 text-black dark:text-white px-2 py-1 rounded-md">
                                <?= date('H:i', strtotime($row['start_time'])) ?> - <?= date('H:i', strtotime($row['end_time'])) ?>
                                </span>
                                📍 <span class="bg-green-300 dark:bg-green-600 text-black dark:text-white px-2 py-1 rounded-md">
                                    <?= $row['room'] ?>
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

    <!-- Konten -->
    <div class="flex flex-col items-center justify-center p-10">
        <!-- Grid Menu -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 w-full max-w-4xl">
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
        </div>
    </div>

</body>
</html>
