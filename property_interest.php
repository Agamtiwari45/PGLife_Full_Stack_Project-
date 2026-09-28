<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

function jsonResponse($payload)
{
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS interested_users_properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            property_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_property (user_id, property_id),
            KEY property_id_idx (property_id)
        ) ENGINE=InnoDB"
    );
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Unable to initialize interest data.',
        'error' => $e->getMessage(),
    ]);
}

$action = $_REQUEST['action'] ?? 'load';

if ($action === 'check_login') {
    jsonResponse([
        'success' => true,
        'loggedIn' => isset($_SESSION['user_id']),
    ]);
}


if ($action === 'load') {
    $propertyIds = [];

    if (isset($_REQUEST['property_ids'])) {
        foreach (explode(',', $_REQUEST['property_ids']) as $propertyId) {
            $propertyId = trim($propertyId);
            if ($propertyId !== '' && ctype_digit($propertyId)) {
                $propertyIds[] = (int) $propertyId;
            }
        }
    }

    $propertyIds = array_values(array_unique($propertyIds));

    if (empty($propertyIds)) {
        jsonResponse([
            'success' => true,
            'properties' => [],
        ]);
    }

    $countStmt = $pdo->prepare('SELECT property_id, COUNT(*) AS interested_count FROM interested_users_properties WHERE property_id IN (' . implode(',', array_fill(0, count($propertyIds), '?')) . ') GROUP BY property_id');
    $countStmt->execute($propertyIds);

    $counts = [];
    while ($row = $countStmt->fetch()) {
        $counts[(int) $row['property_id']] = (int) $row['interested_count'];
    }

    $userInterestedPropertyIds = [];
    if (isset($_SESSION['user_id'])) {
        $userId = (int) $_SESSION['user_id'];
        $userParams = [':user_id' => $userId];
        $userPlaceholders = [];

        foreach ($propertyIds as $index => $propertyId) {
            $paramKey = ':pid_' . $index;
            $userPlaceholders[] = $paramKey;
            $userParams[$paramKey] = $propertyId;
        }

        $userInterestedStmt = $pdo->prepare(
            'SELECT property_id FROM interested_users_properties WHERE user_id = :user_id AND property_id IN (' . implode(',', $userPlaceholders) . ')'
        );
        $userInterestedStmt->execute($userParams);

        while ($row = $userInterestedStmt->fetch()) {
            $userInterestedPropertyIds[] = (int) $row['property_id'];
        }
    }

    $properties = [];
    foreach ($propertyIds as $propertyId) {
        $properties[] = [
            'property_id' => $propertyId,
            'interested' => in_array($propertyId, $userInterestedPropertyIds, true),
            'count' => $counts[$propertyId] ?? 0,
        ];
    }

    jsonResponse([
        'success' => true,
        'properties' => $properties,
    ]);
}

if ($action === 'toggle') {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Please login first.',
            'require_login' => true,
        ]);
    }

    $propertyId = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);

    if ($propertyId === false || $propertyId <= 0) {
        jsonResponse([
            'success' => false,
            'message' => 'Invalid property.',
        ]);
    }

    $userId = (int) $_SESSION['user_id'];

    $existingStmt = $pdo->prepare('SELECT id FROM interested_users_properties WHERE user_id = :user_id AND property_id = :property_id LIMIT 1');
    $existingStmt->execute([
        ':user_id' => $userId,
        ':property_id' => $propertyId,
    ]);

    $isInterested = false;

    if ($existingStmt->fetch()) {
        $deleteStmt = $pdo->prepare('DELETE FROM interested_users_properties WHERE user_id = :user_id AND property_id = :property_id');
        $deleteStmt->execute([
            ':user_id' => $userId,
            ':property_id' => $propertyId,
        ]);
    } else {
        $insertStmt = $pdo->prepare('INSERT INTO interested_users_properties (user_id, property_id) VALUES (:user_id, :property_id)');
        $insertStmt->execute([
            ':user_id' => $userId,
            ':property_id' => $propertyId,
        ]);
        $isInterested = true;
    }

    $countStmt = $pdo->prepare('SELECT COUNT(*) AS interested_count FROM interested_users_properties WHERE property_id = :property_id');
    $countStmt->execute([':property_id' => $propertyId]);
    $countRow = $countStmt->fetch();

    jsonResponse([
        'success' => true,
        'property_id' => $propertyId,
        'interested' => $isInterested,
        'count' => (int) ($countRow['interested_count'] ?? 0),
    ]);
}

jsonResponse([
    'success' => false,
    'message' => 'Invalid request.',
]);
