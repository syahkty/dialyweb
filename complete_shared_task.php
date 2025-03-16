<?php
require 'config.php'; // Pastikan koneksi database ada

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['shared_task_id'])) {
    $shared_task_id = intval($_POST['shared_task_id']); // Pastikan input adalah angka
    $completed_at = date('Y-m-d H:i:s');

    if ($shared_task_id > 0) { // Pastikan ID valid
        $stmt = $pdo->prepare("UPDATE shared_tasks SET completed_at = ? WHERE id = ?");
        $success = $stmt->execute([$completed_at, $shared_task_id]);

        if ($success) {
            header("Location: task.php?success=shared_task_completed");
            exit();
        } else {
            header("Location: task.php?error=failed_to_update");
            exit();
        }
    } else {
        header("Location: task.php?error=invalid_id");
        exit();
    }
} else {
    header("Location: task.php?error=invalid_request");
    exit();
}
