<?php
include "config.php";
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // ✅ Pindahkan ini ke awal!

// Ambil tugas yang dibagikan kepada user yang sedang login
$stmt = $pdo->prepare("SELECT 
    st.id AS shared_task_id,  -- Ambil ID dari shared_tasks
    t.title, 
    t.due_date, 
    t.detail, 
    u.username AS sender_name, 
    st.completed_at AS shared_completed_at
FROM shared_tasks st
JOIN tasks t ON st.task_id = t.id
JOIN users u ON st.sender_id = u.id
WHERE st.receiver_id = ?;
");
$stmt->execute([$user_id]);
$shared_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $due_date = $_POST['due_date'];
    $schedule_id = $_POST['schedule_id'];
    $detail = $_POST['detail'];

    // Gunakan PDO untuk INSERT
    $sql = "INSERT INTO tasks (title, due_date, schedule_id, user_id, detail) 
            VALUES (:title, :due_date, :schedule_id, :user_id, :detail)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':title' => $title,
        ':due_date' => $due_date,
        ':schedule_id' => $schedule_id,
        ':user_id' => $user_id,
        ':detail' => $detail
    ]);

    if ($result) {
        $_SESSION['success_message'] = "Tugas berhasil ditambahkan!";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan tugas!";
    }

    header("Location: task.php");
    exit();
}

// Ambil Data Tugas Belum Selesai
$stmt = $pdo->prepare("SELECT tasks.*, schedule.course_name FROM tasks 
                        LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                        WHERE tasks.status = 'Belum' AND tasks.user_id = ?");
$stmt->execute([$user_id]);
$pending_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil Data Tugas Selesai
$stmt = $pdo->prepare("SELECT tasks.*, schedule.course_name FROM tasks 
                        LEFT JOIN schedule ON tasks.schedule_id = schedule.id 
                        WHERE tasks.status = 'Selesai' AND tasks.user_id = ?");
$stmt->execute([$user_id]);
$done_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar tugas yang sudah selesai (termasuk tugas sendiri dan tugas yang dibagikan)
$stmt = $pdo->prepare("
    SELECT id, title, completed_at FROM tasks 
    WHERE user_id = ? AND status = 'Selesai'
    UNION
    SELECT shared_tasks.id, tasks.title, shared_tasks.completed_at 
    FROM shared_tasks 
    JOIN tasks ON shared_tasks.task_id = tasks.id 
    WHERE shared_tasks.receiver_id = ? AND shared_tasks.completed_at IS NOT NULL
    ORDER BY completed_at DESC
");
$stmt->execute([$user_id, $user_id]);
$done_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Query hanya mengambil jadwal milik user yang login
$stmt = $pdo->prepare("SELECT * FROM schedule WHERE user_id = ?");
$stmt->execute([$user_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <title>Dashboard Harian | Tugas</title>
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
            let isDarkMode = document.documentElement.classList.contains('dark');

    <?php if (isset($_SESSION['success_message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: '<?= $_SESSION['success_message'] ?>',
            showConfirmButton: false,
            timer: 2000,
            background: isDarkMode ? '#1E293B' : '#ffffff', // Warna dark/light mode
            color: isDarkMode ? '#ffffff' : '#000000' // Warna teks dark/light mode
        });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: '<?= $_SESSION['error_message'] ?>',
            showConfirmButton: false,
            timer: 2000,
            background: isDarkMode ? '#1E293B' : '#ffffff', // Warna dark/light mode
            color: isDarkMode ? '#ffffff' : '#000000' // Warna teks dark/light mode
        });
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <?php foreach ($schedules as $row): ?>

                <option value="<?= $row['id'] ?>"><?= $row['course_name'] ?></option>
            <?php endforeach; ?>
        </select>
        <textarea name="detail" placeholder="Detail Tugas" required 
          class="w-full bg-gray-200 dark:bg-gray-700 p-5 rounded-md text-xl"></textarea>
        <button type="submit" name="add_task" class="w-full bg-blue-500 text-white p-5 rounded-md text-xl">➕ Tambah</button>
    </form>

    <!-- Daftar Tugas -->
    <h2 class="text-2xl font-bold mt-6 mb-4">📌 Tugas Belum Selesai</h2>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
    <?php if (count($pending_tasks) > 0): ?>
    <?php foreach ($pending_tasks as $row): ?>
        <div class="flex flex-col sm:flex-row sm:justify-between gap-2 border-b pb-4 mb-4">
            <div>
                <h3 class="font-semibold text-xl"><?= htmlspecialchars($row['title']) ?></h3>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    <?= htmlspecialchars($row['course_name']) ?> | ⏳ <?= htmlspecialchars($row['due_date']) ?>
                </p>
                <p class="text-gray-700 dark:text-gray-300 mr-6"><?= htmlspecialchars($row['detail']) ?></p>
            </div>
            <a href="update_task.php?id=<?= $row['id'] ?>" 
               class="bg-green-500 text-white p-4 text-xl rounded-md text-center sm:w-40 h-15">
                ✔ Selesaikan
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-gray-500 dark:text-gray-400 text-lg text-center">
        🎉 Tidak ada tugas pending! Nikmati harimu! 🎉
    </p>
<?php endif; ?>
    </div>

    <form action="share_task.php" method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md flex flex-col gap-4 mt-4">
    <label for="task">Pilih Tugas:</label>
    <select name="task_id" required class="w-full bg-gray-200 dark:bg-gray-700 p-5 rounded-md text-xl">
        <?php
        // Ambil daftar tugas milik user yang login
        $stmt = $pdo->prepare("SELECT id, title FROM tasks WHERE user_id = ? AND due_date >= CURDATE()");
        $stmt->execute([$user_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($tasks as $task) {
            echo "<option value='{$task['id']}'>{$task['title']}</option>";
        }
        ?>
    </select>

    <label for="friend">Pilih Teman:</label>
    <select name="receiver_id" required class="w-full bg-gray-200 dark:bg-gray-700 p-5 rounded-md text-xl">
    <?php
    // Ambil daftar teman yang sudah diterima
    $query = "SELECT users.id, users.username FROM friends 
              JOIN users ON friends.friend_id = users.id 
              WHERE friends.user_id = ? AND friends.status = 'accepted'
              UNION
              SELECT users.id, users.username FROM friends 
              JOIN users ON friends.user_id = users.id 
              WHERE friends.friend_id = ? AND friends.status = 'accepted'";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Loop untuk menampilkan daftar teman dalam <option>
    foreach ($friends as $friend) {
        echo "<option value='{$friend['id']}'>{$friend['username']}</option>";
    }
    ?>
</select>


    <button type="submit" name="share_task" class="w-full bg-blue-500 text-white p-5 rounded-md text-xl">Bagikan</button>
</form>

<!-- Tugas yang Dibagikan -->
<h2 class="text-2xl font-bold mt-6 mb-4">📨 Tugas yang Dibagikan</h2>
<div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
    <?php if (count($shared_tasks) > 0): ?>
        <?php foreach ($shared_tasks as $row): ?>
            <div class="border-b pb-4 mb-4">
                <h3 class="font-semibold text-xl"><?= htmlspecialchars($row['title']) ?></h3>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    Dari: <?= htmlspecialchars($row['sender_name']) ?> | ⏳ <?= htmlspecialchars($row['due_date']) ?>
                </p>
                <p class="text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['detail']) ?></p>
                
                <!-- Tombol Selesaikan -->
                <?php if (empty($row['shared_completed_at']) || $row['shared_completed_at'] == "0000-00-00 00:00:00"): ?>
                    <form action="complete_shared_task.php" method="POST">
    <input type="hidden" name="shared_task_id" value="<?= $row['shared_task_id'] ?>">
    <button type="submit" class="mt-3 px-4 py-2 bg-green-500 text-white rounded-lg shadow-md hover:bg-green-600 transition">
        ✅ Selesaikan
    </button>
</form>
<p class="text-sm text-gray-500">🆔 ID Shared Task: <?= $row['shared_task_id'] ?></p>

                <?php else: ?>
                    <p class="text-green-500 font-bold mt-2">✅ Tugas sudah selesai</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-500 dark:text-gray-400 text-lg text-center">
            ❌ Tidak ada tugas yang dibagikan!
        </p>
    <?php endif; ?>
</div>





    <!-- Tugas Selesai -->
    <h2 class="text-2xl font-bold mt-6 mb-4">✅ Tugas Selesai</h2>
    <div class="space-y-3">
    <?php foreach ($done_tasks as $row): ?>
    <div class="bg-white dark:bg-gray-800 p-5 rounded-md shadow-md flex flex-col sm:flex-row sm:justify-between">
        <div>
            <h3 class="font-semibold text-xl"><?= htmlspecialchars($row['title']) ?></h3>
            <p class="text-gray-600 dark:text-gray-400 text-lg">
                <?= htmlspecialchars($row['title']) ?> | ✅ <?= htmlspecialchars($row['completed_at']) ?>
            </p>
        </div>
    </div>
<?php endforeach; ?>

    </div>
</div>
<h2 class="text-2xl font-bold mt-6 mb-4">✅ Grafik Tugas</h2>
<div class="flex justify-center items-center h-80 pt-10">
     <canvas id="taskChart"></canvas>
    </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let ctx = document.getElementById('taskChart').getContext('2d');
    let taskChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Selesai', 'Belum Selesai'],
            datasets: [{
                data: [<?= count($done_tasks) ?>, <?= count($pending_tasks) ?>],
                backgroundColor: ['#10B981', '#EF4444']
            }]
        }
    });
</script>

<?php
// Tampilkan notifikasi jika ada
if (isset($_SESSION['error_message']) || isset($_SESSION['success_message'])):
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php endif; ?>
</body>
</html>
