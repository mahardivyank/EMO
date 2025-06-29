<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_GET['user_id'] ?? 0);
if (!$user_id) die("Invalid user.");

// Get user info
$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) die("User not found.");

$basePath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/{$user['username']}/about/";
if (!is_dir($basePath)) mkdir($basePath, 0755, true);

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("SELECT image FROM user_about_sections WHERE id = ? AND user_id = ?");
    $stmt->execute([$delId, $user_id]);
    $section = $stmt->fetch();
    if ($section) {
        if ($section['image']) @unlink($basePath . $section['image']);
        $pdo->prepare("DELETE FROM user_about_sections WHERE id = ?")->execute([$delId]);
        header("Location: about_member.php?user_id=$user_id");
        exit;
    }
}

// Handle updates and inserts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update existing sections
    foreach ($_POST['existing_heading'] as $i => $heading) {
        $content = $_POST['existing_content'][$i];
        $id = intval($_POST['section_id'][$i]);
        $imageName = $_POST['existing_image'][$i];

        // New image uploaded
        if (!empty($_FILES['existing_image_upload']['name'][$i]) && $_FILES['existing_image_upload']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['existing_image_upload']['name'][$i], PATHINFO_EXTENSION);
            $imageName = "section_" . time() . "_upd_$i." . $ext;
            move_uploaded_file($_FILES['existing_image_upload']['tmp_name'][$i], $basePath . $imageName);
        }

        $pdo->prepare("UPDATE user_about_sections SET heading = ?, content = ?, image = ? WHERE id = ? AND user_id = ?")
            ->execute([$heading, $content, $imageName, $id, $user_id]);
    }

    // Insert new sections
    if (!empty($_POST['heading'])) {
        foreach ($_POST['heading'] as $i => $heading) {
            $content = $_POST['content'][$i];
            $imageName = null;
            if (!empty($_FILES['image']['name'][$i]) && $_FILES['image']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'][$i], PATHINFO_EXTENSION);
                $imageName = "section_" . time() . "_new_$i." . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'][$i], $basePath . $imageName);
            }
            $pdo->prepare("INSERT INTO user_about_sections (user_id, heading, content, image) VALUES (?, ?, ?, ?)")
                ->execute([$user_id, $heading, $content, $imageName]);
        }
    }
    $success = "✅ Sections saved successfully.";
}

$sections = $pdo->prepare("SELECT * FROM user_about_sections WHERE user_id = ? ORDER BY id ASC");
$sections->execute([$user_id]);
$sections = $sections->fetchAll();
?>

<?php include '../includes/admin_header.php'; ?>
<style>
  body {
    background: #101010;
    font-family: 'Segoe UI', sans-serif;
    color: #f4f4f4;
    padding: 40px;
  }
  h2, h3 {
    color: gold;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
    animation: fadeIn 0.6s ease;
  }
  form {
    max-width: 900px;
    margin: auto;
    padding: 25px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.1);
    animation: slideUp 0.8s ease;
  }
  input[type="text"], textarea, input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    margin-bottom: 20px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 10px;
    color: white;
    outline: none;
    transition: all 0.3s ease;
  }
  input[type="text"]:focus, textarea:focus {
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 6px gold;
  }
  .section-block {
    margin-bottom: 30px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
  }
  .section-block:hover {
    transform: scale(1.01);
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
  }
  button {
    background: gold;
    border: none;
    color: black;
    padding: 10px 20px;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.3s ease, transform 0.2s;
  }
  button:hover {
    background: #ffd700;
    transform: scale(1.05);
  }
  a {
    color: #00eaff;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
    margin-top: 20px;
  }
  a:hover {
    color: gold;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<script src="https://cdn.tiny.cloud/1/kp7lkwef6fn3opi086mm6b7ep7477lpshgt24fl1healndc9/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
function initEditors() {
  tinymce.init({
    selector: 'textarea.tinymce',
    height: 250,
    plugins: 'lists link image code preview',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code preview',
    menubar: false,
    skin: 'oxide-dark',
    content_css: 'dark'
  });
}

function addSection() {
  const container = document.getElementById('newSectionContainer');
  const div = document.createElement('div');
  div.className = 'section-block';
  div.innerHTML = `
    <input type="text" name="heading[]" placeholder="New Section Title" required><br>
    <textarea name="content[]" class="tinymce" required></textarea><br>
    <input type="file" name="image[]" accept="image/*"><br><br><hr>
  `;
  container.appendChild(div);
  initEditors();
}

document.addEventListener('DOMContentLoaded', initEditors);
</script>

<h2>📝 Edit About Sections for: <?= htmlspecialchars($user['username']) ?></h2>
<img src="/EMO/uploads/<?= $user['category_slug'] ?>/<?= $user['username'] ?>/profile.jpg" width="100" style="border-radius:50%; box-shadow: 0 0 15px gold;"><br><br>

<?php if (!empty($success)): ?><p style="color: #00ff88; font-weight: bold;">✅ <?= $success ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <h3>📌 Existing Sections</h3>
  <?php foreach ($sections as $i => $sec): ?>
    <div class="section-block">
      <input type="hidden" name="section_id[]" value="<?= $sec['id'] ?>">
      <input type="hidden" name="existing_image[]" value="<?= $sec['image'] ?>">

      <input type="text" name="existing_heading[]" value="<?= htmlspecialchars($sec['heading']) ?>" required><br>
      <textarea name="existing_content[]" class="tinymce" required><?= htmlspecialchars($sec['content']) ?></textarea><br>
      <?php if ($sec['image']): ?>
        <img src="/EMO/uploads/<?= $user['category_slug'] ?>/<?= $user['username'] ?>/about/<?= $sec['image'] ?>" width="100" style="border-radius: 8px;"><br>
      <?php endif; ?>
      <label>Replace Image (optional):</label>
      <input type="file" name="existing_image_upload[]" accept="image/*"><br>
      <a href="?user_id=<?= $user_id ?>&delete_id=<?= $sec['id'] ?>" onclick="return confirm('Delete this section?')">❌ Delete Section</a>
    </div>
  <?php endforeach; ?>

  <h3>➕ Add New Sections</h3>
  <div id="newSectionContainer"></div>
  <button type="button" onclick="addSection()">➕ Add Another Section</button><br><br>

  <button type="submit">💾 Save All Changes</button>
</form>

<a href="dashboard.php">⬅️ Back to Dashboard</a>
<?php include '../includes/admin_footer.php'; ?>
