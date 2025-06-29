<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Fetch existing password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $msg = "❌ Unable to verify user.";
    } elseif ($current !== $user['password']) {
        $msg = "❌ Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $msg = "❌ New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $msg = "❌ New passwords do not match.";
    } else {
        // Update password
        $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($upd->execute([$new, $userId])) {
            $msg = "✅ Password changed successfully.";
        } else {
            $msg = "❌ Failed to update password.";
        }
    }
}
?>

<?php include '../includes/user_header.php'; ?>

<h2>🔒 Change Password</h2>

<?php if ($msg): ?>
    <p style="color: <?= strpos($msg, '✅') === 0 ? 'green' : 'red' ?>;"><?= $msg ?></p>
<?php endif; ?>

<form method="POST">
    <label>Current Password:</label><br>
    <input type="password" name="current_password" required><br><br>

    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Update Password</button>
</form>

<br>
<a href="settings.php">⬅️ Back to Settings</a>

<?php include '../includes/user_footer.php'; ?>
