<?php
include "../config.php";
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Tambah Jadwal
if (isset($_POST['add_schedule'])) {
    $day = $_POST['day'];
    $course_name = $_POST['course_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];

    $sql = "INSERT INTO schedule (day, course_name, start_time, end_time, room, user_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$day, $course_name, $start_time, $end_time, $room, $user_id]);
    
    $_SESSION['success_message'] = "Jadwal berhasil ditambahkan!";
    header("Location: schedule.php");
    exit();
}

// Hapus Jadwal (Pakai JavaScript, jadi hapus di sini hanya eksekusi)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM schedule WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    
    $_SESSION['success_message'] = "Jadwal berhasil dihapus!";
    header("Location: schedule.php");
    exit();
}

// Ambil Data Jadwal
$stmt = $pdo->prepare("SELECT * FROM schedule 
                        WHERE user_id = ? 
                        ORDER BY FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')");
$stmt->execute([$user_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil Data untuk Edit
$edit_id = "";
$edit_data = ["day" => "", "course_name" => "", "start_time" => "", "end_time" => "", "room" => ""];
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM schedule WHERE id = ? AND user_id = ?");
    $stmt->execute([$edit_id, $user_id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit_data;
}

// Simpan Perubahan Edit Jadwal
if (isset($_POST['update_schedule'])) {
    $id = $_POST['id'];
    $day = $_POST['day'];
    $course_name = $_POST['course_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];

    $stmt = $pdo->prepare("UPDATE schedule SET day=?, course_name=?, start_time=?, end_time=?, room=? WHERE id=? AND user_id=?");
    $stmt->execute([$day, $course_name, $start_time, $end_time, $room, $id, $user_id]);
    
    $_SESSION['success_message'] = "Jadwal berhasil diperbarui!";
    header("Location: schedule.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Harian | Jadwal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Konfigurasi Tailwind agar dark mode pakai class
        tailwind.config = {
            darkMode: 'class'
        };

        document.addEventListener("DOMContentLoaded", () => {
            const theme = localStorage.getItem("theme");
            
            if (theme === "dark") {
                document.documentElement.classList.add("dark");
                document.getElementById("darkModeIcon").innerHTML = "☀️";
            } else {
                document.documentElement.classList.remove("dark");
                document.getElementById("darkModeIcon").innerHTML = "🌙";
            }

            // SweetAlert jika ada pesan sukses
            <?php if (isset($_SESSION['success_message'])): ?>
                let isDarkMode = document.documentElement.classList.contains('dark');
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: "<?= $_SESSION['success_message'] ?>",
                    showConfirmButton: false,
                    timer: 2000,
                    background: isDarkMode ? '#1E293B' : '#ffffff',
                    color: isDarkMode ? '#ffffff' : '#000000'
                });
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        });

        function toggleDarkMode() {
            if (document.documentElement.classList.contains("dark")) {
                document.documentElement.classList.remove("dark");
                localStorage.setItem("theme", "light");
                document.getElementById("darkModeIcon").innerHTML = "🌙";
            } else {
                document.documentElement.classList.add("dark");
                localStorage.setItem("theme", "dark");
                document.getElementById("darkModeIcon").innerHTML = "☀️";
            }
        }

        function confirmDelete(id) {
            let isDarkMode = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: "Konfirmasi Hapus",
                text: "Apakah Anda yakin ingin menghapus jadwal ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal",
                background: isDarkMode ? '#1E293B' : '#ffffff',
                color: isDarkMode ? '#ffffff' : '#000000'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "schedule.php?delete=" + id;
                }
            });
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

    <a href="../index.php" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md mb-6 inline-block">⬅ Kembali</a>

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
            <input type="text" name="room" placeholder="Ruangan" required class="bg-gray-200 dark:bg-gray-700 border p-3 rounded-md" value="<?= $edit_data["room"]?>">
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
    <?php foreach ($schedules as $row): ?>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col justify-between">
        <div>
            <h2 class="text-lg font-bold text-blue-600 dark:text-blue-400"><?= htmlspecialchars($row['course_name']) ?></h2>
            <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($row['day']) ?> | ⏰ <?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></p>
            <p class="text-gray-700 dark:text-gray-300 mt-2">📍 Ruangan: <?= htmlspecialchars($row['room']) ?></p>
        </div>
        <div class="flex justify-start mt-4">
            <a href="schedule.php?edit=<?= $row['id'] ?>" class="bg-yellow-500 p-2 rounded-md me-2 pe-4">✏ Edit</a>
            <button onclick="confirmDelete(<?= $row['id'] ?>)" class="bg-red-500 p-2 rounded-md">🗑 Hapus</button>
        </div>
    </div>
<?php endforeach; ?>
    </div>
</body>
</html>
