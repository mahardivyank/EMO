<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$friendId = $_GET['id'] ?? null;
if (!$friendId) die("Invalid member ID");

// Fetch friend info
$stmt = $pdo->prepare("SELECT u.*, c.name AS category, c.slug AS category_slug 
                       FROM users u 
                       JOIN categories c ON u.category_id = c.id 
                       WHERE u.id = ?");
$stmt->execute([$friendId]);
$friend = $stmt->fetch();
if (!$friend) die("Member not found.");

$friendSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $friend['username']));
$profileImgPath = "../uploads/{$friend['category_slug']}/$friendSlug/profile.jpg";
$profileImgUrl = file_exists($profileImgPath) ? $profileImgPath : "../assets/images/default.jpg";

// Gallery Logic
$galleryUrl = "../uploads/{$friend['category_slug']}/$friendSlug/gallery";
$galleryDir = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$friend['category_slug']}/$friendSlug/gallery";
$images = is_dir($galleryDir) ? array_diff(scandir($galleryDir), ['.', '..']) : [];

// About Sections
$aboutStmt = $pdo->prepare("SELECT * FROM user_about_sections WHERE user_id = ? ORDER BY id ASC");
$aboutStmt->execute([$friendId]);
$aboutSections = $aboutStmt->fetchAll();

$aboutURL = "../uploads/{$friend['category_slug']}/{$friendSlug}/about/";

include '../includes/user_header.php';
?>

<div class="container mt-4">
  <div class="card shadow p-4 fade-in">
    <div class="text-center mb-4">
      <img src="<?= $profileImgUrl ?>" width="100" height="100" style="border-radius: 50%; border: 2px solid #ddd;"><br>
      <h3 class="mt-3"><?= htmlspecialchars($friend['username']) ?></h3>
      <p><strong>Email:</strong> <small><?= htmlspecialchars($friend['email']) ?></small></p>
      <p><strong>Category:</strong> <?= htmlspecialchars($friend['category']) ?></p>
    </div>

    <!-- ABOUT MEMBER SECTIONS -->
    <?php if (!empty($aboutSections)): ?>
      <div class="mb-5">
        <h4 class="title-xl mb-3">📖 About <?= htmlspecialchars($friend['username']) ?></h4>
        <?php foreach ($aboutSections as $sec): ?>
          <div class="card mb-4 p-3 shadow-sm fade-in">
            <h5 class="mb-2 text-primary"><?= htmlspecialchars($sec['heading']) ?></h5>
            <div class="row align-items-center">
              <div class="<?= $sec['image'] ? 'col-md-8' : 'col-12' ?>">
                <!-- Render rich TinyMCE content -->
                <div class="rich-text-content"><?= $sec['content'] ?></div>
              </div>
              <?php
              $imgPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$friend['category_slug']}/{$friendSlug}/about/" . $sec['image'];
              if ($sec['image'] && file_exists($imgPath)): ?>
                <div class="col-md-4 text-end">
                  <img src="<?= $aboutURL . $sec['image'] ?>" alt="Section Image" class="img-fluid rounded shadow" style="max-height: 200px;">
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- GALLERY -->
    <hr>
    <h4>📸 Gallery</h4>
    <?php if (!empty($images)): ?>
      <div class="d-flex flex-wrap gap-3 mt-3">
        <?php foreach ($images as $img): ?>
          <img src="<?= $galleryUrl . '/' . $img ?>" width="100" height="100" style="border:1px solid #ccc; border-radius: 10px; object-fit:cover;">
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="mt-2"><em>No gallery images available.</em></p>
    <?php endif; ?>

    <div class="mt-4 text-center">
      <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
    </div>
  </div>
</div>

<?php include '../includes/user_footer.php'; ?>
        