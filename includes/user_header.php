<?php if (!isset($_SESSION)) session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required Meta Tags -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title>EMO - Private Social Portfolio</title>
    <meta name="description" content="EMO is a private and secure portfolio space where members can safely store, manage, and showcase their memories and moments.">
    <meta name="keywords" content="EMO, private social portfolio, photo sharing, memory keeper, user gallery, secure media, personal profile, digital diary">
    <meta name="author" content="EMO Team">
    <meta name="robots" content="index, follow">
    <meta name="language" content="en">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="EMO - Private Social Portfolio">
    <meta property="og:description" content="Your personal digital sanctuary. Upload and share photos, videos, and stories – securely and privately.">
    <meta property="og:image" content="https://yourdomain.com/assets/images/og-preview.jpg">
    <meta property="og:url" content="https://yourdomain.com/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="EMO - Private Social Portfolio">
    <meta name="twitter:description" content="Your personal digital sanctuary. Upload and share photos, videos, and stories – securely and privately.">
    <meta name="twitter:image" content="https://yourdomain.com/assets/images/og-preview.jpg">

    <!-- Favicon & Theme -->
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <meta name="theme-color" content="#d4af37">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://yourdomain.com/">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fffbe6, #f9f5f0);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #d4af37 !important;
        }

        .navbar-light .navbar-nav .nav-link {
            font-weight: 500;
            color: #333;
            transition: color 0.3s ease;
        }

        .navbar-light .navbar-nav .nav-link:hover,
        .navbar-light .navbar-nav .nav-link:focus {
            color: #d4af37;
        }

        .navbar-light .navbar-nav .nav-link.text-danger:hover {
            color: #a90000 !important;
        }

        .navbar {
            background-color: #fff;
            border-bottom: 2px solid #f0e6d2;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .navbar-toggler {
            border: none;
        }

        .navbar-toggler:focus {
            outline: none;
            box-shadow: none;
        }
    </style>

    <!-- Optional JS Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            gsap.from(".navbar", { duration: 1, y: -50, opacity: 0, ease: "power2.out" });
            gsap.from(".nav-link", { duration: 0.8, opacity: 0, stagger: 0.2, delay: 0.5 });
        });
    </script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light px-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">EMO</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar" aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="userNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-3">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="upload_gallery.php"><i class="bi bi-upload"></i> Upload</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_gallery.php"><i class="bi bi-image"></i> My Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="reset_password.php"><i class="bi bi-lock"></i> Password</a></li>
                    <li class="nav-item"><a class="nav-link" href="members.php"><i class="bi bi-people"></i> Members</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">