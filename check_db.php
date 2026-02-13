<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=koleksi_buku;charset=utf8mb4','root','');
    $b = $pdo->query('SELECT COUNT(*) FROM buku')->fetchColumn();
    $k = $pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
    echo "buku={$b}\nkategori={$k}\n";
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
