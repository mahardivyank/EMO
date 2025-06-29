<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$msg = "";
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ========== CATEGORY CREATION ==========
    if ($action === 'add_category') {
        $name = trim($_POST['category']);
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $imageName = "category.jpg";
        $folderPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/" . $slug;
        $imagePath = $folderPath . "/" . $imageName;

        $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            $msg = "❌ Category with the same name already exists.";
        } elseif (!isset($_FILES['category_image']) || $_FILES['category_image']['error'] !== UPLOAD_ERR_OK) {
            $msg = "❌ Category image is required.";
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['category_image']['type'], $allowedTypes)) {
                if (!is_dir($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                if (move_uploaded_file($_FILES['category_image']['tmp_name'], $imagePath)) {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image) VALUES (?, ?, ?)");
                    if ($stmt->execute([$name, $slug, $imageName])) {
                        $msg = "✅ Category '$name' created successfully.";
                    } else {
                        $msg = "❌ Failed to save category to database.";
                    }
                } else {
                    $msg = "❌ Failed to upload category image.";
                }
            } else {
                $msg = "❌ Only JPG and PNG images are allowed.";
            }
        }
    }

    // ========== MEMBER CREATION ==========
    elseif ($action === 'add_user') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $category_id = intval($_POST['category_id']);

        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            $msg = "❌ Profile image is required.";
        } else {
            $emailCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $emailCheck->execute([$email]);
            if ($emailCheck->fetchColumn() > 0) {
                $msg = "❌ Email already exists. Please use a different email.";
            } else {
                $catStmt = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
                $catStmt->execute([$category_id]);
                $cat = $catStmt->fetch();

                if ($cat) {
                    $user_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $username));
                    $baseFolder = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/" . $cat['slug'] . "/" . $user_slug;
                    $profileImagePath = $baseFolder . "/profile.jpg";

                    if (!is_dir($baseFolder . "/gallery")) {
                        mkdir($baseFolder . "/gallery", 0755, true);
                    }
                    if (!is_dir($baseFolder . "/about")) {
                        mkdir($baseFolder . "/about", 0755, true);
                    }

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    if (in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $profileImagePath)) {
                            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, category_id, is_admin, view_limit, profile_image) 
                                                   VALUES (?, ?, ?, ?, 0, 0, ?)");
                            if ($stmt->execute([$username, $email, $password, $category_id, "profile.jpg"])) {
                                $userId = $pdo->lastInsertId();
                                $permissions = [
                                    "can_view" => isset($_POST['can_view']) ? array_map('intval', $_POST['can_view']) : [],
                                    "viewed_by" => isset($_POST['viewed_by']) ? array_map('intval', $_POST['viewed_by']) : []
                                ];
                                file_put_contents($baseFolder . '/permissions.json', json_encode($permissions, JSON_PRETTY_PRINT));

                                // ✅ Redirect to about_member.php
                                header("Location: about_member.php?user_id=" . $userId);
                                exit;
                            } else {
                                $msg = "❌ Error inserting member into database.";
                            }
                        } else {
                            $msg = "❌ Failed to upload profile image.";
                        }
                    } else {
                        $msg = "❌ Only JPG and PNG images are allowed.";
                    }
                } else {
                    $msg = "❌ Invalid category selected.";
                }
            }
        }
    }
}
?>

<?php include '../includes/admin_header.php'; ?>
<h2>Welcome, Admin</h2>

<!-- CATEGORY FORM -->
<h3>Create New Category</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add_category">
    <input type="text" name="category" placeholder="Category Name" required><br><br>
    <input type="file" name="category_image" accept="image/*" required><br><br>
    <button type="submit">Add Category</button>
</form>

<hr>


<!-- Advanced CSS -->
<style>
:root {
    --gold: #d4af37;
    --dark: #1e1e2f;
    --bg: #f8f9fa;
    --glass: rgba(255, 255, 255, 0.1);
}

body {
    background: linear-gradient(135deg,rgb(31, 31, 48), #2a2a40);
    font-family: 'Segoe UI', sans-serif;
    color: #fff;
    animation: fadeIn 1s ease-in-out;
}

.text-golden {
    color: var(--gold);
}

.card {
    background: var(--glass);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    backdrop-filter: blur(20px);
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.card:hover {
    transform: scale(1.02);
    box-shadow: 0 0 50px rgba(212, 175, 55, 0.5);
}

input.form-control, select.form-select {
    background: #fff;
    color: #000;
    border-radius: 10px;
    transition: all 0.3s ease-in-out;
}

input:focus, select:focus {
    outline: none;
    border: 2px solid var(--gold);
    box-shadow: 0 0 10px var(--gold);
}

button {
    border-radius: 12px;
    transition: all 0.3s ease-in-out;
    font-weight: bold;
    letter-spacing: 1px;
}

button:hover {
    transform: scale(1.05);
    box-shadow: 0 0 10px var(--gold);
}

img.rounded-circle {
    border: 2px solid var(--gold);
    transition: transform 0.3s ease-in-out;
}

img.rounded-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 0 10px var(--gold);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Advanced JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Animate cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, {
        threshold: 0.2
    });

    document.querySelectorAll('.card').forEach(card => {
        observer.observe(card);
    });

    // Tooltip Hover for checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('mouseenter', () => {
            cb.parentElement.title = cb.nextElementSibling?.textContent || 'Member';
        });
    });

    // Live Image Preview for uploads
    const previewImage = (input, callback) => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => callback(e.target.result);
                reader.readAsDataURL(file);
            }
        });
    };

    previewImage(document.querySelector('input[name="profile_image"]'), (src) => {
        const preview = document.createElement('img');
        preview.src = src;
        preview.className = 'rounded mt-2';
        preview.style.maxWidth = '100px';
        preview.style.border = '2px solid var(--gold)';
        document.querySelector('input[name="profile_image"]').parentElement.appendChild(preview);
    });

    previewImage(document.querySelector('input[name="category_image"]'), (src) => {
        const preview = document.createElement('img');
        preview.src = src;
        preview.className = 'rounded mt-2';
        preview.style.maxWidth = '100px';
        preview.style.border = '2px solid var(--gold)';
        document.querySelector('input[name="category_image"]').parentElement.appendChild(preview);
    });

    // Floating labels animation (example advanced UX)
    document.querySelectorAll('input.form-control, select.form-select').forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', () => {
            if (!input.value) input.parentElement.classList.remove('focused');
        });
    });
});
</script>


<!-- MEMBER FORM -->
<h3>Add New Member (User)</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add_user">

    <input type="text" name="username" placeholder="User Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="password" placeholder="Password" required><br><br>

    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label><strong>Can View These Members:</strong></label><br>
    <div style="max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px; width:100%;">
        <?php
        $users = $pdo->query("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id ORDER BY u.username ASC")->fetchAll();
        foreach ($users as $user):
            $userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
            $imgPath = "../uploads/{$user['category_slug']}/{$userSlug}/profile.jpg";
            $imgUrl = file_exists($imgPath) ? $imgPath : '../assets/images/default.jpg';
        ?>
        <label style="display:inline-block; width:140px; margin:5px; text-align:center;">
            <img src="<?= $imgUrl ?>" width="60" height="60" style="display:block; border-radius:50%; border:1px solid #ccc; margin-bottom:5px;">
            <input type="checkbox" name="can_view[]" value="<?= $user['id'] ?>"> 
            <div style="font-size:12px;"><?= htmlspecialchars($user['username']) ?></div>
        </label>
        <?php endforeach; ?>
    </div><br><br>

    <label><strong>Can Be Viewed By:</strong></label><br>
    <div style="max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px; width:100%;">
        <?php foreach ($users as $user): ?>
        <label style="display:inline-block; width:140px; margin:5px; text-align:center;">
            <img src="<?= $imgUrl ?>" width="60" height="60" style="display:block; border-radius:50%; border:1px solid #ccc; margin-bottom:5px;">
            <input type="checkbox" name="viewed_by[]" value="<?= $user['id'] ?>"> 
            <div style="font-size:12px;"><?= htmlspecialchars($user['username']) ?></div>
        </label>
        <?php endforeach; ?>
    </div><br><br>

    <input type="file" name="profile_image" accept="image/*" required><br><br>
    <button type="submit">Create Member</button>
</form>

<br><br>

<?php if ($msg): ?>
    <p style="color: green; font-weight: bold;"><?= $msg ?></p>
<?php endif; ?>

<br>
<a href="logout.php">Logout</a>
<?php include '../includes/admin_footer.php'; ?>
