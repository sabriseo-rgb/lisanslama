<?php

function make_license($type = 'FULL')
{
    $token = strtoupper(bin2hex(random_bytes(8)));
    return ($type === 'FULL' ? 'FULL-' : 'LIMIT-') . $token;
}

function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function set_flash(string $key, string $message, string $type = 'info'): void
{
    ensure_session_started();
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type,
    ];
}

function get_flash(string $key): ?array
{
    ensure_session_started();
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $flash = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $flash;
}

function get_all_flashes(): array
{
    ensure_session_started();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flashes;
}
