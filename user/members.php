<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Fetch all other users
$stmt = $pdo->prepare("SELECT u.id, u.username, u.email, c.slug AS category_slug 
                       FROM users u 
                       JOIN categories c ON u.category_id = c.id 
                       WHERE u.id != ? AND u.is_admin = 0 
                       ORDER BY u.username ASC");
$stmt->execute([$currentUserId]);
$users = $stmt->fetchAll();

include '../includes/user_header.php';
?>

<h2>👥 Explore Other Members</h2>

<?php if ($users): ?>
    <div style="display: flex; flex-wrap: wrap;">
        <?php foreach ($users as $user): 
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
            $imgPath = "../uploads/{$user['category_slug']}/{$slug}/profile.jpg";
        ?>
            <div style="width: 200px; margin: 10px; padding: 10px; border: 1px solid #ccc; text-align: center;">
                <img src="<?= file_exists($imgPath) ? $imgPath : '../assets/images/default.png' ?>" width="100" height="100"><br>
                <strong><?= htmlspecialchars($user['username']) ?></strong><br>
                <a href="friend.profile.php?id=<?= $user['id'] ?>">🔍 View Profile</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No other members found.</p>
<?php endif; ?>

<?php include '../includes/user_footer.php'; ?>
