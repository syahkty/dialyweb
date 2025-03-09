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

// Ambil Data Tugas yang Belum Selesai
$pending_tasks = $conn->query("SELECT tasks.*, schedule.course_name FROM tasks 
                               LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                               WHERE tasks.status = 'Belum'");

// Ambil Data Tugas yang Sudah Selesai
$done_tasks = $conn->query("SELECT tasks.*, schedule.course_name FROM tasks 
                            LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                            WHERE tasks.status = 'Selesai'");

$tasks = $conn->query("SELECT tasks.*, schedule.course_name FROM tasks 
                       LEFT JOIN schedule ON tasks.schedule_id = schedule.id");

// Cek apakah notifikasi sudah pernah ditampilkan
if (!isset($_SESSION['notified'])) {
    // Ambil tugas yang deadline dalam 1 hari dan belum selesai
    $tomorrow = date("Y-m-d", strtotime("+2 day"));
    $result = $conn->query("
        SELECT tasks.title, schedule.course_name 
        FROM tasks 
        LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
        WHERE tasks.due_date = '$tomorrow' AND tasks.status != 'Selesai'
    ");

    if ($result->num_rows > 0) {
        $_SESSION['notified'] = true; // Tandai sudah menampilkan notifikasi
        
        echo "<div class='notif bg-yellow-300 p-2 mb-2'>";
        echo "⚠️ Tugas berikut akan deadline besok:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "<b>" . $row['title'] . "</b> - " . $row['course_name'] . "<br>";
        }
        echo "</div>";
    }
}

$schedules = $conn->query("SELECT * FROM schedule");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Todo Tugas Kuliah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">

<h1 class="text-2xl font-bold mb-4">Daftar Tugas Kuliah</h1>
<a href="schedule.php" class="bg-green-500 text-white p-2 rounded-md mb-4 inline-block">➡️ Jadwal</a>
<a href="reset_notif.php" class="bg-red-500 text-white p-2 rounded">Reset Notifikasi</a>


<!-- Form Tambah Tugas -->
<form method="POST" class="mb-4">
    <input type="text" name="title" placeholder="Judul Tugas" required class="border p-2 mr-2">
    <input type="date" name="due_date" required class="border p-2 mr-2">
    <select name="schedule_id" required class="border p-2 mr-2">
        <option value="">Pilih Mata Kuliah</option>
        <?php while ($row = $schedules->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['course_name'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" name="add_task" class="bg-blue-500 text-white p-2">Tambah</button>
</form>

<!-- Tabel Tugas Belum Selesai -->
<h2 class="text-xl font-bold mt-6 mb-2">Tugas Belum Selesai</h2>
<table class="border-collapse border w-full">
    <tr class="bg-gray-200">
        <th class="border p-2">Tugas</th>
        <th class="border p-2">Mata Kuliah</th>
        <th class="border p-2">Deadline</th>
        <th class="border p-2">Aksi</th>
    </tr>
    <?php while ($row = $pending_tasks->fetch_assoc()): ?>
    <tr>
        <td class="border p-2"><?= $row['title'] ?></td>
        <td class="border p-2"><?= $row['course_name'] ?></td>
        <td class="border p-2"><?= $row['due_date'] ?></td>
        <td class="border p-2">
            <a href="update_task.php?id=<?= $row['id'] ?>" class="bg-green-500 text-white p-1 rounded">✔ Selesaikan</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Tabel Tugas Selesai -->
<h2 class="text-xl font-bold mt-6 mb-2">Tugas Selesai</h2>
<table class="border-collapse border w-full">
    <tr class="bg-gray-200">
        <th class="border p-2">Tugas</th>
        <th class="border p-2">Mata Kuliah</th>
        <th class="border p-2">Deadline</th>
    </tr>
    <?php while ($row = $done_tasks->fetch_assoc()): ?>
    <tr>
        <td class="border p-2"><?= $row['title'] ?></td>
        <td class="border p-2"><?= $row['course_name'] ?></td>
        <td class="border p-2"><?= $row['due_date'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<script>
    function dismissNotif() {
        document.getElementById("notifBox").style.display = "none";

        // Kirim request ke PHP untuk menghapus session
        fetch("clear_notif.php").then(() => location.reload());
    }
</script>


</script>
</body>
</html>
