<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode([
        'success' => false,
        'require_login' => true,
        'message' => 'Please login first to save this property.',
    ]);
    exit;
}

$propertyId = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
if ($propertyId === false || $propertyId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid property selected.',
    ]);
    exit;
}

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS interested_users_properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            property_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_property (user_id, property_id),
            KEY property_id_idx (property_id)
        ) ENGINE=InnoDB
    ");

    $userId = (int) $_SESSION['user_id'];

    $existingStmt = $pdo->prepare(
        'SELECT id FROM interested_users_properties WHERE user_id = :user_id AND property_id = :property_id LIMIT 1'
    );
    $existingStmt->execute([
        ':user_id' => $userId,
        ':property_id' => $propertyId,
    ]);

    $isInterested = !$existingStmt->fetch();

    if ($isInterested) {
        $insertStmt = $pdo->prepare(
            'INSERT INTO interested_users_properties (user_id, property_id) VALUES (:user_id, :property_id)'
        );
        $insertStmt->execute([
            ':user_id' => $userId,
            ':property_id' => $propertyId,
        ]);
    } else {
        $deleteStmt = $pdo->prepare(
            'DELETE FROM interested_users_properties WHERE user_id = :user_id AND property_id = :property_id'
        );
        $deleteStmt->execute([
            ':user_id' => $userId,
            ':property_id' => $propertyId,
        ]);
    }

    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) AS interested_count FROM interested_users_properties WHERE property_id = :property_id'
    );
    $countStmt->execute([':property_id' => $propertyId]);
    $countRow = $countStmt->fetch();

    echo json_encode([
        'success' => true,
        'property_id' => $propertyId,
        'interested' => $isInterested,
        'count' => (int) ($countRow['interested_count'] ?? 0),
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to update favorite status.',
        'error' => $e->getMessage(),
    ]);
}
