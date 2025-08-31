<?php
echo "<pre>";
$base = __DIR__;
$path = $base.'/vendor/composer/autoload_static.php';
echo "BASE DIR : $base\n";
echo "CHECK    : $path\n";
echo "EXISTS   : ".(file_exists($path)?'YES':'NO')."\n";
echo "READABLE : ".(is_readable($path)?'YES':'NO')."\n";
echo "REALPATH : ".(realpath($path) ?: '(none)')."\n";
echo "LIST DIR :\n";
print_r(scandir($base.'/vendor/composer'));
