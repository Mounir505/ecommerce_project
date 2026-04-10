<?php
function log_auth_event(PDO $pdo, string $email, string $status, bool $is_admin = false): void
{
    $stmt = $pdo->prepare("
        INSERT INTO auth_events (email, ip_address, user_agent, status, is_admin)
        VALUES (:email, :ip, :agent, :status, :admin)
    ");
    $stmt->execute([
        ':email'  => $email,
        ':ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ':agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ':status' => $status,
        ':admin'  => $is_admin ? 1 : 0
    ]);
}
