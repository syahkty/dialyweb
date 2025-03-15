<?php
include "config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $receiver_id = $_POST['receiver_id'];

    $sql = "INSERT INTO shared_tasks (task_id, sender_id, receiver_id) 
            VALUES (:task_id, :sender_id, :receiver_id)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':task_id' => $task_id,
        ':sender_id' => $user_id,
        ':receiver_id' => $receiver_id
    ]);

    if ($result) {
        $_SESSION['success_message'] = "Tugas berhasil dibagikan!";
    } else {
        $_SESSION['error_message'] = "Gagal membagikan tugas!";
    }

    header("Location: task.php");
    exit();
}
?>
