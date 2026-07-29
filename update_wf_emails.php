<?php
/**
 * Update phpBB noreply emails from wf_email_import table.
 *
 * Assumptions:
 * - phpBB config file exists at ../../../config.php relative to this script
 * - Import table: wf_email_import
 * - Import columns: id (Wereldfietser member id), email
 * - Link field: <prefix>profile_fields_data.pf_wereldfietser_id
 */

declare(strict_types=1);

// Run preview by default. Use --apply to execute updates.
$apply = isset($argv) && in_array('--apply', $argv, true);

$phpbbRootPath = realpath(__DIR__ . '/../../../');
if ($phpbbRootPath === false) {
    fwrite(STDERR, "Error: Could not resolve phpBB root path from script location." . PHP_EOL);
    exit(1);
}

$configFile = $phpbbRootPath . DIRECTORY_SEPARATOR . 'config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Error: config.php not found at {$configFile}" . PHP_EOL);
    exit(1);
}

require $configFile;

if (!isset($dbms, $dbhost, $dbname, $dbuser, $dbpasswd, $table_prefix)) {
    fwrite(STDERR, "Error: Missing required DB settings in config.php." . PHP_EOL);
    exit(1);
}

if (!in_array($dbms, ['mysqli', 'mysql', 'mysql4'], true)) {
    fwrite(STDERR, "Error: Unsupported dbms '{$dbms}'. This script expects MySQL/MariaDB." . PHP_EOL);
    exit(1);
}

$charset = 'utf8mb4';
$host = $dbhost !== '' ? $dbhost : '127.0.0.1';
$portPart = !empty($dbport) ? ';port=' . $dbport : '';
$dsn = "mysql:host={$host}{$portPart};dbname={$dbname};charset={$charset}";
$usersTable = $table_prefix . 'users';
$profileFieldsDataTable = $table_prefix . 'profile_fields_data';
$importTable = 'wf_email_import';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_TIMEOUT => 10,
];

try {
    $pdo = new PDO($dsn, $dbuser, $dbpasswd, $options);
    $pdo->exec('SET SESSION innodb_lock_wait_timeout = 5');
    $pdo->exec('SET SESSION lock_wait_timeout = 5');

    echo $apply ? "Mode: APPLY" . PHP_EOL : "Mode: PREVIEW" . PHP_EOL;

    $previewSql = "
        SELECT
            u.user_id,
            u.username,
            pfd.pf_wereldfietser_id AS wf_id,
            u.user_email AS old_email,
            TRIM(i.email) AS new_email
        FROM `{$usersTable}` u
        JOIN `{$profileFieldsDataTable}` pfd ON pfd.user_id = u.user_id
        JOIN `{$importTable}` i ON i.id = pfd.pf_wereldfietser_id
        WHERE u.user_email LIKE '%@users.noreply.%'
          AND TRIM(i.email) <> ''
          AND TRIM(i.email) LIKE '%_@_%._%'
    ";

    $rows = $pdo->query($previewSql)->fetchAll();
    echo "Candidates: " . count($rows) . PHP_EOL;

    foreach (array_slice($rows, 0, 20) as $r) {
        echo "{$r['user_id']} | {$r['username']} | {$r['old_email']} -> {$r['new_email']}" . PHP_EOL;
    }
    if (count($rows) > 20) {
        echo "... showing first 20 only" . PHP_EOL;
    }

    if (!$apply) {
        echo PHP_EOL . "Preview only. Run with --apply to execute updates." . PHP_EOL;
        exit(0);
    }

    $pdo->beginTransaction();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wf_email_backup_20260729 (
            user_id INT UNSIGNED NOT NULL PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            user_email VARCHAR(255) NOT NULL,
            backed_up_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        INSERT INTO wf_email_backup_20260729 (user_id, username, user_email)
        SELECT u.user_id, u.username, u.user_email
        FROM `{$usersTable}` u
        JOIN `{$profileFieldsDataTable}` pfd ON pfd.user_id = u.user_id
        JOIN `{$importTable}` i ON i.id = pfd.pf_wereldfietser_id
        WHERE u.user_email LIKE '%@users.noreply.%'
          AND TRIM(i.email) <> ''
          AND TRIM(i.email) LIKE '%_@_%._%'
        ON DUPLICATE KEY UPDATE user_email = VALUES(user_email), backed_up_at = CURRENT_TIMESTAMP
    ");

    $updateSql = "
        UPDATE `{$usersTable}` u
        JOIN `{$profileFieldsDataTable}` pfd ON pfd.user_id = u.user_id
        JOIN `{$importTable}` i ON i.id = pfd.pf_wereldfietser_id
        SET u.user_email = TRIM(i.email)
        WHERE u.user_email LIKE '%@users.noreply.%'
          AND TRIM(i.email) <> ''
          AND TRIM(i.email) LIKE '%_@_%._%'
    ";
    $updated = $pdo->exec($updateSql);

    $remaining = $pdo->query("
        SELECT COUNT(*) AS c
        FROM `{$usersTable}`
        WHERE user_email LIKE '%@users.noreply.%'
    ")->fetch()['c'];

    $pdo->commit();

    echo PHP_EOL . "Updated users: {$updated}" . PHP_EOL;
    echo "Remaining noreply emails: {$remaining}" . PHP_EOL;
    echo "Backup table: wf_email_backup_20260729" . PHP_EOL;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
