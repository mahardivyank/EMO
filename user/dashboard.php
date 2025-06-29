<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, c.name AS category, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$permPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/{$userSlug}/permissions.json";
$canView = [];
if (file_exists($permPath)) {
    $data = json_decode(file_get_contents($permPath), true);
    $canView = $data['can_view'] ?? [];
}

$aboutSections = $pdo->prepare("SELECT * FROM user_about_sections WHERE user_id = ? ORDER BY id ASC");
$aboutSections->execute([$id]);
$sections = $aboutSections->fetchAll();

$aboutURL = "../uploads/{$user['category_slug']}/{$userSlug}/about/";

include '../includes/user_header.php';
?>

<style>
  *{
    color: #000;
  }
  body {
    color:rgb(0, 0, 0);
    font-family: 'Poppins', sans-serif;
  }

  .glass-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    margin-bottom: 2rem;
  }

  .section-title {
    color: #d4af37;
    font-size: 2.2rem;
    font-weight: 700;
  }

  .rich-text-content p {
    line-height: 1.7;
    color: #ccc;
  }

  .avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #d4af37;
  }

  .btn-custom {
    border: 1px solid #d4af37;
    color: #d4af37;
    background: transparent;
    padding: 0.6rem 1.2rem;
    transition: all 0.3s ease;
    font-weight: 500;
  }

  .btn-custom:hover {
    background: #d4af37;
    color: #000;
  }

  .fade-in {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease;
  }

  .fade-in.visible {
    opacity: 1;
    transform: translateY(0);
  }
</style>

<div class="container mt-5">
  <div class="glass-card fade-in text-center">
    <h2 class="section-title mb-3">Welcome, <?= htmlspecialchars($user['username']) ?></h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($user['category']) ?></p>
  </div>

  <?php if (!empty($sections)): ?>
    <div class="glass-card fade-in">
      <h3 class="section-title mb-4">About Me</h3>
      <?php foreach ($sections as $sec): ?>
        <div class="glass-card fade-in">
          <h5 class="text-warning"><?= htmlspecialchars($sec['heading']) ?></h5>
          <div class="row align-items-center">
            <div class="<?= $sec['image'] ? 'col-md-8' : 'col-12' ?>">
              <div class="rich-text-content"><?= $sec['content'] ?></div>
            </div>
            <?php
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/{$userSlug}/about/" . $sec['image'];
            if ($sec['image'] && file_exists($imagePath)): ?>
              <div class="col-md-4 mt-3 mt-md-0 text-end">
                <img src="<?= $aboutURL . $sec['image'] ?>" alt="Section Image" class="img-fluid rounded" style="max-height: 200px;">
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="glass-card fade-in">
    <h3 class="section-title mb-4">You Can View These Members</h3>
    <div class="row">
      <?php
      if (empty($canView)) {
        echo "<div class='col-12'><em>No members assigned to view.</em></div>";
      } else {
        $placeholders = implode(',', array_fill(0, count($canView), '?'));
        $query = "SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id IN ($placeholders)";
        $stmt = $pdo->prepare($query);
        $stmt->execute($canView);
        $members = $stmt->fetchAll();

        foreach ($members as $m) {
          $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $m['username']));
          $imgPath = "../uploads/{$m['category_slug']}/{$slug}/profile.jpg";
          $imgUrl = file_exists($imgPath) ? $imgPath : '../assets/images/default.jpg';
          echo "
            <div class='col-sm-6 col-md-4 mb-4'>
              <a href='friend.profile.php?id={$m['id']}' class='d-flex align-items-center gap-3 p-3 glass-card fade-in text-decoration-none text-white'>
                <img src='{$imgUrl}' class='avatar' alt='{$m['username']}'>
                <div>
                  <div class='fw-bold text-golden'>" . htmlspecialchars($m['username']) . "</div>
                  <small class='text-light'>" . htmlspecialchars($m['email']) . "</small>
                </div>
              </a>
            </div>";
        }
      }
      ?>
    </div>
  </div>

  <div class="text-center mt-4 d-flex flex-wrap justify-content-center gap-3">
    <a href="profile.php" class="btn btn-custom">My Profile</a>
    <a href="upload_gallery.php" class="btn btn-custom">Upload Gallery</a>
    <a href="view_gallery.php" class="btn btn-custom">My Gallery</a>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const faders = document.querySelectorAll('.fade-in');
    const appearOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
    const appearOnScroll = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      });
    }, appearOptions);
    faders.forEach(fader => appearOnScroll.observe(fader));
  });
</script>

<?php include '../includes/user_footer.php'; ?>
