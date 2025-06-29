<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid category ID");

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();
if (!$category) die("Category not found");

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category']);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $imageName = $category['image'];

    // Rename folder if slug changed
    $oldPath = "../uploads/" . $category['slug'];
    $newPath = "../uploads/" . $slug;
    if ($category['slug'] !== $slug && is_dir($oldPath)) {
        rename($oldPath, $newPath);
    }

    // Update image if provided
    if ($_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['category_image']['tmp_name'], $newPath . "/" . $imageName);
    }

    $update = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
    if ($update->execute([$name, $slug, $id])) {
        header("Location: view_categories.php");
        exit;
    } else {
        $msg = "❌ Failed to update category.";
    }
}
?>
<?php include '../includes/admin_header.php'; ?>
<style>
  body {
    background: #111;
    font-family: 'Segoe UI', sans-serif;
    color: #f4f4f4;
    padding: 40px;
  }
  h2 {
    text-align: center;
    color: gold;
    margin-bottom: 30px;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
    animation: fadeInDown 0.7s ease;
  }
  form {
    max-width: 500px;
    margin: auto;
    background: rgba(255, 255, 255, 0.05);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.15);
    animation: fadeInUp 0.8s ease;
  }
  label {
    font-weight: bold;
    color: #ddd;
  }
  input[type="text"], input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    margin-bottom: 20px;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.08);
    color: white;
    outline: none;
    transition: all 0.3s ease;
  }
  input[type="text"]:focus, input[type="file"]:focus {
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 0 0 5px gold;
  }
  button[type="submit"] {
    background-color: gold;
    color: black;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
  }
  button[type="submit"]:hover {
    background-color: #ffd700;
    transform: scale(1.05);
  }
  p {
    text-align: center;
    margin-top: 15px;
  }
  .back-link {
    display: block;
    text-align: center;
    margin-top: 30px;
    color: #00eaff;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s;
  }
  .back-link:hover {
    color: gold;
  }
  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const fileInput = document.querySelector('input[type="file"]');

    fileInput.addEventListener('change', (e) => {
      const fileName = e.target.files[0]?.name;
      if (fileName) {
        fileInput.style.background = "rgba(255, 215, 0, 0.1)";
        fileInput.style.color = "gold";
      }
    });
  });
</script>

<h2>✏️ Edit Category</h2>
<form method="POST" enctype="multipart/form-data">
  <label>Category Name:</label><br>
  <input type="text" name="category" value="<?= htmlspecialchars($category['name']) ?>" required><br>

  <label>Replace Image:</label><br>
  <input type="file" name="category_image" accept="image/*"><br>

  <button type="submit">Update Category</button>
</form>
<p style="color: red; text-align:center; font-weight:bold;"> <?= $msg ?> </p>

<a href="view_categories.php" class="back-link">⬅️ Back to Categories</a>
<?php include '../includes/admin_footer.php'; ?>
