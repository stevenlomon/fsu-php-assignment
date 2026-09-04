<?php
  require_once __DIR__ . '/includes/helpers.php';

  $title = "The Retro Vibe";
  $subheader = "Ditt forum för att diskutera retro gaming nostalgi 👾";

  session_start(); // Se kommentar kring denna i `login.php`

  $successMessage = $_SESSION['flash_success'] ?? null;
  unset($_SESSION['flash_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <header>
    <p>PHP <?= phpversion()?> running cleanly</p>
    <a href="index.php"><h1><?= e($title) ?></h1></a>
    <h2><?= e($subheader) ?></h2>
  </header>

  <?php if (is_logged_in()): ?>
    <?php if ($successMessage): ?>
      <p style="color: green;"><?= e($successMessage) ?></p>
    <?php endif; ?>
    <a href="profile.php">Profil</a>
    <a href="logout.php">Logga ut</a>
  <?php else: ?>
    <a href="register.php">Registrera</a>
    <a href="login.php">Logga in</a>
  <?php endif; ?>
        
  <a href="groups.php">Alla grupper</a>
</body>
</html>