<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'check_login') {
    header('Content-Type: application/json');
    echo json_encode([
        'loggedIn' => isUserLoggedIn(),
        'user_name' => currentUserName(),
    ]);
    exit;
}

function redirectWithMessage($page, $message, $type = 'success')
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type,
    ];
    header("Location: {$page}");
    exit;
}

function resolvePreviousPage(): string
{
    $fallback = 'agam.php';

    if (empty($_SERVER['HTTP_REFERER'])) {
        return $fallback;
    }

    $refererPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    if (empty($refererPath)) {
        return $fallback;
    }

    $baseName = strtolower(basename($refererPath));
    $allowedPages = ['agam.php', 'property_list.php', 'property_detail.php', 'dashboard.php', 'index.php'];

    if (in_array($baseName, $allowedPages, true)) {
        return $baseName;
    }

    return $fallback;
}

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(15) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            college_name VARCHAR(150) NOT NULL,
            gender ENUM('male', 'female') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
} catch (PDOException $e) {
    die('Database setup failed: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('agam.php', 'Invalid request.', 'error');
}

$action = $_POST['action'] ?? '';

if ($action === 'signup') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $collegeName = trim($_POST['college_name'] ?? '');
    $gender = $_POST['gender'] ?? '';

    if ($fullName === '' || $phone === '' || $email === '' || $password === '' || $collegeName === '' || !in_array($gender, ['male', 'female'], true)) {
        redirectWithMessage(resolvePreviousPage(), 'Please fill all signup fields correctly.', 'error');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage('agam.php', 'Please enter a valid email address.', 'error');
    }

    if (strlen($password) < 6) {
        redirectWithMessage('agam.php', 'Password must be at least 6 characters long.', 'error');
    }

    $checkStmt = $pdo->prepare('SELECT id, email, phone FROM users WHERE email = :email OR phone = :phone LIMIT 1');
    $checkStmt->execute([
        ':email' => $email,
        ':phone' => $phone,
    ]);

    $existingUser = $checkStmt->fetch();

    if ($existingUser) {
        if ($existingUser['email'] === $email && $existingUser['phone'] === $phone) {
            redirectWithMessage(resolvePreviousPage(), 'This email and phone number has already been registered.', 'error');
        } elseif ($existingUser['email'] === $email) {
            redirectWithMessage(resolvePreviousPage(), 'This email has already been registered.', 'error');
        } else {
            redirectWithMessage(resolvePreviousPage(), 'This phone number has already been registered.', 'error');
        }
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare(
        'INSERT INTO users (full_name, phone, email, password, college_name, gender) VALUES (:full_name, :phone, :email, :password, :college_name, :gender)'
    );

    $insertStmt->execute([
        ':full_name' => $fullName,
        ':phone' => $phone,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':college_name' => $collegeName,
        ':gender' => $gender,
    ]);

    $_SESSION['user_id'] = (int) $pdo->lastInsertId();
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_email'] = $email;

    redirectWithMessage(resolvePreviousPage(), 'Signup successful! Welcome to PG Life.', 'success');
}

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        redirectWithMessage(resolvePreviousPage(), 'Please enter both email and password.', 'error');
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        redirectWithMessage(resolvePreviousPage(), 'Invalid email or password.', 'error');
    }

    if (!password_verify($password, $user['password'])) {
        redirectWithMessage(resolvePreviousPage(), 'Wrong password.', 'error');
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];

    redirectWithMessage(resolvePreviousPage(), 'Login successful! Welcome back.', 'success');
}

redirectWithMessage(resolvePreviousPage(), 'Invalid action.', 'error');
