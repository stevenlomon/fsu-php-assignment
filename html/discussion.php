<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  $title = "The Retro Vibe";

  // Skaffa discussion ID på samma sätt som vi gör i group.php
  $discussionId = (int)($_GET['id'] ?? 0);

  if ($discussionId <= 0) {
    header('Location: /groups.php'); // Säkrare ifall denna bara leder tillbara till groups.php så det inte blir en redirect trap
    exit;
  }

  $discussion = get_discussion($mysqli, $discussionId);
  $subheader = "Diskussions sida: " . $discussion['title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
</head>
<body>
  <header>
    <p>PHP <?= phpversion()?> running cleanly</p>
    <a href="index.php"><h1><?= e($title) ?></h1></a>
    <h2><?= e($subheader) ?></h2>
  </header>
  
</body>
</html>