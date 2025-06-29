<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($email, $password, true)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login - EMO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .login-wrapper {
            max-width: 400px;
            width: 100%;
        }

        .login-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 2rem;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .btn-gold {
            background: linear-gradient(90deg, #FFD700, #FFC300);
            border: none;
            color: #000;
            font-weight: bold;
            transition: transform 0.2s ease;
        }

        .btn-gold:hover {
            transform: scale(1.05);
        }

        .form-control:focus {
            border-color: #FFD700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ddd;
        }

        .theme-toggle i {
            font-size: 1.5rem;
            cursor: pointer;
            color: #FFD700;
            transition: transform 0.3s;
        }

        .theme-toggle i:hover {
            transform: rotate(20deg);
        }

        .forgot-link {
            color: #FFD700;
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #fff;
        }
    </style>
    <script>
        // Show/hide password
        document.getElementById("togglePassword").addEventListener("click", function() {
            const pwd = document.getElementById("passwordInput");
            const type = pwd.getAttribute("type") === "password" ? "text" : "password";
            pwd.setAttribute("type", type);
            this.classList.toggle("bi-eye");
            this.classList.toggle("bi-eye-slash");
        });

        // Dark/Light Mode Toggle
        document.getElementById("themeToggle").addEventListener("click", function() {
            document.body.classList.toggle("light-theme");
            if (document.body.classList.contains("light-theme")) {
                document.body.style.background = "#f2f2f2";
                this.classList.replace("bi-sun-fill", "bi-moon-stars-fill");
            } else {
                document.body.style.background = "";
                this.classList.replace("bi-moon-stars-fill", "bi-sun-fill");
            }
        });

        // Optional: Simple client-side validation
        document.getElementById("loginForm").addEventListener("submit", function(e) {
            const email = document.getElementById("emailInput").value.trim();
            const password = document.getElementById("passwordInput").value.trim();
            if (!email || !password) {
                e.preventDefault();
                alert("Please fill in all fields.");
            }
        });
    </script>
    <div class="login-wrapper">
        <div class="login-glass card shadow-lg">
            <h2 class="text-center mb-4">Admin Login</h2>

            <form method="POST" id="loginForm" novalidate>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" id="emailInput" placeholder="Admin Email" required>
                    <label for="emailInput"><i class="bi bi-envelope"></i> Admin Email</label>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" name="password" id="passwordInput" placeholder="Password" required>
                    <label for="passwordInput"><i class="bi bi-lock"></i> Password</label>
                    <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember Me</label>
                </div>

                <button type="submit" class="btn btn-gold w-100">Login</button>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mt-3 animate__animated animate__shakeX"><?= $error ?></div>
                <?php endif; ?>
            </form>

            <div class="text-center mt-3">
                <a href="#" class="forgot-link">Forgot Password?</a>
            </div>

            <div class="theme-toggle text-center mt-3">
                <i class="bi bi-sun-fill" id="themeToggle"></i>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2"></script>
    <script src="assets/js/login.js"></script>
</body>

</html>