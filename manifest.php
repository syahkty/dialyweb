<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$manifest = [
    "name"=> "Tugas Kuliah",
    "short_name"=> "TUKU",
    "start_url"=> "/index.php",
    "display"=> "standalone",
    "background_color"=> "#ffffff",
    "theme_color"=> "#0d6efd",
    "description"=> "Aplikasi manajemen tugas dan jadwal",
    "icons"=> [
      [
        "src"=> "https://syahkty.web.id/gambar/favicon.png"
,
        "sizes"=> "192x192",
        "type"=> "image/png"
      ],
      [
        "src"=> "https://syahkty.web.id/gambar/favicon.png",
        "sizes"=> "512x512",
        "type"=> "image/png"
      ]
    ]
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
