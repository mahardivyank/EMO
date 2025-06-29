<?php if (!isset($_SESSION)) session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <title>EMO Admin Panel</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            padding: 0;
            background: #f9f9f9;
        }

        nav {
            background-color: #222;
            padding: 10px;
        }

        nav a {
            color: gold;
            text-decoration: none;
            margin-right: 20px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            padding: 20px;
        }
    </style>
</head>

<body>
    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="view_categories.php">📁 Categories</a>
        <a href="view_members.php">👥 Members</a>
        <a href="admin_settings.php">⚙️ Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </nav>
    <div class="container">