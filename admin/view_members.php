<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

// Get all users
$users = $pdo->query("
    SELECT u.*, c.name AS category, c.slug AS category_slug 
    FROM users u 
    JOIN categories c ON u.category_id = c.id 
    ORDER BY u.id DESC
")->fetchAll();

// Build user lookup map for quick access
$userMap = [];
foreach ($users as $u) {
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $u['username']));
    $imgPath = "../uploads/{$u['category_slug']}/{$slug}/profile.jpg";
    $img = file_exists($imgPath) ? $imgPath : '../assets/images/default.jpg';
    $userMap[$u['id']] = [
        'name' => $u['username'],
        'email' => $u['email'],
        'image' => $img
    ];
}

// Reusable render function
function renderMemberList($ids, $userMap)
{
    if (empty($ids)) return "<em style='color:#999;'>None</em>";
    $html = "<div style='display:flex; flex-wrap:wrap; gap:5px;'>";
    foreach ($ids as $id) {
        if (!isset($userMap[$id])) continue;
        $m = $userMap[$id];
        $html .= "
            <div style='text-align:center; width:70px; font-size:11px;'>
                <img src='{$m['image']}' width='40' height='40' style='border-radius:50%; border:1px solid #ccc;'><br>
                {$m['name']}<br>
                <span style='font-size:9px; color:#666;'>{$m['email']}</span>
            </div>";
    }
    $html .= "</div>";
    return $html;
}
?>

<?php include '../includes/admin_header.php'; ?>
<div class="members-wrapper">
    <h2 class="page-title">👥 View All Members</h2>
    <style>
        body {
            background: linear-gradient(to right, #000000, #1c1c1c);
            font-family: 'Segoe UI', sans-serif;
            color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .members-wrapper {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(255, 215, 0, 0.3);
            animation: fadeIn 1s ease;
        }

        .page-title {
            text-align: center;
            font-size: 2rem;
            color: gold;
            margin-bottom: 30px;
            animation: slideDown 0.6s ease;
        }

        .table-container {
            overflow-x: auto;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
        }

        .styled-table th {
            background-color: rgba(255, 215, 0, 0.2);
            color: gold;
            font-weight: bold;
        }

        .member-row {
            transition: transform 0.3s ease, background 0.3s ease;
            cursor: pointer;
        }

        .member-row:hover {
            transform: scale(1.01);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .email-cell {
            font-size: 12px;
            color: #aaa;
        }

        .profile-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            transition: transform 0.4s ease;
        }

        .profile-img:hover {
            transform: rotate(8deg) scale(1.1);
        }

        .actions .action-btn {
            margin: 0 4px;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .action-btn.edit {
            background: rgba(255, 215, 0, 0.1);
            color: gold;
        }

        .action-btn.delete {
            background: rgba(255, 0, 0, 0.1);
            color: #ff5e5e;
        }

        .action-btn.view {
            background: rgba(0, 255, 255, 0.1);
            color: #00eaff;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
        }

        .back-link a {
            text-decoration: none;
            color: #ffd700;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: white;
        }

        .profile-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255, 215, 0, 0.4);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2),
                inset 0 0 5px rgba(255, 255, 255, 0.2);
            transition: transform 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
            position: relative;
            z-index: 1;
        }

        .profile-img:hover {
            transform: scale(1.15) rotate(3deg);
            border-color: gold;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.7),
                0 0 40px rgba(255, 255, 255, 0.1),
                inset 0 0 10px rgba(255, 255, 255, 0.3);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .flash {
            animation: flashAnim 0.5s ease-in-out;
        }

        @keyframes flashAnim {
            from {
                background-color: rgba(255, 215, 0, 0.4);
            }

            to {
                background-color: transparent;
            }
        }

        .profile-ring {
            position: relative;
            display: inline-block;
        }

        .profile-ring::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.4) 0%, transparent 70%);
            animation: pulseRing 2.5s infinite ease-in-out;
            z-index: 0;
        }

        @keyframes pulseRing {
            0% {
                transform: scale(0.9);
                opacity: 0.6;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.1;
            }

            100% {
                transform: scale(0.9);
                opacity: 0.6;
            }
        }
        .tilt-img {
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    perspective: 1000px;
    transform-style: preserve-3d;
    will-change: transform;
}

.tilt-img.hidden {
    opacity: 0;
    transform: scale(0.8);
}

.tilt-img.reveal {
    opacity: 1;
    transform: scale(1);
    transition: all 0.6s ease-out;
}

/* Burst effect (gold ring pulse) */
.tilt-img.burst {
    box-shadow:
        0 0 10px gold,
        0 0 20px gold,
        0 0 40px rgba(255, 215, 0, 0.6),
        inset 0 0 10px rgba(255, 255, 255, 0.3);
    border-color: gold;
}

    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const rows = document.querySelectorAll(".member-row");

            rows.forEach(row => {
                row.addEventListener("click", () => {
                    row.classList.add("flash");
                    setTimeout(() => {
                        row.classList.remove("flash");
                    }, 500);
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
    const tiltImages = document.querySelectorAll('.tilt-img');

    tiltImages.forEach((img) => {
        // Add tilt on mouse move
        img.addEventListener('mousemove', (e) => {
            const rect = img.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 10;
            const rotateY = (x - centerX) / 10;

            img.style.transform = `rotateX(${-rotateX}deg) rotateY(${rotateY}deg) scale(1.1)`;
        });

        img.addEventListener('mouseleave', () => {
            img.style.transform = 'rotateX(0) rotateY(0) scale(1)';
        });
    });

    // Animate image entrance using IntersectionObserver
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    tiltImages.forEach(img => {
        img.classList.add('hidden');
        observer.observe(img);
    });

    // Optional: burst effect on hover
    tiltImages.forEach(img => {
        img.addEventListener('mouseenter', () => {
            img.classList.add('burst');
            setTimeout(() => img.classList.remove('burst'), 500);
        });
    });
});

    </script>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Category</th>
                    <th>Can View</th>
                    <th>Can Be Viewed By</th>
                    <th>Profile Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $u): ?>
                    <?php
                    $userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $u['username']));
                    $basePath = "../uploads/{$u['category_slug']}/{$userSlug}";
                    $imgPath = "$basePath/profile.jpg";
                    $imgExists = file_exists($imgPath);
                    $permPath = "$basePath/permissions.json";
                    $canView = $viewedBy = [];
                    if (file_exists($permPath)) {
                        $data = json_decode(file_get_contents($permPath), true);
                        $canView = $data['can_view'] ?? [];
                        $viewedBy = $data['viewed_by'] ?? [];
                    }
                    ?>
                    <tr class="member-row">
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td class="email-cell"><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['category']) ?></td>
                        <td><?= renderMemberList($canView, $userMap) ?></td>
                        <td><?= renderMemberList($viewedBy, $userMap) ?></td>
                        <td>
                            <?php if ($imgExists): ?>
                                <div class="profile-ring">
                                    <img src="<?= $imgPath ?>" class="profile-img tilt-img">
                                </div>
                            <?php else: ?>
                                <em>No image</em>
                            <?php endif; ?>
                        </td>


                        <td class="actions">
                            <a href="edit_member.php?id=<?= $u['id'] ?>" class="action-btn edit">✏️</a>
                            <a href="delete_member.php?id=<?= $u['id'] ?>" class="action-btn delete" onclick="return confirm('Are you sure?');">❌</a>
                            <a href="view_member_profile.php?id=<?= $u['id'] ?>" class="action-btn view">🔍</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="back-link">
        <a href="dashboard.php">⬅️ Back to Dashboard</a>
    </div>
</div>


<?php include '../includes/admin_footer.php'; ?>