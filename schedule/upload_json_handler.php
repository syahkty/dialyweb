<?php
// upload_json_handler.php
declare(strict_types=1);

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); exit();
}

if (!isset($_POST['import_schedule_json']) || !isset($_FILES['schedule_json'])) {
  $_SESSION['error_message'] = 'Tidak ada file JSON yang diunggah.';
  header('Location: schedule.php'); exit();
}

/* ---- validasi & baca file ---- */
$tmp = $_FILES['schedule_json']['tmp_name'];
if (!is_uploaded_file($tmp)) {
  $_SESSION['error_message'] = 'Upload JSON gagal.';
  header('Location: schedule.php'); exit();
}

$mimeOk = ['application/json', 'text/json', 'text/plain'];
$finfo  = finfo_open(FILEINFO_MIME_TYPE);
$mime   = finfo_file($finfo, $tmp);
finfo_close($finfo);
if (!in_array($mime, $mimeOk, true)) {
  // tetap izinkan, beberapa browser melabeli .json sebagai text/plain
}

$raw = file_get_contents($tmp);
$data = json_decode($raw, true);

if ($data === null) {
  $_SESSION['error_message'] = 'Format JSON tidak valid.';
  header('Location: schedule.php'); exit();
}

/* ---- ambil array records ----
   ekspektasi: array of objects, contoh:
   [
     {"hari":"Selasa","mulai":"13:10","selesai":"15:00","ruang":"TI2","mata_kuliah":"Pemrograman Mobile"},
     ...
   ]
   Kita juga dukung kunci alternatif: day/hari, course_name/mata_kuliah, start_time/mulai, end_time/selesai, room/ruang
*/
if (isset($data['data']) && is_array($data['data']))    $records = $data['data'];
elseif (isset($data['records']) && is_array($data['records'])) $records = $data['records'];
elseif (is_array($data))                                   $records = $data;
else {
  $_SESSION['error_message'] = 'Struktur JSON tidak dikenali.';
  header('Location: schedule.php'); exit();
}

function norm_time(string $t): string {
  $t = str_replace('O', '0', trim($t));       // perbaiki OCR O->0 jika ada
  if (preg_match('/^(\d{1,2}):(\d{1,2})$/', $t, $m)) {
    return sprintf('%02d:%02d:00', (int)$m[1], (int)$m[2]); // <-- tambahkan :00
  }
  return $t;
}

$userId = (int)$_SESSION['user_id'];

/* ---- siapkan statement ---- */
$insert = $pdo->prepare(
  "INSERT INTO schedule (day, course_name, start_time, end_time, room, user_id)
   VALUES (?, ?, ?, ?, ?, ?)"
);
$cekDup = $pdo->prepare(
  "SELECT id FROM schedule
   WHERE user_id=? AND day=? AND course_name=? AND start_time=? AND end_time=? AND room=? LIMIT 1"
);

$pdo->beginTransaction();
$ok = 0;

try {
  foreach ($records as $r) {
    if (!is_array($r)) continue;

    // mapping kunci (dukung Bahasa Indonesia & Inggris)
    $day   = $r['day']         ?? $r['hari']        ?? null;
    $name  = $r['course_name'] ?? $r['mata_kuliah'] ?? $r['matakuliah'] ?? null;
    $start = $r['start_time']  ?? $r['mulai']       ?? null;
    $end   = $r['end_time']    ?? $r['selesai']     ?? null;
    $room  = $r['room']        ?? $r['ruang']       ?? null;

    // validasi minimal kolom yang ada di tabel
    if (!$day || !$name || !$start || !$end || !$room) {
      continue; // lewati baris yang kurang lengkap
    }

    $start = norm_time((string)$start);
    $end   = norm_time((string)$end);
    $day   = ucfirst(strtolower((string)$day));
    $room  = strtoupper(str_replace('O','0',(string)$room)); // SG01/TI2

    // anti duplikat
    $cekDup->execute([$userId, $day, $name, $start, $end, $room]);
    if ($cekDup->fetchColumn()) continue;

    $insert->execute([$day, $name, $start, $end, $room, $userId]);
    $ok += $insert->rowCount();
  }

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  $_SESSION['error_message'] = 'DB error: ' . $e->getMessage();
  header('Location: schedule.php'); exit();
}

if ($ok === 0) {
  $_SESSION['error_message'] = 'Tidak ada baris baru yang dimasukkan (mungkin duplikat atau JSON kosong).';
} else {
  $_SESSION['success_message'] = "Impor JSON selesai. $ok jadwal berhasil dimasukkan.";
}
header('Location: schedule.php'); exit();
