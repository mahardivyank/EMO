<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

// Load settings
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $stmt = $pdo->prepare("REPLACE INTO settings (name, value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }
    header("Location: admin_settings.php?success=1");
    exit;
}

include '../includes/admin_header.php';
?>
<style>
  body {
    background: #0f0f0f;
    font-family: 'Segoe UI', sans-serif;
    color: #f4f4f4;
    padding: 40px;
  }
  h2 {
    text-align: center;
    color: gold;
    margin-bottom: 30px;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
    animation: fadeInDown 0.6s ease;
  }
  form {
    max-width: 600px;
    margin: auto;
    background: rgba(255, 255, 255, 0.05);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.15);
    animation: fadeInUp 0.8s ease;
  }
  label {
    font-weight: bold;
    color: #ccc;
  }
  input[type="text"] {
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
  input[type="text"]:focus {
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 0 0 6px gold;
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
    font-weight: bold;
  }
  a {
    display: block;
    text-align: center;
    margin-top: 25px;
    color: #00eaff;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s;
  }
  a:hover {
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

<h2>⚙️ Admin Settings</h2>
<?php if (isset($_GET['success'])): ?>
  <p style="color: #00ff88">✅ Settings updated successfully!</p>
<?php endif; ?>

<form method="POST">
  <label>Site Title:</label><br>
  <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>"><br>

  <label>Admin Display Name:</label><br>
  <input type="text" name="admin_name" value="<?= htmlspecialchars($settings['admin_name'] ?? '') ?>"><br>

  <label>Footer Note:</label><br>
  <input type="text" name="footer_note" value="<?= htmlspecialchars($settings['footer_note'] ?? '') ?>"><br>

  <button type="submit">💾 Save Settings</button>
</form>

<a href="dashboard.php">⬅️ Back to Dashboard</a>

<?php include '../includes/admin_footer.php'; ?>
