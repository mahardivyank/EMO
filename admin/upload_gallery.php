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

$msg = "";
$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$galleryPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug/gallery";
if (!is_dir($galleryPath)) mkdir($galleryPath, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = count($_FILES['gallery_images']['name']);
    for ($i = 0; $i < $count; $i++) {
        $tmp = $_FILES['gallery_images']['tmp_name'][$i];
        $name = basename($_FILES['gallery_images']['name'][$i]);
        $type = $_FILES['gallery_images']['type'][$i];

        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($type, $allowed)) {
            move_uploaded_file($tmp, "$galleryPath/$name");
        }
    }
    $msg = "✅ Gallery images uploaded successfully.";
}
?>

<?php include '../includes/admin_header.php'; ?>
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #111;
    color: #f4f4f4;
    padding: 30px;
    text-align: center;
  }
  h2 {
    color: gold;
    text-shadow: 1px 1px 6px rgba(255, 215, 0, 0.4);
    margin-bottom: 25px;
    animation: slideDown 0.6s ease-out;
  }
  form {
    background: rgba(255, 255, 255, 0.03);
    padding: 25px;
    border-radius: 12px;
    display: inline-block;
    box-shadow: 0 0 15px rgba(255, 215, 0, 0.1);
    animation: fadeZoom 0.6s ease-in-out;
  }
  input[type="file"] {
    border: 1px solid rgba(255, 215, 0, 0.2);
    background: transparent;
    padding: 10px;
    border-radius: 8px;
    color: #f4f4f4;
    cursor: pointer;
  }
  input[type="file"]::-webkit-file-upload-button {
    background: gold;
    color: black;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
  }
  input[type="file"]::-webkit-file-upload-button:hover {
    background: #fff;
    color: #000;
  }
  button {
    background: gold;
    color: black;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s, transform 0.3s;
  }
  button:hover {
    background: #fff;
    transform: scale(1.05);
  }
  .success-msg {
    margin-top: 20px;
    color: #00ffae;
    font-weight: bold;
    animation: flashIn 0.4s ease-in-out;
  }
  a.back-link {
    display: inline-block;
    margin-top: 30px;
    color: #00eaff;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
  }
  a.back-link:hover {
    color: gold;
  }
  #preview-container {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
  }
  .preview-img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid rgba(255, 215, 0, 0.2);
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.1);
    transition: transform 0.3s ease;
  }
  .preview-img:hover {
    transform: scale(1.1);
  }
  @keyframes fadeZoom {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
  }
  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes flashIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>

<h2>📄 Upload Gallery Images for <?= htmlspecialchars($user['username']) ?></h2>

<form method="POST" enctype="multipart/form-data">
  <input type="file" id="galleryInput" name="gallery_images[]" multiple accept="image/*" required><br><br>
  <button type="submit">Upload Images</button>
</form>

<div id="preview-container"></div>

<?php if ($msg): ?>
  <p class="success-msg"><?= $msg ?></p>
<?php endif; ?>

<br>
<a href="view_member_profile.php?id=<?= $user['id'] ?>" class="back-link">⬅️ Back to Profile</a>

<script>
  document.getElementById('galleryInput').addEventListener('change', function (e) {
    const container = document.getElementById('preview-container');
    container.innerHTML = '';
    Array.from(e.target.files).forEach(file => {
      const reader = new FileReader();
      reader.onload = function (event) {
        const img = document.createElement('img');
        img.src = event.target.result;
        img.classList.add('preview-img');
        container.appendChild(img);
      }
      reader.readAsDataURL(file);
    });
  });
</script>

<?php include '../includes/admin_footer.php'; ?>
