<?php
include "config.php";
session_start();

// Tambah Tugas
if (isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $due_date = $_POST['due_date'];
    $schedule_id = $_POST['schedule_id'];

    $sql = "INSERT INTO tasks (title, due_date, schedule_id) VALUES ('$title', '$due_date', '$schedule_id')";
    $conn->query($sql);
    header("Location: index.php");
}

// Ambil Data Tugas
$pending_tasks = $conn->query("SELECT tasks.*, schedule.course_name FROM tasks 
                               LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                               WHERE tasks.status = 'Belum'");

$done_tasks = $conn->query("SELECT tasks.*, schedule.course_name FROM tasks 
                            LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                            WHERE tasks.status = 'Selesai'");

$schedules = $conn->query("SELECT * FROM schedule");
?>

<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <title>Todo Tugas Kuliah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Simpan preferensi mode di localStorage
        function toggleTheme() {
            const htmlElement = document.documentElement;
            htmlElement.classList.toggle("dark");

            if (htmlElement.classList.contains("dark")) {
                localStorage.setItem("theme", "dark");
            } else {
                localStorage.setItem("theme", "light");
            }
        }

        // Periksa preferensi mode saat halaman dimuat
        document.addEventListener("DOMContentLoaded", () => {
            if (localStorage.getItem("theme") === "dark") {
                document.documentElement.classList.add("dark");
            } else {
                document.documentElement.classList.remove("dark");
            }
        });
    </script>
</head>
<body class="p-6 bg-gray-100 dark:bg-gray-900 dark:text-white transition-colors duration-300">

    <div class="max-w-md sm:max-w-xl md:max-w-5xl mx-auto p-4">
    <h1 class="text-3xl font-bold text-center mb-4">📌 Daftar Tugas Kuliah</h1>
    
    <!-- Tombol Jadwal dan Hutang -->
    <div class="flex flex-col sm:flex-row sm:justify-center gap-3 mb-4">
        <a href="schedule.php" class="bg-blue-500 text-white py-4 px-6 rounded-md text-center text-xl w-full sm:w-1/2">📅 Jadwal</a>
        <a href="debts.php" class="bg-red-500 text-white py-4 px-6 rounded-md text-center text-xl w-full sm:w-1/2">💰 Hutang</a>
        <a href="/Tugas%202/latihan5.html" class="bg-green-500 text-white py-4 px-6 rounded-md text-center text-xl w-full sm:w-1/2">Progweb</a>
    </div>

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
