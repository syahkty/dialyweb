<?php
include "config.php";

// Tambah Jadwal
if (isset($_POST['add_schedule'])) {
    $day = $_POST['day'];
    $course_name = $_POST['course_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];

    $sql = "INSERT INTO schedule (day, course_name, start_time, end_time, room) 
            VALUES ('$day', '$course_name', '$start_time', '$end_time', '$room')";
    $conn->query($sql);
    header("Location: schedule.php");
}

// Hapus Jadwal
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM schedule WHERE id = $id");
    header("Location: schedule.php");
}

// Ambil Data Jadwal
$schedules = $conn->query("SELECT * FROM schedule ORDER BY FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')");

// Ambil Data untuk Edit
$edit_id = "";
$edit_data = ["day" => "", "course_name" => "", "start_time" => "", "end_time" => "", "room" => ""];
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM schedule WHERE id = $edit_id");
    $edit_data = $result->fetch_assoc();
}

// Simpan Perubahan Edit Jadwal
if (isset($_POST['update_schedule'])) {
    $id = $_POST['id'];
    $day = $_POST['day'];
    $course_name = $_POST['course_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];

    $sql = "UPDATE schedule SET day='$day', course_name='$course_name', start_time='$start_time', end_time='$end_time', room='$room' WHERE id='$id'";
    $conn->query($sql);
    header("Location: schedule.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Jadwal Mata Kuliah</title>
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
<body class="bg-white text-black p-8 dark:bg-gray-900 dark:text-white transition-colors duration-300">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-center">📅 Jadwal Mata Kuliah</h1>
        <button onclick="toggleDarkMode()" class="text-2xl focus:outline-none transition">
            <span id="darkModeIcon">🌙</span>
        </button>
    </div>

    <a href="index.php" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md mb-6 inline-block">⬅ Kembali</a>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-6">
        <form method="POST" class="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-2">
            <input type="hidden" name="id" value="<?= $edit_id ?>">
            <input type="text" name="course_name" placeholder="Mata Kuliah" required class="bg-gray-200 dark:bg-gray-700 border p-3 rounded-md" value="<?= $edit_data['course_name'] ?>">
            <select name="day" required class="bg-gray-200 dark:bg-gray-700 border p-3 rounded-md">
                <option value="Senin" <?= $edit_data['day'] == "Senin" ? "selected" : "" ?>>Senin</option>
                <option value="Selasa" <?= $edit_data['day'] == "Selasa" ? "selected" : "" ?>>Selasa</option>
                <option value="Rabu" <?= $edit_data['day'] == "Rabu" ? "selected" : "" ?>>Rabu</option>
                <option value="Kamis" <?= $edit_data['day'] == "Kamis" ? "selected" : "" ?>>Kamis</option>
                <option value="Jumat" <?= $edit_data['day'] == "Jumat" ? "selected" : "" ?>>Jumat</option>
            </select>
            <input type="time" name="start_time" required class="bg-gray-200 dark:bg-gray-700 border p-3 rounded-md" value="<?= $edit_data['start_time'] ?>">
            <input type="time" name="end_time" required class="bg-gray-200 dark:bg-gray-700 border p-3 rounded-md" value="<?= $edit_data['end_time'] ?>">
            <input type="text" name="room" placeholder="Ruangan" required class="bg-gray-200 dark:bg-gray-700 border p-3 rounded-md" value="<?= $edit_data['room'] ?>">
            <div class="flex gap-2">
                <?php if ($edit_id): ?>
                    <button type="submit" name="update_schedule" class="bg-green-500 hover:bg-green-600 text-white dark:text-black p-3 rounded-md">✔ Update</button>
                    <a href="schedule.php" class="bg-gray-500 hover:bg-gray-600 text-white dark:text-black p-3 rounded-md">✖ Batal</a>
                <?php else: ?>
                    <button type="submit" name="add_schedule" class="bg-blue-500 hover:bg-blue-600 text-white dark:text-black p-3 rounded-md">➕ Tambah</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6">
        <?php while ($row = $schedules->fetch_assoc()): ?>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-blue-600 dark:text-blue-400"><?= $row['course_name'] ?></h2>
                <p class="text-gray-600 dark:text-gray-400"><?= $row['day'] ?> | ⏰ <?= $row['start_time'] ?> - <?= $row['end_time'] ?></p>
                <p class="text-gray-700 dark:text-gray-300 mt-2">📍 Ruangan: <?= $row['room'] ?></p>
            </div>
            <div class="flex justify-between mt-4">
                <a href="schedule.php?edit=<?= $row['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white dark:text-black p-2 rounded-md flex-1 text-center mr-2">✏ Edit</a>
                <a href="schedule.php?delete=<?= $row['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white dark:text-black p-2 rounded-md flex-1 text-center">🗑 Hapus</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

