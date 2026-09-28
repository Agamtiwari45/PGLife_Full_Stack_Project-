<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isUserLoggedIn()) {
        header('Location: agam.php');
        exit;
    }
}

function currentUserId(): ?int
{
    if (!isUserLoggedIn()) {
        return null;
    }

    return (int) $_SESSION['user_id'];
}

function currentUserName(): string
{
    return $_SESSION['user_name'] ?? 'User';
}
