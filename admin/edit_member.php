<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid member ID");

// Fetch member
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) die("User not found.");

$msg = "";

// Get category & user slug
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$catStmt = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
$catStmt->execute([$user['category_id']]);
$category_slug = $catStmt->fetchColumn();
$user_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$aboutFolder = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$category_slug}/{$user_slug}/about/";
$aboutURL = "/EMO/uploads/{$category_slug}/{$user_slug}/about/";

// ==== Handle Section Deletion ====
if (isset($_GET['delete_section'])) {
    $sid = intval($_GET['delete_section']);
    $img = $pdo->prepare("SELECT image FROM user_about_sections WHERE id = ? AND user_id = ?");
    $img->execute([$sid, $user['id']]);
    $section = $img->fetch();

    if ($section && $section['image'] && file_exists($aboutFolder . $section['image'])) {
        unlink($aboutFolder . $section['image']);
    }

    $pdo->prepare("DELETE FROM user_about_sections WHERE id = ? AND user_id = ?")->execute([$sid, $user['id']]);
    header("Location: edit_member.php?id=" . $user['id']);
    exit;
}

// ==== Handle Section Update ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_section_id'])) {
    $sid = intval($_POST['update_section_id']);
    $heading = trim($_POST['section_heading']);
    $content = trim($_POST['section_content']);
    $imageName = null;

    if (!is_dir($aboutFolder)) mkdir($aboutFolder, 0755, true);

    if (!empty($_FILES['section_image']['name']) && $_FILES['section_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['section_image']['name'], PATHINFO_EXTENSION);
        $imageName = "section_" . time() . "_{$sid}." . $ext;
        move_uploaded_file($_FILES['section_image']['tmp_name'], $aboutFolder . $imageName);
        $stmt = $pdo->prepare("UPDATE user_about_sections SET heading=?, content=?, image=? WHERE id=? AND user_id=?");
        $stmt->execute([$heading, $content, $imageName, $sid, $user['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE user_about_sections SET heading=?, content=? WHERE id=? AND user_id=?");
        $stmt->execute([$heading, $content, $sid, $user['id']]);
    }

    header("Location: edit_member.php?id=" . $user['id']);
    exit;
}

// ==== Handle Member Info Update ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_section_id'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $category_id = intval($_POST['category_id']);
    $can_view = $_POST['can_view'] ?? [];
    $viewed_by = $_POST['viewed_by'] ?? [];

    $catStmt->execute([$category_id]);
    $new_cat_slug = $catStmt->fetchColumn();
    $new_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $username));

    $oldPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$category_slug}/{$user_slug}";
    $newPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$new_cat_slug}/{$new_slug}";
    if ($oldPath !== $newPath && is_dir($oldPath)) {
        rename($oldPath, $newPath);
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $newPath . "/profile.jpg");
    }

    $permissions = ['can_view' => array_map('intval', $can_view), 'viewed_by' => array_map('intval', $viewed_by)];
    file_put_contents($newPath . "/permissions.json", json_encode($permissions));

    $update = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, category_id = ? WHERE id = ?");
    if ($update->execute([$username, $email, $password, $category_id, $id])) {
        header("Location: view_members.php");
        exit;
    } else {
        $msg = "❌ Failed to update member.";
    }
}
?>

<?php include '../includes/admin_header.php'; ?>

<!-- TinyMCE CDN -->
<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/kp7lkwef6fn3opi086mm6b7ep7477lpshgt24fl1healndc9/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
  tinymce.init({
    selector: 'textarea.rich-editor',
    plugins: 'lists link image table code',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table code',
    menubar: false,
    height: 250
  });
</script>

<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #121212;
    color: #f4f4f4;
    padding: 20px;
  }
  h2, h3 {
    color: gold;
    text-shadow: 1px 1px 5px rgba(255, 215, 0, 0.3);
    animation: fadeSlideDown 0.7s ease-out;
  }
  form {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.1);
    margin-bottom: 20px;
    animation: fadeIn 1s ease;
  }
  input, select, textarea, button {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: none;
    outline: none;
    background: #1e1e1e;
    color: #fff;
    transition: box-shadow 0.3s ease;
  }
  input:focus, select:focus, textarea:focus {
    box-shadow: 0 0 5px gold;
  }
  button {
    background: linear-gradient(to right, gold, orange);
    color: #000;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s ease;
  }
  button:hover {
    transform: scale(1.05);
  }
  .member-labels label {
    display: inline-block;
    width: 140px;
    text-align: center;
    margin: 10px;
    animation: fadeZoom 0.6s ease-in-out;
  }
  .member-labels img {
    border-radius: 50%;
    border: 2px solid rgba(255, 215, 0, 0.4);
    width: 60px;
    height: 60px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .member-labels img:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 0 15px gold;
  }
  .member-labels input[type="checkbox"] {
    margin-top: 8px;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeSlideDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeZoom {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("form").forEach(form => {
      form.addEventListener("mouseenter", () => {
        form.style.boxShadow = "0 0 25px rgba(255, 215, 0, 0.3)";
      });
      form.addEventListener("mouseleave", () => {
        form.style.boxShadow = "0 0 20px rgba(255, 215, 0, 0.1)";
      });
    });
  });
</script>


<h2>✏️ Edit Member</h2>

<form method="POST" enctype="multipart/form-data">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

    <label>Password:</label><br>
    <input type="text" name="password" value="<?= htmlspecialchars($user['password']) ?>" required><br><br>

    <label>Category:</label><br>
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $user['category_id'] ? "selected" : "" ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Replace Profile Image:</label><br>
    <input type="file" name="profile_image" accept="image/*"><br><br>

    <!-- Permissions -->
    <?php
    $allUsers = $pdo->query("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id != $id ORDER BY u.username ASC")->fetchAll();
    $permFile = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$category_slug}/{$user_slug}/permissions.json";
    $permissions = file_exists($permFile) ? json_decode(file_get_contents($permFile), true) : ['can_view' => [], 'viewed_by' => []];
    ?>

    <label><strong>Can View These Members:</strong></label><br>
    <div style="max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px;">
        <?php foreach ($allUsers as $u): 
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $u['username']));
            $imgPath = "../uploads/{$u['category_slug']}/{$slug}/profile.jpg";
            $imgUrl = file_exists($imgPath) ? $imgPath : '../assets/images/default.jpg';
            $checked = in_array($u['id'], $permissions['can_view']) ? "checked" : "";
        ?>
            <label style="display:inline-block; width:140px; text-align:center;">
                <img src="<?= $imgUrl ?>" width="60" height="60" style="border-radius:50%; border:1px solid #ccc;"><br>
                <input type="checkbox" name="can_view[]" value="<?= $u['id'] ?>" <?= $checked ?>>
                <div style="font-size:12px;"><?= htmlspecialchars($u['username']) ?></div>
            </label>
        <?php endforeach; ?>
    </div><br><br>

    <label><strong>Can Be Viewed By:</strong></label><br>
    <div style="max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px;">
        <?php foreach ($allUsers as $u): 
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $u['username']));
            $imgPath = "../uploads/{$u['category_slug']}/{$slug}/profile.jpg";
            $imgUrl = file_exists($imgPath) ? $imgPath : '../assets/images/default.jpg';
            $checked = in_array($u['id'], $permissions['viewed_by']) ? "checked" : "";
        ?>
            <label style="display:inline-block; width:140px; text-align:center;">
                <img src="<?= $imgUrl ?>" width="60" height="60" style="border-radius:50%; border:1px solid #ccc;"><br>
                <input type="checkbox" name="viewed_by[]" value="<?= $u['id'] ?>" <?= $checked ?>>
                <div style="font-size:12px;"><?= htmlspecialchars($u['username']) ?></div>
            </label>
        <?php endforeach; ?>
    </div><br><br>

    <button type="submit">Update Member</button>
</form>

<p style="color: red; font-weight:bold;">
    <?= $msg ?>
</p>

<hr>
<h3>🧩 About Member Sections</h3>
<a href="about_member.php?user_id=<?= $user['id'] ?>">➕ Add New Section</a><br><br>

<?php
$aboutSections = $pdo->prepare("SELECT * FROM user_about_sections WHERE user_id = ? ORDER BY id ASC");
$aboutSections->execute([$user['id']]);
foreach ($aboutSections as $sec):
?>
    <form method="POST" enctype="multipart/form-data" style="border:1px solid #ccc; padding:15px; margin-bottom:20px;">
        <input type="hidden" name="update_section_id" value="<?= $sec['id'] ?>">

        <label>Heading:</label><br>
        <input type="text" name="section_heading" value="<?= htmlspecialchars($sec['heading']) ?>" required><br><br>

        <label>Content:</label><br>
        <textarea name="section_content" class="rich-editor" rows="6" required><?= htmlspecialchars($sec['content']) ?></textarea><br><br>

        <label>Current Image:</label><br>
        <?php if ($sec['image'] && file_exists($aboutFolder . $sec['image'])): ?>
            <img src="<?= $aboutURL . $sec['image'] ?>" width="120"><br><br>
        <?php else: ?>
            <em>(No image)</em><br><br>
        <?php endif; ?>

        <label>Replace Image (optional):</label><br>
        <input type="file" name="section_image" accept="image/*"><br><br>

        <button type="submit">💾 Update Section</button>
        <a href="edit_member.php?id=<?= $user['id'] ?>&delete_section=<?= $sec['id'] ?>" onclick="return confirm('Delete this section?')" style="color:red; margin-left:15px;">🗑️ Delete</a>
    </form>
<?php endforeach; ?>

<a href="view_members.php">⬅️ Back to Members</a>
<?php include '../includes/admin_footer.php'; ?>