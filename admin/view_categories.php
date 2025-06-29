<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>

<?php include '../includes/admin_header.php'; ?>
<style>
  body {
    background: #121212;
    color: #f4f4f4;
    font-family: 'Segoe UI', sans-serif;
    padding: 30px;
  }
  h2 {
    text-align: center;
    color: gold;
    text-shadow: 1px 1px 8px rgba(255, 215, 0, 0.5);
    animation: slideDown 0.6s ease-out;
  }
  .table-container {
    overflow-x: auto;
  }
  table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    margin-top: 30px;
    animation: fadeIn 0.8s ease;
  }
  th, td {
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 215, 0, 0.1);
  }
  th {
    background-color: rgba(255, 215, 0, 0.2);
    color: gold;
    font-weight: bold;
  }
  tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
  }
  img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid rgba(255, 215, 0, 0.2);
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.15);
    transition: transform 0.3s ease;
  }
  img:hover {
    transform: scale(1.1);
  }
  a {
    color: #00eaff;
    text-decoration: none;
    font-weight: bold;
    margin: 0 5px;
    transition: color 0.3s;
  }
  a:hover {
    color: gold;
  }
  .back-link {
    display: block;
    text-align: center;
    margin-top: 40px;
    color: #00eaff;
    font-weight: bold;
    text-decoration: none;
  }
  .back-link:hover {
    color: gold;
  }
  @keyframes slideDown {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  @media screen and (max-width: 768px) {
    th, td {
      font-size: 13px;
      padding: 10px;
    }
    img {
      width: 50px;
      height: 50px;
    }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const tableRows = document.querySelectorAll("table tr");
    tableRows.forEach((row, idx) => {
      if (idx !== 0) {
        row.style.transition = "transform 0.3s ease";
        row.addEventListener("mouseenter", () => {
          row.style.transform = "scale(1.01)";
        });
        row.addEventListener("mouseleave", () => {
          row.style.transform = "scale(1)";
        });
      }
    });
  });
</script>

<h2>📂 View All Categories</h2>

<div class="table-container">
<table>
  <tr>
    <th>#</th>
    <th>Name</th>
    <th>Slug</th>
    <th>Image</th>
    <th>Actions</th>
  </tr>

  <?php if (count($categories) > 0): ?>
    <?php foreach ($categories as $index => $cat): ?>
      <?php
        $imagePath = "../uploads/{$cat['slug']}/{$cat['image']}";
        $imageURL = is_file($imagePath) ? $imagePath : 'no-image.jpg';
      ?>
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($cat['name']) ?></td>
        <td><?= htmlspecialchars($cat['slug']) ?></td>
        <td>
          <img src="<?= $imageURL ?>" alt="category image">
        </td>
        <td>
          <a href="view_category.php?id=<?= $cat['id'] ?>">🔍 View</a>
          <a href="edit_category.php?id=<?= $cat['id'] ?>">✏️ Edit</a>
          <a href="delete_category.php?id=<?= $cat['id'] ?>" onclick="return confirm('Are you sure to delete this category?')">❌ Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="5">No categories found.</td></tr>
  <?php endif; ?>
</table>
</div>

<a href="dashboard.php" class="back-link">⬅️ Back to Dashboard</a>

<?php include '../includes/admin_footer.php'; ?>
