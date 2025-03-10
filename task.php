<?php
include "config.php";
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Tambah Tugas
if (isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $due_date = $_POST['due_date'];
    $schedule_id = $_POST['schedule_id'];

    $sql = "INSERT INTO tasks (title, due_date, schedule_id, user_id) 
            VALUES ('$title', '$due_date', '$schedule_id', '$user_id')";
    $conn->query($sql);
    header("Location: task.php");
    exit();
}

// Ambil Data Tugas Belum Selesai
$stmt = $conn->prepare("SELECT tasks.*, schedule.course_name FROM tasks 
                        LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                        WHERE tasks.status = 'Belum' AND tasks.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_tasks = $stmt->get_result();

// Ambil Data Tugas Selesai
$stmt = $conn->prepare("SELECT tasks.*, schedule.course_name FROM tasks 
                        LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                        WHERE tasks.status = 'Selesai' AND tasks.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$done_tasks = $stmt->get_result();

// Ambil Semua Jadwal (opsional, bisa tetap global)
$schedules = $conn->query("SELECT * FROM schedule");
?>

<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <title>Todo Tugas Kuliah</title>
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
        //         // Periksa preferensi mode saat halaman dimuat
        //         document.addEventListener("DOMContentLoaded", () => {
        //     if (localStorage.getItem("theme") === "dark") {
        //         document.documentElement.classList.add("dark");
        //     } else {
        //         document.documentElement.classList.remove("dark");
        //     }
        // });

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
<body class="p-6 bg-gray-100 dark:bg-gray-900 dark:text-white transition-colors duration-300">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-center">📌 Daftar Tugas Kuliah</h1>
        <button onclick="toggleDarkMode()" class="text-2xl focus:outline-none transition">
            <span id="darkModeIcon">🌙</span>
        </button>
    </div>

    <a href="index.php" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md mb-6 inline-block">⬅ Kembali</a>
    <!-- Form Tambah Tugas -->
    <form method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md flex flex-col gap-4">
        <input type="text" name="title" placeholder="Judul Tugas" required 
               class="w-full bg-gray-200 dark:bg-gray-700 p-5 rounded-md text-xl">
        <input type="date" name="due_date" required 
               class="w-full bg-gray-200 dark:bg-gray-700 p-5 rounded-md text-xl">
        <select name="schedule_id" required 
                class="w-full bg-gray-200 dark:bg-gray-700 p-5 rounded-md text-xl">
            <option value="">Pilih Mata Kuliah</option>
            <?php while ($row = $schedules->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['course_name'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="add_task" class="w-full bg-blue-500 text-white p-5 rounded-md text-xl">➕ Tambah</button>
    </form>

    <!-- Daftar Tugas -->
    <h2 class="text-2xl font-bold mt-6 mb-4">📌 Tugas Belum Selesai</h2>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
        <?php while ($row = $pending_tasks->fetch_assoc()): ?>
        <div class="flex flex-col sm:flex-row sm:justify-between gap-2 border-b pb-4 mb-4">
            <div>
                <h3 class="font-semibold text-xl"><?= $row['title'] ?></h3>
                <p class="text-gray-500 dark:text-gray-400 text-lg"><?= $row['course_name'] ?> | ⏳ <?= $row['due_date'] ?></p>
            </div>
            <a href="update_task.php?id=<?= $row['id'] ?>" class="bg-green-500 text-white p-4 text-xl rounded-md text-center sm:w-40">✔ Selesaikan</a>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Tugas Selesai -->
    <h2 class="text-2xl font-bold mt-6 mb-4">✅ Tugas Selesai</h2>
    <div class="space-y-3">
        <?php while ($row = $done_tasks->fetch_assoc()): ?>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-md shadow-md flex flex-col sm:flex-row sm:justify-between">
            <div>
                <h3 class="font-semibold text-xl"><?= $row['title'] ?></h3>
                <p class="text-gray-600 dark:text-gray-400 text-lg"><?= $row['course_name'] ?> | ✅ <?= $row['due_date'] ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
