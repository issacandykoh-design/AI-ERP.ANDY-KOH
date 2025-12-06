<?php
declare(strict_types=1);

$envFile = __DIR__ . DIRECTORY_SEPARATOR . 'env.production';
if (!is_file($envFile)) {
    fwrite(STDERR, "env.production not found\n");
    exit(1);
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos($line, '=') === false) {
        continue;
    }
    if ($line[0] === '#') {
        continue;
    }
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $v = trim($v, "\"' ");
    $env[$k] = $v;
}
$host = $env['DB_HOST'] ?? null;
$db = $env['DB_DATABASE'] ?? null;
$user = $env['DB_USERNAME'] ?? null;
$pass = $env['DB_PASSWORD'] ?? null;
if (!$host || !$db || !$user || $pass === null) {
    fwrite(STDERR, "Missing DB settings in env.production\n");
    exit(1);
}
$fix = in_array('--fix', $argv, true);
$count = in_array('--count', $argv, true);
$size = in_array('--size', $argv, true);
$dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "DB connect error\n");
    exit(1);
}
$issues = [];
if ($size) {
    try {
        $sql = 'SELECT COALESCE(SUM(data_length + index_length),0) FROM information_schema.tables WHERE table_schema = DATABASE()';
        $stmt = $pdo->query($sql);
        $bytes = (int) $stmt->fetchColumn();
        $gb = $bytes / 1000000000;
        $gib = $bytes / 1073741824;
        echo number_format($gb, 3, '.', '') . " GB\n";
        echo number_format($gib, 3, '.', '') . " GiB\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "size_error\n");
        exit(1);
    }
}
if ($count) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
    $stmt->execute([$db]);
    echo (int) $stmt->fetchColumn() . "\n";
    exit(0);
}
$tables = $pdo->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema = ?');
$tables->execute([$db]);
foreach ($tables->fetchAll(PDO::FETCH_COLUMN) as $table) {
$pkStmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = ? AND table_name = ? AND constraint_type = \'PRIMARY KEY\'');
    $pkStmt->execute([$db, $table]);
    $hasPk = (int) $pkStmt->fetchColumn() > 0;
    if (!$hasPk) {
        $issues[] = ['type' => 'missing_primary_key', 'table' => $table];
    }
}
$fkStmt = $pdo->prepare('SELECT kcu.table_name AS tbl, kcu.column_name AS col, kcu.referenced_table_name AS ref_tbl, kcu.referenced_column_name AS ref_col FROM information_schema.key_column_usage kcu JOIN information_schema.table_constraints tc ON kcu.constraint_name = tc.constraint_name AND kcu.table_schema = tc.table_schema AND kcu.table_name = tc.table_name WHERE tc.constraint_type = \'FOREIGN KEY\' AND kcu.table_schema = ?');
$fkStmt->execute([$db]);
$fks = $fkStmt->fetchAll();
foreach ($fks as $fk) {
    if (!$fk['ref_tbl'] || !$fk['ref_col']) {
        continue;
    }
    $refIdx = $pdo->prepare('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND column_name = ?');
    $refIdx->execute([$db, $fk['ref_tbl'], $fk['ref_col']]);
    $refHasIndex = (int) $refIdx->fetchColumn() > 0;
    if (!$refHasIndex) {
        $issues[] = [
            'type' => 'missing_index_on_referenced',
            'table' => $fk['ref_tbl'],
            'column' => $fk['ref_col'],
            'referencer' => $fk['tbl'] . '.' . $fk['col'],
        ];
    }
}
usort($issues, function ($a, $b) {
    return strcmp(($a['table'] ?? '') . ($a['column'] ?? ''), ($b['table'] ?? '') . ($b['column'] ?? ''));
});
if (!$fix) {
    foreach ($issues as $i) {
        if ($i['type'] === 'missing_primary_key') {
            echo "missing_primary_key \t" . $i['table'] . "\n";
        } elseif ($i['type'] === 'missing_index_on_referenced') {
            echo "missing_index_on_referenced \t" . $i['table'] . "." . $i['column'] . " \tref: " . $i['referencer'] . "\n";
        }
    }
    if (empty($issues)) {
        echo "no_issues\n";
    }
    exit(0);
}
$applied = [];
foreach ($issues as $i) {
    if ($i['type'] === 'missing_index_on_referenced') {
        $idxName = 'idx_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $i['column']) . '_' . substr(md5($i['table'] . $i['column']), 0, 6);
        $sql = 'ALTER TABLE `' . $i['table'] . '` ADD INDEX `' . $idxName . '` (`' . $i['column'] . '`)';
        try {
            $pdo->exec($sql);
            $applied[] = $sql;
        } catch (Throwable $e) {
            $applied[] = 'error ' . $i['table'] . '.' . $i['column'];
        }
    }
    if ($i['type'] === 'missing_primary_key') {
        $hasIdColStmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = \'id\'');
        $hasIdColStmt->execute([$db, $i['table']]);
        $hasId = (int) $hasIdColStmt->fetchColumn() > 0;
        if (!$hasId) {
            $sql = 'ALTER TABLE `' . $i['table'] . '` ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST';
            try {
                $pdo->exec($sql);
                $applied[] = $sql;
            } catch (Throwable $e) {
                $applied[] = 'error add id ' . $i['table'];
            }
        } else {
            $sql = 'ALTER TABLE `' . $i['table'] . '` ADD PRIMARY KEY (`id`)';
            try {
                $pdo->exec($sql);
                $applied[] = $sql;
            } catch (Throwable $e) {
                $applied[] = 'error add pk ' . $i['table'];
            }
        }
    }
}
foreach ($applied as $a) {
    echo $a . "\n";
}
exit(0);
