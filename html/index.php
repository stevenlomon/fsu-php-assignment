<?php
  $title = "The Retro Vibe"
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
    <h1><?= $title ?></h1>
    <h2><?= $subheader ?></h2>
  </header>
  
  <a href="groups.php">Alla grupper</a>
  <a href="profile.php">Profil</a>
  <a href="register.php">Registrera</a>
  <a href="login.php">Logga in</a>
  
</body>
</html>