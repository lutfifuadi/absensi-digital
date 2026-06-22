<?php
// Script untuk backup database legacy absensi_smkpnc
$db = [
    'host' => 'panel.smkputranasionalcibodas.sch.id',
    'dbname' => 'absensi_smkpnc',
    'user' => 'absensi_smkpnc',
    'pass' => 'Smkpnc2k23.'
];

try {
    echo "========================================================\n";
    echo "BACKUP DATABASE LEGACY SMK PNC\n";
    echo "========================================================\n";

    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4", $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $backupFile = 'D:\Project\Aplikasi Presensi\.planning\backup_legacy_smkpnc.sql';
    $handle = fopen($backupFile, 'w');
    if (!$handle) {
        throw new Exception("Gagal membuka file backup untuk menulis!");
    }

    // Tulis header SQL
    fwrite($handle, "-- Backup Database Legacy: {$db['dbname']}\n");
    fwrite($handle, "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

    // Dapatkan semua tabel
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        echo "Mengamankan tabel: {$table}...\n";
        
        // Tulis DROP TABLE
        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
        
        // Dapatkan CREATE TABLE
        $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
        $createRow = $createStmt->fetch();
        fwrite($handle, $createRow['Create Table'] . ";\n\n");
        
        // Dapatkan Data
        $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
        $rows = $dataStmt->fetchAll();
        
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));
                $values = array_map(function($v) use ($pdo) {
                    if ($v === null) return 'NULL';
                    return $pdo->quote($v);
                }, array_values($row));
                
                $sql = "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                fwrite($handle, $sql);
            }
            fwrite($handle, "\n");
        }
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($handle);
    
    echo "========================================================\n";
    echo "BACKUP SELESAI! File disimpan di: $backupFile\n";
    echo "========================================================\n";

} catch (Exception $e) {
    echo "ERROR BACKUP: " . $e->getMessage() . "\n";
}
