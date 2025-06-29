<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isLoggedIn() && !isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password, false)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - EMO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e1e2f, #000000);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: #111;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.2);
            max-width: 900px;
            width: 100%;
            display: flex;
            flex-wrap: wrap;
        }

        .login-image {
            background: url('../assets/images/login-graphic.jpg') no-repeat center center;
            background-size: cover;
            flex: 1 1 50%;
            min-height: 450px;
            position: relative;
        }

        .login-image::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
        }

        .login-image .text {
            position: absolute;
            z-index: 2;
            color: #fff;
            top: 50%;
            left: 10%;
            transform: translateY(-50%);
        }

        .login-image h2 {
            font-weight: 600;
            font-size: 2rem;
            color: #d4af37;
        }

        .login-form {
            flex: 1 1 50%;
            padding: 40px;
            background-color: #1c1c2b;
        }

        .login-form h3 {
            color: #d4af37;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .form-control {
            background-color: #2a2a3d;
            border: none;
            color: #fff;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
            background-color: #2a2a3d;
            color: #fff;
        }

        .btn-gold {
            background-color: #d4af37;
            color: #000;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-gold:hover {
            background-color: #c19e2d;
        }

        .error {
            color: #ff4c4c;
            margin-bottom: 15px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .login-image {
                display: none;
            }
            .login-form {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <div class="text">
                <h2>Welcome Back to EMO</h2>
                <p style="max-width: 80%;">Where memories live and only you hold the key. Log in to continue your journey.</p>
            </div>
        </div>
        <div class="login-form">
            <h3><i class="fas fa-lock me-2"></i>User Login</h3>

            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-light">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-light">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-gold w-100">Login</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
