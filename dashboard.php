<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

if (!isUserLoggedIn()) {
    header('Location: agam.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, full_name, phone, email, college_name, gender FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: agam.php');
    exit;
}

$userName = $user['full_name'] ?? $_SESSION['user_name'] ?? 'User';
$formattedGender = ucfirst($user['gender'] ?? '');

$knownProperties = [
    1 => [
        'name' => 'Navkar Paying Guest',
        'address' => '44, Juhu Scheme, Juhu, Mumbai, Maharashtra 400058',
        'rent' => 'Rs 9,500/-',
        'city' => 'Mumbai',
        'gender' => 'Male',
    ],
    2 => [
        'name' => 'Ganpati Paying Guest',
        'address' => 'Police Beat, Sainath Complex, Borivali East, Mumbai - 400066',
        'rent' => 'Rs 8,500/-',
        'city' => 'Mumbai',
        'gender' => 'Unisex',
    ],
    3 => [
        'name' => 'PG for Girls Borivali West',
        'address' => 'Plot no.258/D4, Gorai no.2, Borivali West, Mumbai, Maharashtra 400092',
        'rent' => 'Rs 8,000/-',
        'city' => 'Mumbai',
        'gender' => 'Female',
    ],
];

$interestedStmt = $pdo->prepare('SELECT property_id FROM interested_users_properties WHERE user_id = :user_id');
$interestedStmt->execute([':user_id' => $userId]);
$interestedPropertyIds = array_map('intval', $interestedStmt->fetchAll(PDO::FETCH_COLUMN, 0));

$interestedProperties = [];
foreach ($interestedPropertyIds as $propertyId) {
    $propertyInfo = $knownProperties[$propertyId] ?? null;
    if (!$propertyInfo) {
        continue;
    }

    $interestedProperties[] = [
        'property_id' => $propertyId,
        'name' => $propertyInfo['name'],
        'address' => $propertyInfo['address'],
        'rent' => $propertyInfo['rent'],
        'city' => $propertyInfo['city'],
        'gender' => $propertyInfo['gender'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PG Life</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --bg: #f3f4f6;
            --card: #ffffff;
            --card-alt: #f8fafc;
            --border: #e5e7eb;
            --text: #1f2937;
            --muted: #6b7280;
            --heading: #111827;
            --brand: #111827;
            --brand-soft: #1f2937;
            --accent: #0f172a;
            --accent-light: #e2e8f0;
            --success: #2563eb;
            --shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            --shadow-hover: 0 12px 30px rgba(15, 23, 42, 0.09);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .dashboard-header {
            background: #111827;
            color: #ffffff;
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px 0 32px;
            box-shadow: 0 2px 12px rgba(17, 24, 39, 0.15);
        }

        .logo-link {
            display: inline-flex;
            align-items: center;
            height: 100%;
        }

        .logo-image {
            height: 38px;
            width: auto;
            display: block;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
        }

        .dashboard-nav {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 14px;
            font-weight: 500;
        }

        .dashboard-nav a {
            color: rgba(255, 255, 255, 0.88);
            transition: color 0.2s ease, opacity 0.2s ease;
        }

        .dashboard-nav a:hover {
            color: #ffffff;
        }

        .dashboard-nav .active {
            color: #dbeafe;
            font-weight: 700;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 10px;
        }

        .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            font-weight: 600;
            opacity: 0.96;
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-icon {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            font-size: 14px;
        }

        .logout-link {
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .logout-link:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }

        .top-spacer {
            height: 34px;
            background: var(--bg);
        }

        .dashboard-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 12px 28px 80px;
        }

        .profile-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 28px 28px 22px;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .profile-card:hover {
            box-shadow: var(--shadow-hover);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 32px;
            padding: 6px 8px;
        }

        .profile-avatar-wrap {
            flex: 0 0 160px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-avatar {
            width: 132px;
            height: 132px;
            border-radius: 32px;
            background: linear-gradient(135deg, #dbeafe, #e2e8f0);
            border: 1px solid rgba(148, 163, 184, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #1f2937;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.5);
        }

        .profile-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .profile-info {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 10px;
            min-width: 0;
        }

        .profile-name {
            margin: 0;
            font-size: clamp(1.8rem, 2vw, 2.4rem);
            font-weight: 800;
            color: var(--heading);
            letter-spacing: -0.03em;
        }

        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 12px 20px;
            margin-top: 8px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: var(--card-alt);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
        }

        .info-item-icon {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e2e8f0, #f1f5f9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--brand-soft);
            flex-shrink: 0;
        }

        .info-item strong {
            display: block;
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-item span {
            font-size: 14px;
            color: var(--text);
            font-weight: 600;
            line-height: 1.4;
        }

        .profile-actions {
            align-self: flex-start;
            padding-top: 8px;
        }

        .edit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-soft));
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(17, 24, 39, 0.14);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .edit-button:hover {
            transform: translateY(-1px) scale(1.01);
            box-shadow: 0 12px 25px rgba(17, 24, 39, 0.18);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 42px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .section-title {
            margin: 0;
            font-size: clamp(1.6rem, 2vw, 2.2rem);
            font-weight: 800;
            color: var(--heading);
            letter-spacing: -0.03em;
        }

        .interest-box {
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            min-height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .empty-state {
            max-width: 420px;
            text-align: center;
            color: var(--muted);
        }

        .empty-state-icon {
            width: 76px;
            height: 76px;
            border-radius: 22px;
            background: linear-gradient(135deg, #e2e8f0, #f8fafc);
            color: var(--brand-soft);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 18px;
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .empty-state h3 {
            margin: 0 0 10px;
            font-size: 1.7rem;
            color: var(--heading);
            font-weight: 700;
        }

        .empty-state p {
            margin: 0 0 20px;
            font-size: 0.98rem;
            line-height: 1.6;
            color: var(--muted);
        }

        .empty-state .browse-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid var(--border);
            color: var(--brand-soft);
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .empty-state .browse-button:hover {
            background: var(--card-alt);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
            transform: translateY(-1px);
        }

        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 8px;
        }

        .favorite-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .favorite-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .favorite-image {
            height: 180px;
            background: linear-gradient(135deg, #e2e8f0, #f8fafc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #1f2937;
        }

        .favorite-content {
            padding: 18px 18px 16px;
        }

        .favorite-name {
            margin: 0 0 8px;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--heading);
        }

        .favorite-address {
            margin: 0 0 12px;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .favorite-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 12px;
        }

        .favorite-rent {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--heading);
        }

        .favorite-gender {
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .favorite-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 9px 14px;
            background: #111827;
            color: #ffffff;
            font-weight: 600;
            margin-top: 12px;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 14px 16px;
                flex-wrap: wrap;
                justify-content: center;
                gap: 12px;
            }

            .dashboard-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .user-menu {
                margin-left: 0;
            }

            .dashboard-wrap {
                padding: 18px 16px 60px;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }

            .profile-body {
                flex-direction: column;
                align-items: center;
            }

            .profile-actions {
                align-self: center;
            }

            .profile-info-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                margin-top: 28px;
            }
        }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <a href="agam.php" class="logo-link" aria-label="PG Life home">
            <img src="Image/img/logo.png" alt="PG Life logo" class="logo-image">
        </a>

        <nav class="dashboard-nav" aria-label="Main navigation">
            <a href="agam.php">Home</a>
            <a href="property_list.php?city=mumbai">Properties / PGs</a>
            <a href="dashboard.php" class="active">Dashboard</a>

            <div class="user-menu">
                <span class="user-pill">
                    <span class="user-icon">👤</span>
                    <?php echo htmlspecialchars($userName); ?>
                </span>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>
    </header>

    <div class="top-spacer"></div>

    <main class="dashboard-wrap">
        <section class="profile-card" aria-label="User profile details">
            <div class="profile-header">
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar" aria-hidden="true">👤</div>
                </div>

                <div class="profile-body">
                    <div class="profile-info">
                        <h1 class="profile-name"><?php echo htmlspecialchars($userName); ?></h1>

                        <div class="profile-info-grid">
                            <div class="info-item">
                                <div class="info-item-icon">✉</div>
                                <div>
                                    <strong>Email</strong>
                                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-item-icon">☎</div>
                                <div>
                                    <strong>Phone</strong>
                                    <span><?php echo htmlspecialchars($user['phone']); ?></span>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-item-icon">🎓</div>
                                <div>
                                    <strong>College / Org</strong>
                                    <span><?php echo htmlspecialchars($user['college_name']); ?></span>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-item-icon">⚧</div>
                                <div>
                                    <strong>Gender</strong>
                                    <span><?php echo htmlspecialchars($formattedGender); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="agam.php" class="edit-button">Edit Profile</a>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-header">
            <h2 class="section-title">My Interested Properties</h2>
        </div>

        <div class="interest-box">
            <?php if (empty($interestedProperties)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🏡</div>
                    <h3>No interested properties yet.</h3>
                    <p>Start exploring PGs in your preferred city and save the ones you love.</p>
                    <a href="property_list.php?city=mumbai" class="browse-button">Browse Properties</a>
                </div>
            <?php else: ?>
                <div class="favorites-grid">
                    <?php foreach ($interestedProperties as $property): ?>
                        <article class="favorite-card">
                            <div class="favorite-image" aria-hidden="true">🏠</div>
                            <div class="favorite-content">
                                <h3 class="favorite-name"><?php echo htmlspecialchars($property['name']); ?></h3>
                                <p class="favorite-address"><?php echo htmlspecialchars($property['address']); ?></p>
                                <div class="favorite-meta">
                                    <span class="favorite-rent"><?php echo htmlspecialchars($property['rent']); ?></span>
                                    <span class="favorite-gender"><?php echo htmlspecialchars($property['gender']); ?></span>
                                </div>
                                <a class="favorite-link" href="property_detail.php?city=<?php echo urlencode(strtolower($property['city'])); ?>">View PG</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
