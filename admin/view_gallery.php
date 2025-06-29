<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid member ID");

$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) die("Member not found");

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$galleryPath = "../uploads/{$user['category_slug']}/$userSlug/gallery";
$fullPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug/gallery";
$galleryImages = is_dir($fullPath) ? array_diff(scandir($fullPath), ['.', '..']) : [];
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

<h2>🖼️ Gallery Images of <?= htmlspecialchars($user['username']) ?></h2>

<?php if (!empty($galleryImages)): ?>
    <?php foreach ($galleryImages as $img): ?>
        <div style="display:inline-block; text-align:center; margin:10px;">
            <img src="<?= $galleryPath . '/' . $img ?>" width="100" height="100"><br>
            <a href="delete_gallery_image.php?id=<?= $user['id'] ?>&img=<?= urlencode($img) ?>" onclick="return confirm('Delete this image?');">❌ Delete</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p><em>No gallery images available.</em></p>
<?php endif; ?>

<br><br>
<a href="view_member_profile.php?id=<?= $user['id'] ?>">⬅️ Back to Profile</a>

<?php include '../includes/admin_footer.php'; ?>
