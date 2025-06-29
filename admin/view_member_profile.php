<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid member ID");

// Fetch user and category info
$stmt = $pdo->prepare("SELECT u.*, c.name AS category, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) die("Member not found");

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$folderPath = "../uploads/{$user['category_slug']}/$userSlug";
$galleryPath = "$folderPath/gallery";
$galleryImages = is_dir($galleryPath) ? array_diff(scandir($galleryPath), ['.', '..']) : [];

$aboutFolder = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/{$userSlug}/about/";
$aboutURL = "/EMO/uploads/{$user['category_slug']}/{$userSlug}/about/";
?>

<?php include '../includes/admin_header.php'; ?>
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #111;
    color: #f4f4f4;
    padding: 20px;
    line-height: 1.6;
  }
  h2, h3, h4 {
    color: gold;
    text-shadow: 1px 1px 5px rgba(255, 215, 0, 0.3);
    animation: fadeSlideDown 0.5s ease-out;
  }
  a {
    color: #00eaff;
    text-decoration: none;
    transition: color 0.3s;
  }
  a:hover {
    color: gold;
  }
  img {
    transition: transform 0.4s ease, box-shadow 0.4s ease;
  }
  img:hover {
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
  }
  .section-box {
    background: rgba(255, 255, 255, 0.03);
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255, 215, 0, 0.15);
    animation: fadeIn 0.7s ease;
  }
  .gallery-img, .perm-img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    margin: 5px;
    border: 2px solid rgba(255, 215, 0, 0.2);
  }
  .perm-img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
  }
  .perm-block {
    display: inline-block;
    text-align: center;
    margin: 10px;
    animation: pulseFadeIn 0.8s ease;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeSlideDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes pulseFadeIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".gallery-img").forEach(img => {
      img.addEventListener("mouseenter", () => {
        img.style.boxShadow = "0 0 15px gold";
      });
      img.addEventListener("mouseleave", () => {
        img.style.boxShadow = "none";
      });
    });
  });
</script>

<h2>🔍 View Member Profile</h2>

<p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
<p><strong>Category:</strong> <?= htmlspecialchars($user['category']) ?></p>
<p><strong>View Limit:</strong> <?= $user['view_limit'] ?></p>

<p><strong>Profile Image:</strong></p>
<?php
$profileImage = "$folderPath/profile.jpg";
if (file_exists($profileImage)) {
    echo "<img src='$profileImage' width='120' height='120' style='border-radius: 10px;'>";
} else {
    echo "<em>No profile image.</em>";
}
?>

<hr>
<h3>🖼️ Gallery Images</h3>
<?php if (!empty($galleryImages)): ?>
    <?php foreach ($galleryImages as $img): ?>
        <img src="<?= $galleryPath . '/' . $img ?>" width="100" height="100" style="margin: 5px; border-radius: 8px; object-fit:cover;">
    <?php endforeach; ?>
<?php else: ?>
    <p><em>No gallery images available.</em></p>
<?php endif; ?>

<br><br>
<a href="upload_gallery.php?id=<?= $user['id'] ?>">📤 Upload More Images</a>
&nbsp;&nbsp;
<a href="view_gallery.php?id=<?= $user['id'] ?>">🗂️ Manage Gallery</a>

<hr>
<h3>📄 About Member Sections</h3>
<?php
$sections = $pdo->prepare("SELECT * FROM user_about_sections WHERE user_id = ? ORDER BY id ASC");
$sections->execute([$user['id']]);
$aboutData = $sections->fetchAll();

if ($aboutData):
    foreach ($aboutData as $sec):
        $imagePath = $aboutFolder . $sec['image'];
        $imageURL = $aboutURL . $sec['image'];
?>
    <div style="border:1px solid #ccc; padding:15px; margin-bottom:20px; border-radius:8px;">
        <h4 style="margin-bottom:10px;"><?= htmlspecialchars($sec['heading']) ?></h4>
        <div><?= $sec['content'] ?></div>
        <?php if (!empty($sec['image']) && file_exists($imagePath)): ?>
            <br><img src="<?= $imageURL ?>" width="150" style="margin-top:10px; border-radius:8px;">
        <?php endif; ?>
    </div>
<?php
    endforeach;
else:
    echo "<p><em>No about sections added.</em></p>";
endif;
?>

<hr>
<h3>🔐 Viewing Permissions</h3>
<?php
$permPath = "$folderPath/permissions.json";
$permissions = file_exists($permPath) ? json_decode(file_get_contents($permPath), true) : ['can_view' => [], 'viewed_by' => []];

function displayPermissionList($ids, $pdo) {
    if (empty($ids)) {
        echo "<p><em>None</em></p>";
        return;
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT u.username, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id IN ($placeholders)");
    $stmt->execute($ids);
    $users = $stmt->fetchAll();

    foreach ($users as $u):
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $u['username']));
        $imgPath = "../uploads/{$u['category_slug']}/$slug/profile.jpg";
        $imgURL = file_exists($imgPath) ? $imgPath : '../assets/images/default.jpg';
?>
        <div style="display:inline-block; text-align:center; margin:10px;">
            <img src="<?= $imgURL ?>" width="60" height="60" style="border-radius:50%; border:1px solid #ccc;"><br>
            <small><?= htmlspecialchars($u['username']) ?></small>
        </div>
<?php
    endforeach;
}
?>

<p><strong>Can View:</strong></p>
<?php displayPermissionList($permissions['can_view'], $pdo); ?>

<br>

<p><strong>Can Be Viewed By:</strong></p>
<?php displayPermissionList($permissions['viewed_by'], $pdo); ?>

<br><br>
<a href="view_members.php">⬅️ Back to Members</a>

<?php include '../includes/admin_footer.php'; ?>
