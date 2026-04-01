<?php
/**
 * TAMEC Mail Queue Worker
 * Run via CLI: php c:\laragon\www\tamec\worker.php
 *
 * Schedule with Windows Task Scheduler to run every 1-2 minutes.
 * Never accessible via browser (CLI-only guard below).
 *
 * Required table (run once in MySQL):
 *
 *   CREATE TABLE mail_queue (
 *     id            INT AUTO_INCREMENT PRIMARY KEY,
 *     to_email      VARCHAR(255)  NOT NULL,
 *     to_name       VARCHAR(255)  DEFAULT '',
 *     subject       VARCHAR(500)  NOT NULL,
 *     body          LONGTEXT      NOT NULL,
 *     status        ENUM('pending','sent','failed') DEFAULT 'pending',
 *     attempts      TINYINT       DEFAULT 0,
 *     error_message TEXT,
 *     created_at    DATETIME      DEFAULT CURRENT_TIMESTAMP,
 *     sent_at       DATETIME
 *   );
 */

// ── Safety: CLI only ──────────────────────────────────────────────────────────
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden' . PHP_EOL);
}

define('BATCH_SIZE', 10);   // emails per run
define('MAX_ATTEMPTS', 3);  // give up after 3 failures

// ── Bootstrap ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/public/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/app/models/PHPMailer/src/Exception.php';
require_once __DIR__ . '/app/models/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/app/models/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// ── DB connection ─────────────────────────────────────────────────────────────
$db = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME'],
    (int) ($_ENV['DB_PORT'] ?? 3306)
);

if ($db->connect_errno) {
    fwrite(STDERR, '[worker] DB connection failed: ' . $db->connect_error . PHP_EOL);
    exit(1);
}
$db->set_charset('utf8mb4');

// ── Fetch pending batch ───────────────────────────────────────────────────────
$stmt = $db->prepare(
    "SELECT id, to_email, to_name, subject, body
     FROM mail_queue
     WHERE status = 'pending' AND attempts < ?
     ORDER BY created_at ASC
     LIMIT " . BATCH_SIZE
);
$maxAttempts = MAX_ATTEMPTS;
$stmt->bind_param('i', $maxAttempts);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($rows)) {
    echo '[worker] No pending emails.' . PHP_EOL;
    exit(0);
}

echo '[worker] Processing ' . count($rows) . ' email(s)...' . PHP_EOL;

// ── Process each queued email ─────────────────────────────────────────────────
foreach ($rows as $row) {
    // Increment attempts immediately so concurrent runs don't double-send
    $upd = $db->prepare("UPDATE mail_queue SET attempts = attempts + 1 WHERE id = ?");
    $upd->bind_param('i', $row['id']);
    $upd->execute();
    $upd->close();

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->Timeout    = 15;

        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], 'TAMEC Care Staffing Services');
        $mail->addAddress($row['to_email'], $row['to_name']);
        $mail->isHTML(true);
        $mail->Subject = $row['subject'];
        $mail->Body    = $row['body'];
        $mail->AltBody = strip_tags($row['body']);

        $mail->send();

        $done = $db->prepare(
            "UPDATE mail_queue SET status = 'sent', sent_at = NOW() WHERE id = ?"
        );
        $done->bind_param('i', $row['id']);
        $done->execute();
        $done->close();

        echo '[worker] Sent → ' . $row['to_email'] . PHP_EOL;

    } catch (PHPMailerException $e) {
        $errMsg = $e->getMessage();

        // Mark failed only if max attempts reached (already incremented above)
        $check = $db->prepare("SELECT attempts FROM mail_queue WHERE id = ?");
        $check->bind_param('i', $row['id']);
        $check->execute();
        $cur = $check->get_result()->fetch_assoc();
        $check->close();

        $newStatus = ($cur['attempts'] >= MAX_ATTEMPTS) ? 'failed' : 'pending';

        $fail = $db->prepare(
            "UPDATE mail_queue SET status = ?, error_message = ? WHERE id = ?"
        );
        $fail->bind_param('ssi', $newStatus, $errMsg, $row['id']);
        $fail->execute();
        $fail->close();

        fwrite(STDERR, '[worker] Failed (' . $newStatus . ') → ' . $row['to_email'] . ': ' . $errMsg . PHP_EOL);
    }
}

echo '[worker] Done.' . PHP_EOL;
$db->close();
